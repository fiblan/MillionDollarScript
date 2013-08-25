<?php
/**
 * @version		$Id: paypalIPN.php 69 2010-09-12 01:31:15Z ryan $
 * @package		mds
 * @copyright	(C) Copyright 2010 Ryan Rhode, All rights reserved.
 * @author		Ryan Rhode, ryan@milliondollarscript.com
 * @license		This program is free software; you can redistribute it and/or modify
 *		it under the terms of the GNU General Public License as published by
 *		the Free Software Foundation; either version 3 of the License, or
 *		(at your option) any later version.
 *
 *		This program is distributed in the hope that it will be useful,
 *		but WITHOUT ANY WARRANTY; without even the implied warranty of
 *		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *		GNU General Public License for more details.
 *
 *		You should have received a copy of the GNU General Public License along
 *		with this program;  If not, see http://www.gnu.org/licenses/gpl-3.0.html.
 *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *
 *		Million Dollar Script
 *		A pixel script for selling pixels on your website.
 *
 *		For instructions see README.txt
 *
 *		Visit our website for FAQs, documentation, a list team members,
 *		to post any bugs or feature requests, and a community forum:
 * 		http://www.milliondollarscript.com/
 *
 */
require_once "../config.php";

$_PAYMENT_OBJECTS['PayPal'] = new PayPal;//"paypal";

define (IPN_LOGGING, 'Y');


function pp_mail_error($msg) {

	$date = date("D, j M Y H:i:s O"); 
	
	$headers = "From: ". SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Reply-To: ".SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Return-Path: ".SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "X-Mailer: PHP" ."\r\n";
	$headers .= "Date: $date" ."\r\n"; 
	$headers .= "X-Sender-IP: $REMOTE_ADDR" ."\r\n";

	$entry_line =  "(payal error detected) $msg\r\n "; 
	$log_fp = @fopen("logs.txt", "a"); 
	@fputs($log_fp, $entry_line); 
	@fclose($log_fp);


	@mail(SITE_CONTACT_EMAIL, "Error message from ".SITE_NAME." Jamit Paypal IPN script. ", $msg, $headers);

}

function pp_log_entry ($entry_line) {

	if (IPN_LOGGING == 'Y') {

		$entry_line =  "$entry_line\r\n "; 
		$log_fp = @fopen("logs.txt", "a"); 
		@fputs($log_fp, $entry_line); 
		@fclose($log_fp);

	}


}


function pp_prefix_order_id($order_id) {

	return substr(md5(SITE_NAME), 1, 5).$order_id;

}


function pp_strip_order_id($order_id) {

	return substr($order_id, 5);
}




#####################################################################################

