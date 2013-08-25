<?php
/**
 * @version		$Id: moneybookers.php 69 2010-09-12 01:31:15Z ryan $
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

$_PAYMENT_OBJECTS['moneybookers'] = new moneybookers;

define (IPN_LOGGING, 'Y');


function mb_mail_error($msg) {

	$date = date("D, j M Y H:i:s O"); 
	
	$headers = "From: ". SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Reply-To: ".SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Return-Path: ".SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "X-Mailer: PHP" ."\r\n";
	$headers .= "Date: $date" ."\r\n"; 
	$headers .= "X-Sender-IP: $REMOTE_ADDR" ."\r\n";

	$entry_line =  "(moneybookers error detected) $msg\r\n "; 
	$log_fp = @fopen("logs.txt", "a"); 
	@fputs($log_fp, $entry_line); 
	@fclose($log_fp);


	@mail(SITE_CONTACT_EMAIL, "Error message from ".SITE_NAME." Moneybookers payment script. ", $msg, $headers);

}

function mb_log_entry ($entry_line) {

	if (IPN_LOGGING == 'Y') {

		$entry_line =  "$entry_line\r\n "; 
		$log_fp = @fopen("logs.txt", "a"); 
		@fputs($log_fp, $entry_line); 
		@fclose($log_fp);

	}


}

if ($_POST['merchant_id']!='') { 

	$merchant_id = $_POST['merchant_id'];
	$transaction_id = $_POST['transaction_id'];
	$secret = strtoupper (MONEYBOOKERS_SECRET_WORD);
	$mb_amount = $_POST['mb_amount'];
	$mb_currency = $_POST['currency'];
	$status = $_POST['status'];
	$md5sig = $_POST['md5sig'];
	$status = $_POST['Status'];

	$working_sig = strtoupper (md5($merchant_id.$transaction_id.$secret.$mb_amount.$mb_currency.$status));

	$sql = "SELECT * FROM orders where order_id='".$_POST['transaction_id']."'";
	$result = mysql_query ($sql) or die (mysql_error().$sql);
	$order_row = mysql_fetch_array($result);

	if ($working_sig == $md5sig) {

		switch ($status) {

			case "-2": // failed
				break;
			case "2": // processed
				debit_transaction($transaction_id, $mb_amount, MONEYBOOKERS_CURRENCY, "mb".$transaction_id, $reason_code, 'moneybookers');
				complete_order ($order_row['user_id'], $_POST['transaction_id']);
				break;
			case "1": // scheduled (wait for 2 or -2)
				break;
			case "0": // pending
				break;
			case "-1": // cancelled
				break;

		}


	} else {

		echo "Invalid signiture";


	}




}






###########################################################################
# Payment Object



class moneybookers {

	var $name="moneybookers.com";
	var $description = 'moneybookers.com - Visa & MasterCard payments';
	var $className="moneybookers";
	

	function moneybookers() {

		global $label;
		$this->description = $label['payment_moneybookers_description'];
		$this->name = $label['payment_moneybookers_name'];

		if ($this->is_installed()) {

			$sql = "SELECT * FROM config where `key`='MONEYBOOKERS_ENABLED' OR `key`='MONEYBOOKERS_CURRENCY' OR `key`='MONEYBOOKERS_EMAIL' OR `key`='MONEYBOOKERS_STATUS_URL' OR `key`='MONEYBOOKERS_RETURN_URL' OR `key`='MONEYBOOKERS_CANCEL_URL' OR `key`='MONEYBOOKERS_SECRET_WORD' OR `key`='MONEYBOOKERS_LANGUAGE'";
			$result = mysql_query($sql) or die (mysql_error().$sql);

			while ($row=mysql_fetch_array($result)) {

				define ($row['key'], $row['val']);

			}

			

		}


	}

	function get_currency() {

		return MONEYBOOKERS_CURRENCY;

	}


	function install() {

		echo "Install moneybookers..<br>";

		$host = $_SERVER['SERVER_NAME']; // hostname
		$http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
		$http_url = explode ("/", $http_url);
		array_pop($http_url); // get rid of filename
		array_pop($http_url); // get rid of /admin
		$http_url = implode ("/", $http_url);

	
		$sql = "REPLACE INTO config (`key`, val) VALUES ('MONEYBOOKERS_ENABLED', 'N')";
		mysql_query($sql);
		
		$sql = "REPLACE INTO config (`key`, val) VALUES ('MONEYBOOKERS_CURRENCY', 'USD')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('MONEYBOOKERS_LANGUAGE', 'EN')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('MONEYBOOKERS_EMAIL', '')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('MONEYBOOKERS_STATUS_URL', 'http://$host".$http_url."/payment/moneybookers.php"."')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('MONEYBOOKERS_RETURN_URL', 'http://$host".$http_url."/users/index.php"."')";
		mysql_query($sql);


		$sql = "REPLACE INTO config (`key`, val) VALUES ('MONEYBOOKERS_CANCEL_URL', 'http://$host".$http_url."/users/orders.php"."')";
		mysql_query($sql);


		$sql = "REPLACE INTO config (`key`, val) VALUES ('MONEYBOOKERS_SECRET_WORD', '')";
		mysql_query($sql);
		
		
	}

	function uninstall() {

		echo "Uninstall Moneybookers..<br>";

	
		$sql = "DELETE FROM config where `key`='MONEYBOOKERS_ENABLED'";
		mysql_query($sql);
		
		$sql = "DELETE FROM config where `key`='MONEYBOOKERS_CURRENCY'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='MONEYBOOKERS_EMAIL'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='MONEYBOOKERS_LANGUAGE'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='MONEYBOOKERS_STATUS_URL'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='MONEYBOOKERS_RETURN_URL'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='MONEYBOOKERS_CANCEL_URL'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='MONEYBOOKERS_SECRET_WORD'";
		mysql_query($sql);

		
		
	}

	function payment_button($order_id) {

		global $label;

		$sql = "SELECT * from orders where order_id='".$order_id."'";
		$result = mysql_query($sql) or die(mysql_error().$sql);
		$order_row = mysql_fetch_array($result);
		

		

		?>

<form action="https://www.moneybookers.com/app/payment.pl" method="post" target="_blank">
<input type="hidden" name="pay_to_email" value="<?php echo MONEYBOOKERS_EMAIL; ?>">
<input type="hidden" name="status_url" value="<?php echo MONEYBOOKERS_STATUS_URL; ?>">
<input type="hidden" name="language" value="<?php echo MONEYBOOKERS_LANGUAGE; ?>">
<input type="hidden" name="transaction_id" value="<?php echo $order_row['order_id']; ?>">
<input type="hidden" name="amount" value="<?php echo convert_to_currency($order_row['price'], $order_row['currency'], MONEYBOOKERS_CURRENCY); ?>">
<input type="hidden" name="currency" value="<?php echo MONEYBOOKERS_CURRENCY; ?>">
<input type="hidden" name="cancel_url" value="<?php echo MONEYBOOKERS_CANCEL_URL; ?>">
<input type="hidden" name="return_url" value="<?php echo MONEYBOOKERS_RETURN_URL; ?>">
<input type="hidden" name="detail1_description" value="<?php echo $label['payment_moneybookers_descr']; ?>">
<input type="hidden" name="detail1_text" value="<?php echo SITE_NAME; ?>">
<input type="submit" value="<?php echo $label['pay_by_moneybookers_button'];?>">
</form>

		<?php

	}

	function config_form() {

		if ($_REQUEST['action']=='save') {

			$moneybookers_email = $_REQUEST['moneybookers_email'];
			$moneybookers_language = $_REQUEST['moneybookers_language'];
			$moneybookers_currency = $_REQUEST['moneybookers_currency'];
			$moneybookers_status_url = $_REQUEST['moneybookers_status_url'];
			$moneybookers_return_url = $_REQUEST['moneybookers_return_url'];
			$moneybookers_cancel_url = $_REQUEST['moneybookers_cancel_url'];
			$moneybookers_secret_word = $_REQUEST['moneybookers_secret_word'];
			

		} else {

			$moneybookers_email = MONEYBOOKERS_EMAIL;
			$moneybookers_language = MONEYBOOKERS_LANGUAGE;
			$moneybookers_currency = MONEYBOOKERS_CURRENCY;
			$moneybookers_status_url = MONEYBOOKERS_STATUS_URL;
			$moneybookers_return_url = MONEYBOOKERS_RETURN_URL;
			$moneybookers_cancel_url = MONEYBOOKERS_CANCEL_URL;
			$moneybookers_secret_word = MONEYBOOKERS_SECRET_WORD;

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
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Moneybookers 
      Email</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="moneybookers_email" size="33" value="<?php echo $moneybookers_email; ?>"></font></td>
    </tr>



	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Moneybookers 
      Language</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <select name="moneybookers_language"  > 
	 <option value="EN" <?php if ($moneybookers_language=='EN') { echo ' selected ';}  ?> >English</option>
	 <option value="DE" <?php if ($moneybookers_language=='DE') { echo ' selected ';}  ?>>German</option>
	 <option value="ES" <?php if ($moneybookers_language=='ES') { echo ' selected ';}  ?>>Spanish</option>
	 <option value="FR" <?php if ($moneybookers_language=='FR') { echo ' selected ';}  ?>>French</option>
	 <option value="IT" <?php if ($moneybookers_language=='IT') { echo ' selected ';}  ?>>Italian</option>
	 <option value="PL" <?php if ($moneybookers_language=='PL') { echo ' selected ';}  ?>>Polish</option>
	  </select> 
	  </font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Moneybookers 
      Currency</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <select name="moneybookers_currency"  value="<?php echo $moneybookers_currency; ?>"> 
	  <?php currency_option_list ($moneybookers_currency); ?>
	  </select>(Please select a currency that is supported by Moneybookers. If the currency is not on the list, you may add it under the Configuration section)
	  </font></td>
    </tr>
	
	 
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Moneybookers 
      Status URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="moneybookers_status_url" size="50" value="<?php echo $moneybookers_status_url; ?>"><br>(eg. http://<?php echo $host.$http_url."/payment/moneybookers.php"; ?>)</font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Moneybookers 
      Return URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="moneybookers_return_url" size="33" value="<?php echo $moneybookers_return_url; ?>"> I.e. 'Thank you page', (eg. http://<?php echo $host.$http_url."/users/index.php"; ?>) </font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Moneybookers 
      Cancel URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="moneybookers_cancel_url" size="33" value="<?php echo $moneybookers_cancel_url; ?>"> I.e. 'Payment cancelled page', (eg. http://<?php echo $host.$http_url."/users/orders.php"; ?>) </font></td>
    </tr>
	
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Moneybookers secret word</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="moneybookers_secret_word" size="50" value="<?php echo $moneybookers_secret_word; ?>"><br>(Note: The secret word MUST be submitted in the 'profile' section in lowercase. If you insert uppercase symbols, they will automatically be converted to lower case. The only restriction on your secret word is the length which must not exceed 10 characters. Non-alphanumeric symbols can be used. If the secret word is not shown in your profile, please contact merchantservices@moneybookers.com)</font></td>
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

	
		$sql = "REPLACE INTO config (`key`, val) VALUES ('MONEYBOOKERS_EMAIL', '".$_REQUEST['moneybookers_email']."')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('MONEYBOOKERS_LANGUAGE', '".$_REQUEST['moneybookers_language']."')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('MONEYBOOKERS_CURRENCY', '".$_REQUEST['moneybookers_currency']."')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('MONEYBOOKERS_STATUS_URL', '".$_REQUEST['moneybookers_status_url']."')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('MONEYBOOKERS_RETURN_URL', '".$_REQUEST['moneybookers_return_url']."')";
		mysql_query($sql);	
		$sql = "REPLACE INTO config (`key`, val) VALUES ('MONEYBOOKERS_CANCEL_URL', '".$_REQUEST['moneybookers_cancel_url']."')";
		mysql_query($sql);	
		$sql = "REPLACE INTO config (`key`, val) VALUES ('MONEYBOOKERS_SECRET_WORD', '".$_REQUEST['moneybookers_secret_word']."')";
		mysql_query($sql);	

	}

	// true or false
	function is_enabled() {

		$sql = "SELECT val from config where `key`='MONEYBOOKERS_ENABLED' ";
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

		$sql = "SELECT val from config where `key`='MONEYBOOKERS_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);
		//$row = mysql_fetch_array($result);

		if (mysql_num_rows($result)>0) {
			return true;

		} else {
			return false;

		}

	}

	function enable() {

		$sql = "UPDATE config set val='Y' where `key`='MONEYBOOKERS_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);


	}

	function disable() {

		$sql = "UPDATE config set val='N' where `key`='MONEYBOOKERS_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);

	}

}



?>