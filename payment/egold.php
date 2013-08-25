<?php
/**
 * @version		$Id: egold.php 69 2010-09-12 01:31:15Z ryan $
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

$_PAYMENT_OBJECTS['egold'] = new egold;//"paypal";

//define (IPN_LOGGING, 'Y');


function eg_mail_error($msg) {

	$date = date("D, j M Y H:i:s O"); 
	
	$headers = "From: ". SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Reply-To: ".SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Return-Path: ".SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "X-Mailer: PHP" ."\r\n";
	$headers .= "Date: $date" ."\r\n"; 
	$headers .= "X-Sender-IP: $REMOTE_ADDR" ."\r\n";

	$entry_line =  "(ccavenue error detected) $msg\r\n "; 
	$log_fp = @fopen("logs.txt", "a"); 
	@fputs($log_fp, $entry_line); 
	@fclose($log_fp);


	@mail(SITE_CONTACT_EMAIL, "Error message from ".SITE_NAME." Jamit egold script. ", $msg, $headers);

}

function eg_log_entry ($entry_line) {

	if (IPN_LOGGING == 'Y') {

		$entry_line =  "$entry_line\r\n "; 
		$log_fp = @fopen("logs.txt", "a"); 
		@fputs($log_fp, $entry_line); 
		@fclose($log_fp);

	}


}



if ($_POST['PAYMENT_ID']!='') {
	
	$alt_hash = strtoupper (md5(EGOLD_ALTERNATE_PASSPHRASE));


	$hash = strtoupper (md5 ($_POST['PAYMENT_ID'].":".$_POST['PAYEE_ACCOUNT'].":".$_POST['PAYMENT_AMOUNT'].":".$_POST['PAYMENT_UNITS'].":".$_POST['PAYMENT_METAL_ID'].":".$_POST['PAYMENT_BATCH_NUM'].":".$_POST['PAYER_ACCOUNT'].":".$alt_hash.":".$_POST['ACTUAL_PAYMENT_OUNCES'].":".$_POST['USD_PER_OUNCE'].":".$_POST['FEEWEIGHT'].":".$_POST['TIMESTAMPGMT']));

	

	$sql = "SELECT * FROM orders where order_id='".$_POST['PAYMENT_ID']."'";
	$result = mysql_query ($sql) or die (mysql_error().$sql);
	$order_row = mysql_fetch_array($result);

	if ($hash == $_POST['HANDSHAKE_HASH']) {

		$egold = new egold;

		debit_transaction($_POST['PAYMENT_ID'], $_POST['PAYMENT_AMOUNT'], $egold->get_currency(), "eg".$_POST['PAYMENT_ID'], $reason_code, 'egold');
		complete_order ($_POST['CUST_NUM'], $_POST['ORDER_NUM']);
				

	} else {

		echo "Invalid signiture";


	}




}






###########################################################################
# Payment Object



class egold {

	var $name="E-Gold";
	var $description = 'E-Gold';
	var $className="egold";
	

	function egold() {

		global $label;
		$this->description = $label['payment_egold_description'];
		if ($this->is_installed()) {

			$sql = "SELECT * FROM config where `key`='EGOLD_ENABLED' OR `key`='EGOLD_PAYMENT_UNITS' OR `key`='EGOLD_PAYEE_ACCOUNT' OR `key`='EGOLD_PAYMENT_METAL_ID' OR `key`='EGOLD_STATUS_URL' OR `key`='EGOLD_PAYMENT_URL' OR `key`='EGOLD_NOPAYMENT_URL' OR `key`='EGOLD_ALTERNATE_PASSPHRASE' ";
			$result = mysql_query($sql) or die (mysql_error().$sql);

			while ($row=mysql_fetch_array($result)) {

				define ($row['key'], $row['val']);

			}

			

		}


	}

	var $egold_units = array(
		'USD' => '1',
		'CAD' => '2',
		'FRF' => '33',
		'CHF' => '41',
		'GBP' => '44',
		'DEM' => '49',
		'AUD' => '61',
		'JPY' => '81',
		'EUR' => '85',
		'BEF' => '86',
		'ATS' => '97',
		'GRD' => '88',
		'ESP' => '89',
		'IEP' => '90',
		'ITL' => '91',
		'LUF' => '92',
		'NLG' => '93',
		'PTE' => '94',
		'FIM' => '95',
		'g' => '8888',
		'oz' => '9999'
	);

	function egold_unit_to_currency ($unit_code) {
	
		$temp = array_flip($this->egold_units);
		return $temp[$unit_code];

	}

	function get_currency() {

		return $this->egold_unit_to_currency (EGOLD_PAYMENT_UNITS);

	}


	function install() {

		echo "Install E-gold..<br>";

		$host = $_SERVER['SERVER_NAME']; // hostname
		$http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
		$http_url = explode ("/", $http_url);
		array_pop($http_url); // get rid of filename
		array_pop($http_url); // get rid of /admin
		$http_url = implode ("/", $http_url);

	
		$sql = "REPLACE INTO config (`key`, val) VALUES ('EGOLD_ENABLED', 'N')";
		mysql_query($sql);
		
		$sql = "REPLACE INTO config (`key`, val) VALUES ('EGOLD_PAYMENT_UNITS', 'USD')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('EGOLD_PAYEE_ACCOUNT', '')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('EGOLD_PAYMENT_METAL_ID', '1')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('EGOLD_ALTERNATE_PASSPHRASE', '1')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('EGOLD_STATUS_URL', 'http://$host".$http_url."/payment/egold.php"."')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('EGOLD_PAYMENT_URL', 'http://$host".$http_url."/users/index.php"."')";
		mysql_query($sql);


		$sql = "REPLACE INTO config (`key`, val) VALUES ('EGOLD_NOPAYMENT_URL', 'http://$host".$http_url."/users/orders.php"."')";
		mysql_query($sql);


		
		
	}

	function uninstall() {

		echo "Uninstall egold..<br>";

	
		$sql = "DELETE FROM config where `key`='EGOLD_ENABLED'";
		mysql_query($sql);
		
		$sql = "DELETE FROM config where `key`='EGOLD_PAYMENT_UNITS'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='EGOLD_PAYEE_ACCOUNT'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='EGOLD_STATUS_URL'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='EGOLD_PAYMENT_URL'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='EGOLD_NOPAYMENT_URL'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='EGOLD_PAYMENT_METAL_ID'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='EGOLD_ALTERNATE_PASSPHRASE'";
		mysql_query($sql);
		
		
	}

	function payment_button($order_id) {

		global $label;

		$sql = "SELECT * from orders where order_id='".$order_id."'";
		$result = mysql_query($sql) or die(mysql_error().$sql);
		$order_row = mysql_fetch_array($result);
		


		?>

<form action="https://www.e-gold.com/sci_asp/payments.asp" method="post" >
<input type="hidden" name="PAYEE_ACCOUNT" value="<?php echo EGOLD_PAYEE_ACCOUNT; ?>">
<input type="hidden" name="PAYEE_NAME" value="<?php echo SITE_NAME; ?>">
<input type="hidden" name="PAYMENT_AMOUNT"  value="<?php echo convert_to_currency($order_row['price'], $order_row['currency'], $this->get_currency() ); ?>">
<input type="hidden" name="PAYMENT_UNITS" value="<?php echo EGOLD_PAYMENT_UNITS; ?>">
<input type="hidden" name="PAYMENT_METAL_ID" value="<?php echo EGOLD_PAYMENT_METAL_ID; ?>">
<input type="hidden" name="PAYMENT_ID" value="<?php echo $order_row['order_id'] ?>">
<input type="hidden" name="STATUS_URL" value="<?php echo EGOLD_STATUS_URL; ?>">

<input type="hidden" name="PAYMENT_URL" value="<?php echo EGOLD_PAYMENT_URL; ?>">
<input type="hidden" name="PAYMENT_URL_METHOD" value="POST">
<input type="hidden" name="NOPAYMENT_URL" value="<?php echo EGOLD_NOPAYMENT_URL; ?>">
<input type="hidden" name="NOPAYMENT_URL_METHOD" value="POST">
<input type="hidden" name="BAGGAGE_FIELDS" value="ORDER_NUM CUST_NUM">

<input type="hidden" name="ORDER_NUM" value="<?php echo $order_row['order_id'];?>">
<input type="hidden" name="CUST_NUM" value="<?php echo $order_row['user_id'];?>">




<input type="submit" value="<?php echo $label['pay_by_egold_button'];?>">
</form>

		

		<?php

	}

	function config_form() {

		if ($_REQUEST['action']=='save') {

			$egold_payee_account = $_REQUEST['egold_payee_account'];
			$egold_payment_units = $_REQUEST['egold_payment_units'];
			$egold_payment_metal_id = $_REQUEST['egold_payment_metal_id'];
			$egold_status_url = $_REQUEST['egold_status_url'];
			$egold_payment_url = $_REQUEST['egold_payment_url'];
			$egold_nopayment_url = $_REQUEST['egold_nopayment_url'];
			$egold_alternate_passphrase = $_REQUEST['egold_alternate_passphrase'];
			
		} else {

			$egold_payee_account = EGOLD_PAYEE_ACCOUNT;
			$egold_payment_units = EGOLD_PAYMENT_UNITS;
			$egold_payment_metal_id = EGOLD_PAYMENT_METAL_ID;
			$egold_status_url = EGOLD_STATUS_URL;
			$egold_payment_url = EGOLD_PAYMENT_URL;
			$egold_nopayment_url = EGOLD_NOPAYMENT_URL;
			$egold_alternate_passphrase = EGOLD_ALTERNATE_PASSPHRASE;
		
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
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Egold 
      Payee Account</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="egold_payee_account" size="33" value="<?php echo $egold_payee_account; ?>"></font></td>
    </tr>



	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Egold  
      Payment Units</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <select name="egold_payment_units"  value="<?php echo $egold_payment_units; ?>"> 
	  <option value="1" <?php if ($egold_payment_units=='1') { echo ' selected ';}  ?> >USD</option>
	  <option value="2" <?php if ($egold_payment_units=='2') { echo ' selected ';}  ?> >CAD</option>
	  <option value="44" <?php if ($egold_payment_units=='44') { echo ' selected ';}  ?> >GBP</option>
	  <option value="61" <?php if ($egold_payment_units=='61') { echo ' selected ';}  ?> >AUD</option>
	  <option value="81" <?php if ($egold_payment_units=='81') { echo ' selected ';}  ?> >JPY</option>
	  <option value="85" <?php if ($egold_payment_units=='44') { echo ' selected ';}  ?> >EUR</option>
	  <option value="8888" <?php if ($egold_payment_units=='8888') { echo ' selected ';}  ?> >Gram (g)</option>
	  <option value="9999" <?php if ($egold_payment_units=='9999') { echo ' selected ';}  ?> >Troy ounce (oz)</option>
	 
	  </select> 
	  </font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Egold  
      Payment Metal</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <select name="egold_payment_metal_id"  value="<?php echo $egold_payment_metal_id; ?>"> 
	  <option value="0" <?php if ($egold_payment_metal_id=='0') { echo ' selected ';}  ?> >Buyer's Choice</option>
	  <option value="1" <?php if ($egold_payment_metal_id=='1') { echo ' selected ';}  ?> >Gold</option>
	  <option value="2" <?php if ($egold_payment_metal_id=='2') { echo ' selected ';}  ?> >Silver</option>
	  <option value="3" <?php if ($egold_payment_metal_id=='3') { echo ' selected ';}  ?> >Platinum</option>
	  <option value="4" <?php if ($egold_payment_metal_id=='4') { echo ' selected ';}  ?> >Palladium</option>
	 
	 
	  </select> 
	  </font></td>
    </tr>

	
	
	 
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">E-Gold 
      Status URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="egold_status_url" size="50" value="<?php echo $egold_status_url; ?>"><br>(eg. http://<?php echo $host.$http_url."/payment/egold.php"; ?>)</font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">E-Gold 
      Payment URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="egold_payment_url" size="33" value="<?php echo $egold_payment_url; ?>"> I.e. 'Thank you page', (eg. http://<?php echo $host.$http_url."/users/index.php"; ?>) </font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">E-Gold 
      Nopayment URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="egold_nopayment_url" size="33" value="<?php echo $egold_nopayment_url; ?>"> I.e. 'Payment cancelled page', (eg. http://<?php echo $host.$http_url."/users/orders.php"; ?>) </font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Egold 
      Alternate Passphrase</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="egold_alternate_passphrase" size="33" value="<?php echo $egold_alternate_passphrase; ?>"> (You must set this in your e-gold account. Go to Account Info -> Passphrase -> and enter your 'New Alternate Passphrase' there.) </font></td>
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

		$code = $this->egold_unit_to_currency ($_REQUEST['egold_payment_units']);
				
		$rate = get_currency_rate($code);

		if ($rate=='') {

			echo "<font color='red'><b>Note: The selected 'Egold payment unit' is not defined in the system. Please add define this as a currency in the 'Currencies' section or select another payment unit.</b></font>";

		}

	
		$sql = "REPLACE INTO config (`key`, val) VALUES ('EGOLD_PAYEE_ACCOUNT', '".$_REQUEST['egold_payee_account']."')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('EGOLD_PAYMENT_UNITS', '".$_REQUEST['egold_payment_units']."')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('EGOLD_PAYMENT_METAL_ID', '".$_REQUEST['egold_payment_metal_id']."')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('EGOLD_STATUS_URL', '".$_REQUEST['egold_status_url']."')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('EGOLD_PAYMENT_URL', '".$_REQUEST['egold_payment_url']."')";
		mysql_query($sql);	
		$sql = "REPLACE INTO config (`key`, val) VALUES ('EGOLD_NOPAYMENT_URL', '".$_REQUEST['egold_nopayment_url']."')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('EGOLD_ALTERNATE_PASSPHRASE', '".$_REQUEST['egold_alternate_passphrase']."')";
		mysql_query($sql);	
	

	}

	// true or false
	function is_enabled() {

		$sql = "SELECT val from config where `key`='EGOLD_ENABLED' ";
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

		$sql = "SELECT val from config where `key`='EGOLD_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);
		//$row = mysql_fetch_array($result);

		if (mysql_num_rows($result)>0) {
			return true;

		} else {
			return false;

		}

	}

	function enable() {

		$sql = "UPDATE config set val='Y' where `key`='EGOLD_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);


	}

	function disable() {

		$sql = "UPDATE config set val='N' where `key`='EGOLD_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);

	}

}



?>