if ($_POST['txn_id']!='') { 

	// check if we can post back to paypal
	if (stristr(ini_get('disable_functions'), "fsockopen")) {
		pp_mail_error ( "<p>fsockopen is disabled on this server, this script can not post information to the PayPal server for IPN confirmation.");
		die();
	}

	// read the post from PayPal system and add 'cmd'
	$req = 'cmd=_notify-validate';

	foreach ($_POST as $key => $value) {
		
		if (get_magic_quotes_gpc()) {
			$value = stripslashes($value);
		}
		$value = urlencode($value);
		$req .= "&$key=$value";
		
	}

	$entry_line =  "$req"; 
	pp_log_entry ($entry_line);

	// post back to PayPal system to validate
	$header .= "POST /cgi-bin/webscr HTTP/1.0\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
	$fp = fsockopen (PAYPAL_SERVER, 80, $errno, $errstr, 30);

	// assign posted variables to local variables
	$item_name = $_POST['item_name'];
	$item_number = $_POST['item_number'];
	$payment_status = $_POST['payment_status'];
	$mc_gross = $_POST['mc_gross'];
	$mc_currency = $_POST['mc_currency'];
	$payment_type = $_POST['payment_type'];
	$pending_reason = $_POST['pending_reason'];
	$reason_code = $_POST['reason_code'];
	$payment_date = $_POST['payment_date'];
	$txn_id = $_POST['txn_id'];
	$parent_txn_id = $_POST['parent_txn_id'];
	$txn_type = $_POST['txn_type'];
	$receiver_email = $_POST['receiver_email'];
	$payer_email = $_POST['payer_email'];

	$invoice_id = pp_strip_order_id($_POST['invoice']);

	$sql = "select * FROM orders where order_id='".$invoice_id."'";
	$result = mysql_query ($sql) or pp_mail_error(mysql_error().$sql);
	$order_row = mysql_fetch_array($result);
	//pp_log_entry($sql."");
	$business = $_POST['business'];
	$employer_id = $_POST['custom']; // employer_id

	$VERIFIED = false;



	if (!$fp) {
	// HTTP ERROR
		$entry_line =  "HTTP ERROR! cannot post back to PayPal\r\n "; 
		pp_log_entry($entry_line);
	} else {

		fputs ($fp, $header . $req); // post to paypal


		while (!feof($fp)) {
		$res = fgets ($fp, 1024);

			$entry_line =  "$res"; 

			if (strcmp ($res, "VERIFIED") == 0) {
				pp_log_entry($entry_line);
				$VERIFIED = 1;
				
				// check that receiver_email is your Primary PayPal email
				if(strcmp(strtolower(PAYPAL_EMAIL), strtolower($business))!=0) {
					pp_mail_error ("Possible fraud. Error with receiver_email. ".strtolower(PAYPAL_EMAIL)." != ".strtolower($business)."\n");
					pp_log_entry("Possible fraud. Error with receiver_email. ".strtolower(PAYPAL_EMAIL)." != ".strtolower($business));
					$VERIFIED = false;	
				} 

				// check so that transactrion id cannot be reused

				$sql = "SELECT * FROM transactions WHERE txn_id='$txn_id' ";
				$result = mysql_query($sql) or pp_mail_error (mysql_error().$sql); 
				if (mysql_num_rows($result)> 0) { 
					//pp_mail_error ("Possible fraud. Transaction id: $txn_id is already in the database. \n");
					pp_log_entry("transaction $txn_id already processed");
					$VERIFIED = false;	
					die();

				}
				// check that payment_amount/payment_currency are correct

				$amount = convert_to_currency($order_row['price'], $order_row['currency'], PAYPAL_CURRENCY);

				if (($amount != $mc_gross)) {
					//pp_mail_error ("Transaction has incorrect currency. $amount=$mc_gross\n");
					pp_log_entry($order_row['price'].$order_row['currency']."Transaction has incorrect currency. $amount=$mc_gross invoice_id: $invoice_id\n");
					$VERIFIED = false;	

				}

				// we only accept web payments.
				// txn_type: 'cart', 'send_money', 'web_accept'
				// 'subscr_signup', 'subscr_cancel', 'subscr_failed', 'subscr_payment', 'subscr_eot', 'subscr_modify'

				/*
				if ($txn_type != 'web_accept' ) { 
					pp_mail_error ("Transaction has incorrect type. txn_type = $txn_type \n");
					$VERIFIED = false;

				}
				*/
				
				

				$entry_line =  "verified: $res";
				pp_log_entry($entry_line);
			}
			else if (strcmp ($res, "INVALID") == 0) {
				pp_log_entry($entry_line);
			// log for manual investigation
				$VERIFIED = false;
				
			}
		}
		fclose ($fp);

		// if VERIFIED=1 process payment
		if ($VERIFIED) {
			
			if ($txn_type=='subscr_signup') {


			}

		
			if ($txn_type=='subscr_cancel') {

				

			}

			

			if ($txn_type=='subscr_modify') {

			}

			if ($txn_type=='subscr_payment') {

				//$invoice_id;

				//$original_order_id

				if (!$order_row['original_order_id']) {
					// this is a renew
					pay_renew_order($invoice_id);
				} else {
					// this is the first payment!
					complete_order ($row['user_id'], $invoice_id);

				}


				debit_transaction($invoice_id, $amount, $currency, $txn_id, $reason, "PayPal", $product_type);


			}

			if ($txn_type=='subscr_failed') {

			}

			if ($txn_type=='subscr_eot') {

			}

			

			if (($txn_type=='web_accept') || ($txn_type=='')) { // transaction came from a button or straight from paypal

			

				switch ($payment_status) {
					case "Canceled_Reversal":
						complete_order ($row['user_id'], $invoice_id);
						debit_transaction($invoice_id, $mc_gross, $mc_currency, $txn_id, $reason_code, 'PayPal');
						
						break;
					case "Completed":
						// Funds successfully transferred
					// complete_order ($user_id, $order_id);

						$sql = "select user_id FROM orders where order_id='".$invoice_id."'";
						$result = mysql_query ($sql) or pp_mail_error(mysql_error().$sql);
						$row = mysql_fetch_array($result);

						complete_order ($row['user_id'], $invoice_id);
						debit_transaction($invoice_id, $mc_gross, $mc_currency, $txn_id, $reason_code, 'PayPal');

						break;
					case "Denied":
						// denied by merchant
						
						break;
					case "Failed":
						// only happens when payment is from customers' bank account
						//insert_transaction ($employer_id, $payment_status, $pending_reason, $reason_code, $payment_date, $txn_id, $parent_txn_id, $txn_type, $payment_type, $mc_gross, $mc_currency, $item_name, $item_number, $invoice_id);

						
						break;
					case "Pending":
						$sql = "select user_id FROM orders where order_id='".$invoice_id."'";
						$result = mysql_query ($sql) or pp_mail_error(mysql_error().$sql);
						$row = mysql_fetch_array($result);

						pend_order ($row['user_id'], $invoice_id);
						
						// pending_reason : 'address', 'echeck', 'intl', 'multi_currency', 'unilateral', 'upgrade', 'verify', 'other'
					
						break;
					case "Refunded":
						// reason_code : 'buyer_complaint', 'chargeback', 'guarantee', 'refund', 'other'
						cancel_order ( $invoice_id);
						credit_transaction($invoice_id, $mc_gross, $mc_currency, $txn_id, $reason_code, 'PayPal');

						break;
					case "Reversed":
						// reason_code : 'buyer_complaint', 'chargeback', 'guarantee', 'refund', 'other'
						cancel_order ( $invoice_id);
						credit_transaction($invoice_id, $mc_gross, $mc_currency, $txn_id, $reason_code, 'PayPal');
						
						break;
					default:
						break;
						
				} // end switch

			} // end web payment


		}// end if VERIFIED == true

	} // end if !$fp

} // end IPN routine


