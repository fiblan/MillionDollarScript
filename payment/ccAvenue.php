<?php
/**
 * @version		$Id: ccAvenue.php 69 2010-09-12 01:31:15Z ryan $
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

$_PAYMENT_OBJECTS['ccAvenue'] = new ccAvenue;//"paypal";

define (IPN_LOGGING, 'Y');


function cc_mail_error($msg) {

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


	@mail(SITE_CONTACT_EMAIL, "Error message from ".SITE_NAME." Jamit ccAvenue script. ", $msg, $headers);

}

function cc_log_entry ($entry_line) {

	if (IPN_LOGGING == 'Y') {

		$entry_line =  "$entry_line\r\n "; 
		$log_fp = @fopen("logs.txt", "a"); 
		@fputs($log_fp, $entry_line); 
		@fclose($log_fp);

	}


}


function cc_getchecksum($MerchantId,$Amount,$OrderId ,$URL,$WorkingKey)
{
	$str ="$MerchantId|$OrderId|$Amount|$URL|$WorkingKey";
	$adler = 1;
	$adler = cc_adler32($adler,$str);
	return $adler;
}

function cc_verifychecksum($MerchantId,$OrderId,$Amount,$AuthDesc,$CheckSum,$WorkingKey)
{
	$str = "$MerchantId|$OrderId|$Amount|$AuthDesc|$WorkingKey";
	$adler = 1;
	$adler = cc_adler32($adler,$str);
	
	if($adler == $CheckSum)
		return "true" ;
	else
		return "false" ;
}

function cc_adler32($adler , $str)
{
	$BASE =  65521 ;

	$s1 = $adler & 0xffff ;
	$s2 = ($adler >> 16) & 0xffff;
	for($i = 0 ; $i < strlen($str) ; $i++)
	{
		$s1 = ($s1 + Ord($str[$i])) % $BASE ;
		$s2 = ($s2 + $s1) % $BASE ;
			//echo "s1 : $s1 <BR> s2 : $s2 <BR>";

	}
	return cc_leftshift($s2 , 16) + $s1;
}

function cc_leftshift($str , $num)
{

	$str = DecBin($str);

	for( $i = 0 ; $i < (64 - strlen($str)) ; $i++)
		$str = "0".$str ;

	for($i = 0 ; $i < $num ; $i++) 
	{
		$str = $str."0";
		$str = substr($str , 1 ) ;
		//echo "str : $str <BR>";
	}
	return cc_cdec($str) ;
}

function cc_cdec($num)
{

	for ($n = 0 ; $n < strlen($num) ; $n++)
	{
	   $temp = $num[$n] ;
	   $dec =  $dec + $temp*pow(2 , strlen($num) - $n - 1);
	}

	return $dec;
}




#####################################################################################


###########################################################################
# Payment Object



class ccAvenue {

	var $name="ccAvenue";
	var $description="ccAvenue Secure Credit Card Payment";
	var $className="ccAvenue";
	

	function ccAvenue() {

		if ($this->is_installed()) {

			$sql = "SELECT * FROM config where `key`='CCAVENUE_ENABLED' OR `key`='CCAVENUE_CURRENCY' OR `key`='CCAVENUE_MERCHANT_ID' OR `key`='CCAVENUE_REDIRECT_URL' OR `key`='CCAVENUE_WORKING_KEY'";
			$result = mysql_query($sql) or die (mysql_error().$sql);

			while ($row=mysql_fetch_array($result)) {

				define ($row['key'], $row['val']);

			}

		}


	}

	function get_currency() {

		return CCAVENUE_CURRENCY;

	}


	function install() {

		echo "Installed ccAvenue..<br>";

		$host = $_SERVER['SERVER_NAME']; // hostname
		$http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
		$http_url = explode ("/", $http_url);
		array_pop($http_url); // get rid of filename
		array_pop($http_url); // get rid of /admin
		$http_url = implode ("/", $http_url);

	
		$sql = "REPLACE INTO config (`key`, val) VALUES ('CCAVENUE_ENABLED', 'N')";
		mysql_query($sql);
		
		$sql = "REPLACE INTO config (`key`, val) VALUES ('CCAVENUE_CURRENCY', 'USD')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('CCAVENUE_MERCHANT_ID', '')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('CCAVENUE_REDIRECT_URL', '"."')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('CCAVENUE_WORKING_KEY', '')";
		mysql_query($sql);
		
		
	}

	function uninstall() {

		echo "Uninstalled CC Avenue..<br>";

	
		$sql = "DELETE FROM config where `key`='CCAVENUE_ENABLED'";
		mysql_query($sql);
		
		$sql = "DELETE FROM config where `key`='CCAVENUE_CURRENCY'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='CCAVENUE_MERCHANT_ID'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='CCAVENUE_REDIRECT_URL'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='CCAVENUE_WORKING_KEY'";
		mysql_query($sql);

		
		
	}

	function payment_button($order_id) {

		global $label;

		$sql = "SELECT * from orders where order_id='".$order_id."'";
		$result = mysql_query($sql) or die(mysql_error().$sql);
		$order_row = mysql_fetch_array($result);
		

		$Checksum = cc_getCheckSum(CCAVENUE_MERCHANT_ID, convert_to_currency($order_row[price], $order_row[currency], CCAVENUE_CURRENCY), $order_id ,CCAVENUE_REDIRECT_URL, CCAVENUE_WORKING_KEY);

		?>

		<form method="post" action="https://www.ccavenue.com/shopzone/cc_details.jsp">
		<input type=hidden name=Merchant_Id value="<?php echo CCAVENUE_MERCHANT_ID; ?>">
		<input type=hidden name=Amount value="<?php echo convert_to_currency($order_row[price], $order_row[currency], CCAVENUE_CURRENCY); ?>">
		<input type=hidden name=Order_Id value="<?php echo $order_row[order_id];?>">
		<input type=hidden name=Redirect_Url value="<?php echo CCAVENUE_REDIRECT_URL; ?>">
		<input type=hidden name=Checksum value="<?php echo $Checksum; ?>">
		
		<input type="hidden" name="Merchant_Param" value="<?php echo $Merchant_Param; ?>"> 
		<INPUT TYPE="submit" value="<?php echo $label['pay_by_ccavenue_button'];?>">
		</form>

<!--
		
		<form action="https://www.ccavenue.com/shopzone/cc_details.jsp" name="form1" method="post" target="_parent">
		<center>PayPal accepts: Visa, Mastercard</center>
		  <input type="hidden" value="_xclick" name="cmd">
		  <input type="hidden" value="<?php echo CCAVENUE_MERCHANT_ID; ?>" name="Merchant_Id">
		  <input type="hidden" value="<?php echo CCAVENUE_REDIRECT_URL; ?>" name="Redirect_Url">
		  <input type="hidden" value="<?php echo $order_row[order_id];?>" name="Order_Id">
		  <input type="hidden" value="<?php echo convert_to_currency($order_row[price], $order_row[currency], CCAVENUE_CURRENCY); ?>" name="Amount">

		  <p align="center">
		  <input target="_parent" type="submit" value="Pay by CCAvenue" alt="CCAVENUE" src="<?php echo CCAVENUE_BUTTON_URL; ?>" border="0" name="submit" >
		  </p>
	</form>

	-->

		<?php

	}

	function config_form() {

		if ($_REQUEST['action']=='save') {

			$ccavenue_merchant_id = $_REQUEST['ccavenue_merchant_id'];
			$ccavenue_currency = $_REQUEST['ccavenue_currency'];
			$ccavenue_redirect_url = $_REQUEST['ccavenue_redirect_url'];
			$ccavenue_working_key = $_REQUEST['ccavenue_working_key'];
			

		} else {

			$ccavenue_merchant_id = CCAVENUE_MERCHANT_ID;
			$ccavenue_currency = CCAVENUE_CURRENCY;
			$ccavenue_redirect_url = CCAVENUE_REDIRECT_URL;
			$ccavenue_working_key = CCAVENUE_WORKING_KEY;
			

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
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">CCAvenue 
      Merchant ID</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="ccavenue_merchant_id" size="33" value="<?php echo $ccavenue_merchant_id; ?>"></font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">CC Avenue 
      Currency</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <select name="ccavenue_currency"  value="<?php echo $ccavenue_currency; ?>"> 
	  <?php currency_option_list ($ccavenue_currency); ?>
	  </select>(Please select a currency that is supported by CCAvenue, ie. USD)
	  </font></td>
    </tr>
	
	 
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">CC Avenue 
      Redirect URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="ccavenue_redirect_url" size="50" value="<?php echo $ccavenue_redirect_url; ?>"><br>(recommended: <b>http://<?php echo $host.$http_url."/".EMPLOYER_FOLDER."thanks.php?m=".$this->className; ?></b> )</font></td>
    </tr>
	
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">CC Avenue 
      Working Key</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="ccavenue_working_key" size="50" value="<?php echo $ccavenue_working_key; ?>"><br>(This is set in your ccavenue account)</font></td>
    </tr>
	<!--
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">CC Avenue 
      Button Image URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="paypal_button_url" size="50" value="<?php echo $paypal_button_url; ?>"><br></font></td>
    </tr>
	-->
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

	
		$sql = "REPLACE INTO config (`key`, val) VALUES ('CCAVENUE_MERCHANT_ID', '".$_REQUEST['ccavenue_merchant_id']."')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('CCAVENUE_CURRENCY', '".$_REQUEST['ccavenue_currency']."')";
		mysql_query($sql);
		
		$sql = "REPLACE INTO config (`key`, val) VALUES ('CCAVENUE_REDIRECT_URL', '".$_REQUEST['ccavenue_redirect_url']."')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('CCAVENUE_WORKING_KEY', '".$_REQUEST['ccavenue_working_key']."')";
		mysql_query($sql);

	}

	// true or false
	function is_enabled() {

		$sql = "SELECT val from config where `key`='CCAVENUE_ENABLED' ";
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

		$sql = "SELECT val from config where `key`='CCAVENUE_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);
		//$row = mysql_fetch_array($result);

		if (mysql_num_rows($result)>0) {
			return true;

		} else {
			return false;

		}

	}

	function enable() {

		$sql = "UPDATE config set val='Y' where `key`='CCAVENUE_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);


	}

	function disable() {

		$sql = "UPDATE config set val='N' where `key`='CCAVENUE_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);

	}

	function process_payment_return() {

		global $label;

		if ($_POST['Merchant_Id']!='') { 

			$sql = "SELECT * FROM orders where order_id='".$_POST['Order_Id']."'";
			$result = mysql_query ($sql) or die (mysql_error().$sql);
			$order_row = mysql_fetch_array($result);

			//$WorkingKey = "" ; //put in the 32 bit working key in the quotes provided here
			
			$Checksum = cc_verifychecksum($_POST['Merchant_Id'], $_POST['Order_Id'] , $_POST['Amount'], $_POST['AuthDesc'], $_POST['Checksum'], CCAVENUE_WORKING_KEY);
				

			if($Checksum=="true" && $_POST['AuthDesc']=="Y") {

				debit_transaction($_POST['Order_Id'], $_POST['Amount'], CCAVENUE_CURRENCY, "ccAve".$_POST['Order_Id'], $reason_code, 'CCAvenue');
				complete_order ($order_row['user_id'], $_POST['Order_Id']);

				?>

					<center>

				<?php echo $label['payment_ccave_note_y'];?> ?>

				</center>
					
					<h3><?php echo $label['payment_ccave_go_back']; ?></h3>

				<?php

				echo "<br>Thank you for shopping with us. Your credit card has been charged and your transaction is successful. You can continue and upload your pixels.";
				
				//Here you need to put in the routines for a successful 
				//transaction such as sending an email to customer,
				//setting database status, informing logistics etc etc
			}
			else if($Checksum=="true" && $_POST['AuthDesc']=="B")
			{


				pend_order ($order_row['user_id'], $_POST['Order_Id']);

				?>

				<center>

				<?php echo $label['payment_ccave_note_b'];?> ?>

				</center>
					
					

					?>
				<br>

				<?php
				
				//Here you need to put in the routines/e-mail for a  "Batch Processing" order
				//This is only if payment for this transaction has been made by an American Express Card
				//since American Express authorisation status is available only after 5-6 hours by mail from ccavenue and at the "View Pending Orders"
			}
			else if($Checksum=="true" && $_POST['AuthDesc']=="N")
			{
				echo "<br>Thank you for shopping with us. However, the transaction has been declined.";
				
				//Here you need to put in the routines for a failed
				//transaction such as sending an email to customer
				//setting database status etc etc

				?>

				<h3><?php echo $label['payment_ccave_go_back']; ?></h3>

				<?php
			}
			else
			{
				echo "<br>Security Error. Illegal access detected";
				
				//Here you need to simply ignore this and dont need
				//to perform any operation in this condition
			}

		}



	}

}



?>