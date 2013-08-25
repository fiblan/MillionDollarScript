<?php
/**
 * @version		$Id: nochexAPC.php 69 2010-09-12 01:31:15Z ryan $
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

$_PAYMENT_OBJECTS['NOCHEX'] =  new NOCHEX;
define (IPN_LOGGING, 'N');

function nc_mail_error($msg) {

	$date = date("D, j M Y H:i:s O"); 
	
	$headers = "From: ". SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Reply-To: ".SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Return-Path: ".SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "X-Mailer: PHP" ."\r\n";
	$headers .= "Date: $date" ."\r\n"; 
	$headers .= "X-Sender-IP: $REMOTE_ADDR" ."\r\n";

	//$entry_line =  "(payal error detected) $msg\r\n "; 
	//$log_fp = @fopen("logs.txt", "a"); 
	//@fputs($log_fp, $entry_line); 
	//@fclose($log_fp);


	@mail(SITE_CONTACT_EMAIL, "Error message from ".SITE_NAME." Jamit nochexAPC script. ", $msg, $headers);

}

function log_entry ($entry_line) {

	if (IPN_LOGGING == 'Y') {

		$entry_line =  "NOCHEX:$entry_line\r\n "; 
		$log_fp = fopen("logs.txt", "a"); 
		fputs($log_fp, $entry_line); 
		fclose($log_fp);

	}


}

// check if we can post back to nochex
if (stristr(ini_get('disable_functions'), "fsockopen")) {
    nc_mail_error ( "<p>fsockopen is disabled on this server, this script can not post information to the nochex server for IPN confirmation.");
	echo "fsockopen() function is disabled on this server.";
    die();
}

if ($_POST['transaction_id']!='') {

	// read the post from nochex system and add 'cmd'
	$req = 'cmd=_notify-validate';

	foreach ($_POST as $key => $value) {
		
		if (get_magic_quotes_gpc()) {
			$value = stripslashes($value);
		}
		$value = urlencode($value);
		$req .= "&$key=$value";
		
	}

		$entry_line =  "$req"; 
		log_entry ($entry_line);

	// post back to nochex system to validate
	$header .= "POST /nochex.dll/apc/apc HTTP/1.0\r\n";
	$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
	$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
	$fp = fsockopen ("www.nochex.com", 80, $errno, $errstr, 30);

	$To_email = $_POST['To_email']; 
	$From_email = $_POST['From_email'];
	$transaction_id = $_POST['transaction_id'];
	$txn_id = $transaction_id;
	$transaction_date = $_POST['transaction_date'];
	$payment_date = $transaction_date;
	$order_id = $_POST['order_id'];
	$amount = $_POST['amount'];
	$security_key = $_POST['security_key'];
	$status = $_POST['status'];
	$txn_type = 'web_accept';
	$mc_gross = $amount;
	$mc_currency = "GBP";
	$order_id = substr($order_id, 1);
	if ($order_id!='') {
		//$invoice_row = get_product_invoice_row ($order_id);
		$sql = "select user_id FROM orders where order_id='".$order_id."'";
		$result = mysql_query ($sql) or nc_mail_error(mysql_error().$sql);
		$row = mysql_fetch_array($result);
	}
	$user_id=$row['user_id'];
	
	$item_number = $order_id;
	$invoice_id = $order_id;
	



	if (!$fp) {
	// HTTP ERROR
		$entry_line =  "HTTP ERROR! cannot post back to nochex\r\n "; 
		log_entry($entry_line);
	} else {

		fputs ($fp, $header . $req); // post to nochex


		while (!feof($fp)) {
		$res = fgets ($fp, 1024);

			$entry_line =  "$res"; 
			

			if (strcmp ($res, "AUTHORISED") == 0) {
				log_entry($entry_line);
				$VERIFIED = 1;
				$payment_status = 'Completed';
				// check that receiver_email is your Primary nochex email
				if(strcmp(strtolower(NOCHEX_EMAIL), strtolower($To_email))!=0) {
					nc_mail_error ("Possible fraud. Error with receiver_email. ".strtolower(NOCHEX_EMAIL)." != ".strtolower($To_email)."\n");
					log_entry("Possible fraud. Error with receiver_email. ".strtolower(NOCHEX_EMAIL)." != ".strtolower($To_email));
					$VERIFIED = false;	
				} 

				// check so that transactrion id cannot be reused

				$sql = "SELECT * FROM transactions WHERE txn_id='$txn_id' ";
				$result = mysql_query($sql) or die (mysql_error().$sql); 
				if (mysql_num_rows($result)>0) {
					nc_mail_error ("Possible fraud. Transaction id: $txn_id is already in the database. \n");
					log_entry("Possible fraud. Transaction id: $txn_id is already in the database.");
					$VERIFIED = false;	

				}
				
				$entry_line =  "verified: $res";
				log_entry($entry_line);
			}
			else if (strcmp ($res, "DECLINED") == 0) {
				log_entry($entry_line);
			// log for manual investigation
				$VERIFIED = false;
				$payment_status = 'Denied';
				
			}
		}
		fclose ($fp);


		// if VERIFIED=1 process payment
		if ($VERIFIED) { 

			switch ($payment_status) {
				
				case "Completed":
					// Funds successfully transferred
	
					complete_order ($user_id, $order_id);
					debit_transaction($order_id, $amount, 'GBP', $txn_id, $reason, 'NOCHEX');

					break;
					
				default:
					break;
					
			}


		}

	}

}

###########################################################################
# Payment Object



class NOCHEX {

	var $name="NOCHEX";
	var $description="NOCHEX - Credit Card Payments. Accepts British Pounds.";
	var $className="NOCHEX";
	

	function NOCHEX() {

		if ($this->is_installed()) {

			$sql = "SELECT * FROM config where `key`='NOCHEX__ENABLED' OR `key`='NOCHEX_LOGO_URL' OR `key`='NOCHEX_CANCEL_RETURN_URL' OR `key`='NOCHEX_RETURN_URL' OR `key`='NOCHEX_APC_URL' OR `key`='NOCHEX_BUTTON_URL' OR `key`='NOCHEX_EMAIL' OR `key`='NOCHEX_CURRENCY'";
			$result = mysql_query($sql) or die (mysql_error().$sql);

			while ($row=mysql_fetch_array($result)) {

				define ($row['key'], $row['val']);

			}

		}

	}

	function get_currency() {

		return NOCHEX_CURRENCY;

	}


	function install() {

		$host = $_SERVER['SERVER_NAME']; // hostname
		$http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
		$http_url = explode ("/", $http_url);
		array_pop($http_url); // get rid of filename
		array_pop($http_url); // get rid of /admin
		$http_url = implode ("/", $http_url);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('NOCHEX_EMAIL', '')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('NOCHEX_ENABLED', 'N')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('NOCHEX_LOGO_URL', '')";
		mysql_query($sql);
		//$sql = "REPLACE INTO config (`key`, val, descr) VALUES ('_2CO_PRODUCT_ID', '1', '# Your 2CO seller ID number.')";
		//mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('NOCHEX_CANCEL_RETURN_URL', '')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('NOCHEX_RETURN_URL', '')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('NOCHEX_APC_URL', 'http://". $host.$http_url."/payment/nochexAPC.php')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('NOCHEX_BUTTON_URL', 'http://support.nochex.com/web/images/cardsboth2.gif')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('NOCHEX_CURRENCY', 'GBP')";
		mysql_query($sql);

	}

	function uninstall() {

		$sql = "DELETE FROM config where `key`='NOCHEX_EMAIL'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='NOCHEX_ENABLED'";
		mysql_query($sql);
		$sql = "DELETE FROM config where `key`='NOCHEX_LOGO_URL'";
		mysql_query($sql);
		//$sql = "REPLACE INTO config (`key`, val, descr) VALUES ('_2CO_PRODUCT_ID', '1', '# Your 2CO seller ID number.')";
		//mysql_query($sql);
		$sql = "DELETE FROM config where `key`='NOCHEX_CANCEL_RETURN_URL'";
		mysql_query($sql);
		$sql = "DELETE FROM config where `key`='NOCHEX_RETURN_URL'";
		mysql_query($sql);
		$sql = "DELETE FROM config where `key`='NOCHEX_APC_URL'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='NOCHEX_BUTTON_URL'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='NOCHEX_CURRENCY'";
		mysql_query($sql);


	}

	function payment_button($order_id) {

		$sql = "SELECT * from orders where order_id='".$order_id."'";
		$result = mysql_query($sql) or die(mysql_error().$sql);
		$order_row = mysql_fetch_array($result);

		
		?>

		<form action="https://www.nochex.com/nochex.dll/checkout" name="form1" method="post" target="_parent">
		  
		  <input type="hidden" value="<?php echo NOCHEX_EMAIL; ?>" name="email"/>
		  <input type="hidden" value="<?php echo number_format(convert_to_currency($order_row[price], $order_row[currency], NOCHEX_CURRENCY), 2, '.', ''); ?>" name="amount"/>
		  <input type="hidden" value="<?php echo $order_row[order_id]; ?>" name="ordernumber" />
		  <input type="hidden" value="<?php echo $item_name; ?>" name="description" />
		  <?php if (trim(NOCHEX_LOGO_URL)!='') {?>
		  <input type="hidden" value="<?php echo NOCHEX_LOGO_URL; ?>" name="logo" />
		  <?php } ?>
		  <input type="hidden" value="<?php echo NOCHEX_APC_URL; ?>" name="responderurl"/>
		  
		  <input type="hidden" value="<?php echo NOCHEX_RETURN_URL; ?>" name="returnurl"/>
		  <input type="hidden" value="<?php echo NOCHEX_CANCEL_RETURN_URL; ?>" name="cancel"/>
<p align="center">
		  <input target="_parent" type="image" alt="I accept payment using NOCHEX" src="<?php echo NOCHEX_BUTTON_URL; ?>" border="0" name="submit" />
		  
	</P>	  
		  
		  </p>
	</form>

	<?php

	}

	function config_form() {

		if ($_REQUEST['action']=='save') {
		

			$nochex_email = $_REQUEST['nochex_email'];
			$nochex_apc_url = $_REQUEST['nochex_apc_url'];
			$nochex_subscr_apc_url = $_REQUEST['nochex_subscr_apc_url'];
			$nochex_return_url = $_REQUEST['nochex_return_url'];
			$nochex_cancel_return_url = $_REQUEST['nochex_cancel_return_url'];
			$nochex_logo_url = $_REQUEST['nochex_logo_url'];
			$nochex_button_url = $_REQUEST['nochex_button_url'];
			$nochex_currency = $_REQUEST['nochex_currency'];
		} else {
			$nochex_email = NOCHEX_EMAIL;
			$nochex_apc_url = NOCHEX_APC_URL;
			$nochex_subscr_apc_url = NOCHEX_SUBSCR_APC_URL;
			$nochex_return_url = NOCHEX_RETURN_URL;
			$nochex_cancel_return_url = NOCHEX_CANCEL_RETURN_URL;
			$nochex_logo_url = NOCHEX_LOGO_URL;
			$nochex_button_url = NOCHEX_BUTTON_URL;
			$nochex_currency = NOCHEX_CURRENCY;
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
      <td colspan="2"  bgcolor="#e6f2ea">
      <font face="Verdana" size="1"><b>NOCHEX Payment Settings</b><br>(Accepts British Pound)</font></td>
    </tr>
	<tr>
      <td width="20%" bgcolor="#e6f2ea"><font face="Verdana" size="1">NOCHEX Email</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="nochex_email" size="29" value="<?php echo $nochex_email; ?>"></font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">NOCHEX Payment 
      APC URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="nochex_apc_url" size="50" value="<?php echo $nochex_apc_url; ?>"><br>Recommended: <b>http://<?php echo $host.$http_url."/payment/nochexAPC.php"; ?></font></td>
    </tr>
		
	 
	 
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">NOCHEX 
      Return URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="nochex_return_url" size="50" value="<?php echo $nochex_return_url; ?>"><br>(eg. http://<?php echo $host.$http_url."/users/"; ?>)</font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">NOCHEX 
      Cancelled Return URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="nochex_cancel_return_url" size="50" value="<?php echo $nochex_cancel_return_url; ?>"><br>(eg. http://<?php echo $host.$http_url."/users/"; ?>)</font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Your  
      Custom Logo URL (optional)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="nochex_logo_url" size="50" value="<?php echo $nochex_logo_url; ?>"><br>(This should be on a HTTPS server. <b>Leave blank if you want no logo</b>. eg. https://www.example.com/images/mylogo.gif)</font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">NOCHEX 
      Checkout button  URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="nochex_button_url" size="50" value="<?php echo $nochex_button_url; ?>"><br>(eg. http://support.nochex.com/web/images/cardsboth2.gif)</font></td>
    </tr>
	
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">NOCHEX Currency</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <select  name="nochex_currency" ><option value="GBP">GBP</select></font></td>
    </tr>
	 <tr>
	
      <td  bgcolor="#e6f2ea" colspan=2><font face="Verdana" size="1"><input type="submit" value="Save">
	  </td>
	  </tr>
    
  </table>
  <input type="hidden" name="pay" value="<?php echo $_REQUEST['pay'];?>">
  <input type="hidden" name="action" value="save">


		<?php

	}

	function save_config() {

		$sql = "REPLACE INTO config (`key`, val) VALUES ('NOCHEX_EMAIL', '".$_REQUEST['nochex_email']."')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('NOCHEX_LOGO_URL', '".$_REQUEST['nochex_logo_url']."')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('NOCHEX_CANCEL_RETURN_URL', '".$_REQUEST['nochex_cancel_return_url']."')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('NOCHEX_RETURN_URL', '".$_REQUEST['nochex_return_url']."')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('NOCHEX_APC_URL', '".$_REQUEST['nochex_apc_url']."')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('NOCHEX_BUTTON_URL', '".$_REQUEST['nochex_button_url']."')";
		mysql_query($sql);


	}

	// true or false
	function is_enabled() {

		$sql = "SELECT val from `config` where `key`='NOCHEX_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);
		$row = mysql_fetch_array($result);
		if ($row['val']=='Y') {
			return true;

		} else {
			return false;

		}

	}


	function is_installed() {

		$sql = "SELECT val from config where `key`='NOCHEX_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);
		//$row = mysql_fetch_array($result);

		if (mysql_num_rows($result)>0) {
			return true;

		} else {
			return false;

		}

	}

	function enable() {

		$sql = "UPDATE config set val='Y' where `key`='NOCHEX_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);


	}

	function disable() {

		$sql = "UPDATE config set val='N' where `key`='NOCHEX_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);

	}


}
?>