###########################################################################
# Payment Object



class PayPal {

	var $name;
	var $description;
	var $className="PayPal";
	

	function PayPal() {

		global $label;

		$this->name=$label['payment_paypal_name'];
		$this->description=$label['payment_paypal_descr'];

		if ($this->is_installed()) {

			$sql = "SELECT * FROM config where `key`='PAYPAL_ENABLED' OR `key`='PAYPAL_EMAIL' OR `key`='PAYPAL_CURRENCY' OR `key`='PAYPAL_BUTTON_URL' OR `key`='PAYPAL_IPN_URL' OR `key`='PAYPAL_RETURN_URL' OR `key`='PAYPAL_CANCEL_RETURN_URL' OR `key`='PAYPAL_PAGE_STYLE' OR `key`='PAYPAL_SERVER' OR `key`='PAYPAL_AUTH_TOKEN' OR `key`='PAYPAL_SUBSCR_MODE' OR `key`='PAYPAL_SUBSCR_BUTTON_URL' ";
			$result = mysql_query($sql) or die (mysql_error().$sql);

			while ($row=mysql_fetch_array($result)) {

				define ($row['key'], $row['val']);

			}

		}


	}

	function get_currency() {

		return PAYPAL_CURRENCY;

	}


	function install() {

		echo "Install PayPal..<br>";

		$sql = "REPLACE INTO config (`key`, val) VALUES ('PAYPAL_ENABLED', 'N')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('PAYPAL_EMAIL', '')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('PAYPAL_CURRENCY', 'USD')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('PAYPAL_BUTTON_URL', 'https://www.paypal.com/en_US/i/btn/x-click-but6.gif')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('PAYPAL_RETURN_URL', '')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('PAYPAL_IPN_URL', '')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('PAYPAL_CANCEL_RETURN_URL', '')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('PAYPAL_PAGE_STYLE', 'default')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('PAYPAL_SERVER', 'www.paypal.com')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('PAYPAL_AUTH_TOKEN', '')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('PAYPAL_SUBSCR_MODE', 'N')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('PAYPAL_SUBSCR_BUTTON_URL', 'https://www.paypal.com/en_US/i/btn/x-click-butcc-subscribe.gif')";
		mysql_query($sql);
		
		

		
	}

	function uninstall() {

		echo "Uninstall PayPal..<br>";

	
		$sql = "DELETE FROM config where `key`='PAYPAL_ENABLED'";
		mysql_query($sql);
		$sql = "DELETE FROM config where `key`='PAYPAL_EMAIL'";
		mysql_query($sql);
		$sql = "DELETE FROM config where `key`='PAYPAL_CURRENCY'";
		mysql_query($sql);
		$sql = "DELETE FROM config where `key`='PAYPAL_BUTTON_URL'";
		mysql_query($sql);
		$sql = "DELETE FROM config where `key`='PAYPAL_IPN_URL'";
		mysql_query($sql);
		$sql = "DELETE FROM config where `key`='PAYPAL_RETURN_URL'";
		mysql_query($sql);
		$sql = "DELETE FROM config where `key`='PAYPAL_CANCEL_RETURN_URL'";
		mysql_query($sql);
		$sql = "DELETE FROM config where `key`='PAYPAL_PAGE_STYLE'";
		mysql_query($sql);
		$sql = "DELETE FROM config where `key`='PAYPAL_SERVER'";
		mysql_query($sql);
		$sql = "DELETE FROM config where `key`='PAYPAL_AUTH_TOKEN'";
		mysql_query($sql);
		$sql = "DELETE FROM config where `key`='PAYPAL_SUBSCR_MODE'";
		mysql_query($sql);
		$sql = "DELETE FROM config where `key`='PAYPAL_SUBSCR_BUTTON_URL'";
		mysql_query($sql);
		
		

	}

	function payment_button($order_id) {

		global $label;

		$sql = "SELECT * from orders where order_id='".$order_id."'";
		$result = mysql_query($sql) or die(mysql_error().$sql);
		$order_row = mysql_fetch_array($result);

		$is_subscription = false;
		if (($order_row['days_expire']>0)&&(PAYPAL_SUBSCR_MODE=='YES')) {
			$is_subscription = true;

		}

		if (USE_PAYPAL_SUBSCR!='YES') {
			$is_subscription = false;

		}

		?>

		<center><b><?php echo $label['payment_paypal_head']; ?></b>
		
		
		<form action="https://<?php echo PAYPAL_SERVER; ?>/cgi-bin/webscr" name="form1" method="post" target="_parent">

		<center><?php echo $label['payment_paypal_accepts']; ?></center>
		<?php 
		if ($is_subscription) { 
		?>
			<input type="hidden" value="_xclick-subscriptions" name="cmd">
			<input type="hidden" name="p3" value="<?php echo $order_row['days_expire']; ?>">
			<input type="hidden" name="t3" value="D">
			<input type="hidden" name="src" value="1">
			<input type="hidden" name="sra" value="1">
		<?php 
		} else { 
		?>
		  <input type="hidden" value="_xclick" name="cmd">

		 <?php 
		} 
		?>
		<input type="hidden" value="<?php echo PAYPAL_EMAIL; ?>" name="business">
		<input type="hidden" value="<?php echo PAYPAL_IPN_URL; ?>" name="notify_url">
		<input type="hidden" value="<?php echo SITE_NAME; ?> Order #<?php echo $order_row[order_id];?>" name="item_name">
		<input type="hidden" value="<?php echo PAYPAL_RETURN_URL; ?>" name="return">
		<input type="hidden" value="<?php echo PAYPAL_CANCEL_RETURN_URL; ?>" name="cancel_return"/>
		<input type="hidden" value="<?php echo pp_prefix_order_id($order_row[order_id]);?>" name="invoice" >
		<?php 
		if ($is_subscription) { 
		?>

			<input type="hidden" name="a3" value="<?php echo convert_to_currency($order_row['price'], $order_row['currency'], PAYPAL_CURRENCY); ?>">
		<?php
		} else {
		?> 
			<input type="hidden" value="<?php echo convert_to_currency($order_row['price'], $order_row['currency'], PAYPAL_CURRENCY); ?>" name="amount">
		<?php
		}
		?>
		<input type="hidden" value="<?php echo $order_row[order_id];?>" name="item_number">
		<input type="hidden" value="<?php echo $order_row[user_id];?>" name="custom">
		<input type="hidden" value="<?php echo PAYPAL_PAGE_STYLE;?>" name="page_style">

		<input type="hidden" value="1" name="no_shipping"/>
		<input type="hidden" value="1" name="no_note"/>
		<input type="hidden" value="<?php echo PAYPAL_CURRENCY;?>" name="currency_code">
		<p align="center">
		<?php 
		if ($is_subscription) { 
		?>
			<input type="image" src="<?php echo PAYPAL_SUBSCR_BUTTON_URL; ?>" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">

		<?php 
		} else { 
		?>
			<input target="_parent" type="image" alt="<?php echo $label['payment_paypal_bttn_alt']; ?>" src="<?php echo PAYPAL_BUTTON_URL; ?>" border="0" name="submit" >
		<?php 
		} ?>
		</p>
	</form>

		<?php

	}

	function config_form() {

		if ($_REQUEST['action']=='save') {

			$paypal_email = $_REQUEST['paypal_email'];
			$paypal_server = $_REQUEST['paypal_server'];
			$paypal_ipn_url = $_REQUEST['paypal_ipn_url'];
			$paypal_return_url = $_REQUEST['paypal_return_url'];
			$paypal_cancel_return_url = $_REQUEST['paypal_cancel_return_url'];
			$paypal_page_style = $_REQUEST['paypal_page_style'];
			$paypal_currency = $_REQUEST['paypal_currency'];
			$paypal_button_url = $_REQUEST['paypal_button_url'];
			$paypal_auth_token = $_REQUEST['paypal_auth_token'];
			$paypal_subscr_mode = $_REQUEST['paypal_subscr_mode'];
			$paypal_subscr_button_url = $_REQUEST['paypal_subscr_button_url'];

		} else {

			$paypal_email = PAYPAL_EMAIL;
			$paypal_server = PAYPAL_SERVER;
			$paypal_ipn_url = PAYPAL_IPN_URL;
			$paypal_return_url = PAYPAL_RETURN_URL;
			$paypal_cancel_return_url = PAYPAL_CANCEL_RETURN_URL;
			$paypal_page_style = PAYPAL_PAGE_STYLE;
			$paypal_currency = PAYPAL_CURRENCY;
			$paypal_button_url = PAYPAL_BUTTON_URL;
			$paypal_auth_token = PAYPAL_AUTH_TOKEN;
			$paypal_subscr_mode = PAYPAL_SUBSCR_MODE;
			$paypal_subscr_button_url = PAYPAL_SUBSCR_BUTTON_URL;

		}

		$host = $_SERVER['SERVER_NAME']; // hostname
		  $http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
		  $http_url = explode ("/", $http_url);
		  array_pop($http_url); // get rid of filename
		  array_pop($http_url); // get rid of /admin
		  $http_url = implode ("/", $http_url);

		?>
		<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
		<table border="0" cellpadding="5" cellspacing="2" style="border-style:groove" id="AutoNumber1" width="100%" bgcolor="#FFFFFF">
    <tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">PayPal 
      Email address</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="paypal_email" size="33" value="<?php echo $paypal_email; ?>">Note: Ensure that IPN is enabled for this PayPal account. </font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">PayPal 
      Server host</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <select name="paypal_server">
	  <option value="www.paypal.com" <?php if ($paypal_server == 'www.paypal.com' ) { echo " selected ";}  ?> >PayPal [www.paypal.com]</option>
	  <option value="www.sandbox.paypal.com" <?php if ($paypal_server == 'www.sandbox.paypal.com' ) { echo " selected ";}  ?>>PayPal Sand Box [www.sandbox.paypal.com]</option>
	  </select> Note: If you want to test the paypal IPN functions, you can set the host to PayPal's sand-box server. Set to www.paypal.com once your website goes live)
	  </font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Paypal 
      Identity token</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="paypal_auth_token" size="50" value="<?php echo $paypal_auth_token; ?>"><br><font face="Verdana" size="1">Required for PDT (Payment Data Transfer). You can find the Identity token under Profile -> 'Website Payment Prefrences' page in your PayPal account. Also, turn 'Auto Return' to 'On', and insert the 'PayPal Return URL' that is show here below. If you have another website using the Auto Return feature then leave it as it is - the million dollar script will tell PayPal what to do to make sure it does not conflict with your other sites. </font></td>
    </tr>
	 
	 <tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Paypal 
      IPN URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="paypal_ipn_url" size="50" value="<?php echo $paypal_ipn_url; ?>"><br>Recommended: <b>http://<?php echo $host.$http_url."/payment/paypalIPN.php"; ?></font></td>
    </tr>
	 
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Paypal 
      Return URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="paypal_return_url" size="50" value="<?php echo $paypal_return_url; ?>"><br>(recommended: <b>http://<?php echo $host.$http_url."/users/thanks.php?m=".$this->className; ?></b> Note: This URL should also be entered as the 'Return URL' on the 'Website Payment prefrences in your PayPal account)</font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Paypal 
      Cancelled Return URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="paypal_cancel_return_url" size="50" value="<?php echo $paypal_cancel_return_url; ?>"><br>(eg. http://<?php echo $host.$http_url."/users/"; ?>)</font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Paypal 
      Page Style</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="paypal_page_style" size="50" value="<?php echo $paypal_page_style; ?>"><br>(Your PayPal account's page style. Defined in your paypal account's options.)</font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Paypal 
      Currency</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
	  <select name="paypal_currency">
		<option value="USD" <?php if ($paypal_currency=='USD') { echo " selected "; }  ?> >USD</option>
		<option value="AUD" <?php if ($paypal_currency=='AUD') { echo " selected "; }  ?> >AUD</option>
		<option value="EUR" <?php if ($paypal_currency=='EUR') { echo " selected "; }  ?> >EUR</option>
		<option value="CAD" <?php if ($paypal_currency=='CAD') { echo " selected "; }  ?> >CAD</option>
		<option value="JPY" <?php if ($paypal_currency=='JPY') { echo " selected "; }  ?> >JPY</option>
		<option value="GBP" <?php if ($paypal_currency=='GBP') { echo " selected "; }  ?> >GBP</option>
	  </select> (PayPal currently accepts 5 currencies, and the local currency amount, if not supported, will be converted during checkout)
     </td>
    </tr>
	
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Paypal 
      Button Image URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="paypal_button_url" size="50" value="<?php echo $paypal_button_url; ?>"><br></font></td>
    </tr>
	<!--
		<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Enable Subscriptions (Y/N)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
       <input type="radio" name="paypal_subscr_mode" value="YES"  <?php if ($paypal_subscr_mode=='YES') { echo " checked "; } ?> >Yes - Use PayPal's subscription features if the order has expiring pixels. Users will be billed automatically at the end of each period.<br>
	  <input type="radio" name="paypal_subscr_mode" value="NO"  <?php if ($paypal_subscr_mode=='NO') { echo " checked "; } ?> >No<br></font></td>
    </tr>
	-->
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Paypal 
      Subscription Button Image URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="paypal_subscr_button_url" size="50" value="<?php echo $paypal_subscr_button_url; ?>"><br></font></td>
    </tr>
	 <tr>
	
      <td  bgcolor="#e6f2ea" colspan=2><font face="Verdana" size="1"><input type="submit" value="Save">
	  </td>
	  </tr>
    
  </table>
  <input type="hidden" name="pay" value="<?php echo $_REQUEST['pay'];?>">
  <input type="hidden" name="action" value="save">
  </form>

  <?php

		

	}

	function save_config() {

	

		//$sql = "REPLACE INTO config (`key`, val) VALUES ('PAYPAL_ENABLED', 'N')";
		//mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('PAYPAL_EMAIL', '".$_REQUEST['paypal_email']."')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('PAYPAL_CURRENCY', '".$_REQUEST['paypal_currency']."')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('PAYPAL_BUTTON_URL', '".$_REQUEST['paypal_button_url']."')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('PAYPAL_IPN_URL', '".$_REQUEST['paypal_ipn_url']."')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('PAYPAL_RETURN_URL', '".$_REQUEST['paypal_return_url']."')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('PAYPAL_CANCEL_RETURN_URL', '".$_REQUEST['paypal_cancel_return_url']."')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('PAYPAL_PAGE_STYLE', '".$_REQUEST['paypal_page_style']."')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('PAYPAL_SERVER', '".$_REQUEST['paypal_server']."')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('PAYPAL_AUTH_TOKEN', '".$_REQUEST['paypal_auth_token']."')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('PAYPAL_SUBSCR_MODE', '".$_REQUEST['paypal_subscr_mode']."')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('PAYPAL_SUBSCR_BUTTON_URL', '".$_REQUEST['paypal_subscr_button_url']."')";
		mysql_query($sql);

		

		

	}

	// true or false
	function is_enabled() {

		$sql = "SELECT val from config where `key`='PAYPAL_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);
		$row = mysql_fetch_array($result);
		if ($row['val']=='Y') {
			return true;

		} else {
			return false;

		}

	}

	// true or false
	function is_installed() {

		$sql = "SELECT val from config where `key`='PAYPAL_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);
		//$row = mysql_fetch_array($result);

		if (mysql_num_rows($result)>0) {
			return true;

		} else {
			return false;

		}

	}

	function enable() {

		$sql = "UPDATE config set val='Y' where `key`='PAYPAL_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);


	}

	function disable() {

		$sql = "UPDATE config set val='N' where `key`='PAYPAL_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);

	}

	function process_payment_return() {

		global $label;

		// read the post from PayPal system and add 'cmd'
		$req = 'cmd=_notify-synch';

		$tx_token = $_GET['tx'];
		$auth_token = PAYPAL_AUTH_TOKEN;
		$req .= "&tx=$tx_token&at=$auth_token";

		//print_r($_REQUEST);


		

	}

}



?>