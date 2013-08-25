<?php
/**
 * @version		$Id: checkorder.php 69 2010-09-12 01:31:15Z ryan $
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

$_PAYMENT_OBJECTS['check'] =  new check;

define (IPN_LOGGING, 'Y');

function ch_mail_error($msg) {

	$date = date("D, j M Y H:i:s O"); 
	
	$headers = "From: ". SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Reply-To: ".SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Return-Path: ".SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "X-Mailer: PHP" ."\r\n";
	$headers .= "Date: $date" ."\r\n"; 
	$headers .= "X-Sender-IP: $REMOTE_ADDR" ."\r\n";

	@mail(SITE_CONTACT_EMAIL, "Error message from ".SITE_NAME." Jamit check payment mod. ", $msg, $headers);

}

function ch_log_entry ($entry_line) {

	if (IPN_LOGGING == 'Y') {

		$entry_line =  "Check:$entry_line\r\n "; 
		$log_fp = fopen("logs.txt", "a"); 
		fputs($log_fp, $entry_line); 
		fclose($log_fp);

	}


}


###########################################################################
# Payment Object


class check {

	var $name="Check / Money Order";
	var $description="Mail funds by Check / Money Order.";
	var $className="check";
	

	function check() {

		if ($this->is_installed()) {

			$sql = "SELECT * FROM config where `key`='CHECK_ENABLED' OR `key`='CHECK_PAYABLE' OR `key`='CHECK_ADDRESS'  OR `key`='CHECK_CURRENCY' OR `key`='CHECK_EMAIL_CONFIRM'";
			$result = mysql_query($sql) or die (mysql_error().$sql);

			while ($row=mysql_fetch_array($result)) {
				define ($row['key'], $row['val']);
			}

		}

	}

	function get_currency() {

		return CHECK_CURRENCY;

	}


	function install() {

		$sql = "REPLACE INTO config (`key`, val) VALUES ('CHECK_ENABLED', '')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('CHECK_CURRENCY', 'USD')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('CHECK_PAYABLE', '')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('CHECK_ADDRESS', '')";
		mysql_query($sql);


		$sql = "REPLACE INTO config (`key`, val) VALUES ('CHECK_EMAIL_CONFIRM', '')";
		mysql_query($sql);




		

	}

	function uninstall() {

		$sql = "DELETE FROM config where `key`='CHECK_ENABLED'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='CHECK_CURRENCY'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='CHECK_PAYABLE'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='CHECK_ADDRESS'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='CHECK_EMAIL_CONFIRM'";
		mysql_query($sql);

	}

	function payment_button($order_id) {

		
		global $label;

		$sql = "SELECT * from orders where order_id=".$order_id;
		$result = mysql_query($sql) or die(mysql_error().$sql);
		$order_row = mysql_fetch_array($result);
	
				
			?>
			<center>
			
			<input type="button" value="<?php echo $label['payment_check_button']; ?>" onclick="window.location='<?php echo BASE_HTTP_PATH."users/thanks.php?m=".$this->className."&order_id=".$order_row['order_id']."&nhezk5=3"; ?>'">
			</center>

			

	<?php

	}

	function config_form() {

		if ($_REQUEST['action']=='save') {
		
			$check_enabled = $_REQUEST['check_enabled'];
			$check_currency = $_REQUEST['check_currency'];
			$check_payable = $_REQUEST['check_payable'];
			$check_address = $_REQUEST['check_address'];
			$check_email_confirm = $_REQUEST['check_email_confirm'];
			
		} else {
			$check_enabled = CHECK_ENABLED;
			$check_currency = CHECK_CURRENCY;
			$check_payable = CHECK_PAYABLE;
			$check_address = CHECK_ADDRESS;
			$check_email_confirm = CHECK_EMAIL_CONFIRM;
			
		}

		
		?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
		 <table border="0" cellpadding="5" cellspacing="2" style="border-style:groove" id="AutoNumber1" width="100%" bgcolor="#FFFFFF">

     <tr>
      <td colspan="2"  bgcolor="#e6f2ea">
      <font face="Verdana" size="1"><b>Check Payment Settings</b><br>(If you leave any field field blank, then it will not show up on the checkout)</font></td>
    </tr>
    <tr>
      <td width="20%" bgcolor="#e6f2ea"><font face="Verdana" size="1">Payable to Name</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="check_payable" size="29" value="<?php echo $check_payable; ?>"></font></td>
    </tr>
	 <tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Payable to Address</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <textarea name="check_address" rows="4"><?php echo $check_address; ?></textarea></font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Check Currency</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <select  name="check_currency" ><?php echo currency_option_list ($check_currency); ?></select></font></td>
    </tr>
	<!--
<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Send confirmation email</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
       <input type="radio" name="bank_email_confirm" value="YES"  <?php if ($bank_email_confirm=='YES') { echo " checked "; } ?> >Yes - Send email with bank details<br>
	  <input type="radio" name="bank_email_confirm" value="NO"  <?php if ($bank_email_confirm=='NO') { echo " checked "; } ?> >No<br></font></td>
    </tr>
	-->
     <tr>
	
      <td  bgcolor="#e6f2ea" colspan=2><font face="Verdana" size="1"><input type="submit" value="Save"></font>
	  </td>
	  </tr>
  </table>
  <input type="hidden" name="pay" value="<?php echo $_REQUEST['pay'];?>">
  <input type="hidden" name="action" value="save">
  
</form>

		<?php

	}

	function save_config() {

		
		$sql = "REPLACE INTO config (`key`, val) VALUES ('CHECK_NAME', '".$_REQUEST['check_name']."')";
		mysql_query($sql) or die (mysql_error().$sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('CHECK_PAYABLE', '".$_REQUEST['check_payable']."')";
		mysql_query($sql) or die (mysql_error().$sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('CHECK_ADDRESS', '".$_REQUEST['check_address']."')";
		mysql_query($sql) or die (mysql_error().$sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('CHECK_CURRENCY', '".$_REQUEST['check_currency']."')";
		mysql_query($sql) or die (mysql_error().$sql);
		
		$sql = "REPLACE INTO config (`key`, val) VALUES ('CHECK_EMAIL_CONFIRM', '".$_REQUEST['check_email_confirm']."')";
		mysql_query($sql) or die (mysql_error().$sql);


	}

	// true or false
	function is_enabled() {

		$sql = "SELECT val from `config` where `key`='CHECK_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);
		$row = mysql_fetch_array($result);
		if ($row['val']=='Y') {
			return true;

		} else {
			return false;

		}

	}


	function is_installed() {

		$sql = "SELECT val from config where `key`='CHECK_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);
		//$row = mysql_fetch_array($result);

		if (mysql_num_rows($result)>0) {
			return true;

		} else {
			return false;

		}

	}

	function enable() {

		$sql = "UPDATE config set val='Y' where `key`='CHECK_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);


	}

	function disable() {

		$sql = "UPDATE config set val='N' where `key`='CHECK_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);

	}

	function process_payment_return() {

		global $label;

		if (($_REQUEST['order_id']!='') && ($_REQUEST['nhezk5']!='')) {

			//print_r($_SESSION);

			if ($_SESSION['MDS_ID']=='') {

				echo "Error: You must be logged in to view this page";


			} else {

				//require ("../users/header.php");
		?>
			<div style='background-color: #ffffff; border-color:#C0C0C0; border-style:solid;padding:10px'>
		<p align="center"><center>
				<?php

				$sql = "SELECT * from orders where order_id='".$_REQUEST['order_id']."' and user_id='".$_SESSION['MDS_ID']."'";
				$result = mysql_query($sql) or die(mysql_error().$sql);
				$order_row = mysql_fetch_array($result);
			
				$check_amount = convert_to_currency($order_row['price'], $order_row['currency'], CHECK_CURRENCY);
				$check_amount = format_currency($check_amount, CHECK_CURRENCY, true);

				$label['payment_check_heading'] = str_replace ("%INVOICE_AMOUNT%", $check_amount, $label['payment_check_heading']);
				//$label['payment_check_note'] = str_replace ("%CONTACT_EMAIL%", SITE_CONTACT_EMAIL, $label['payment_check_note']);
				//$label['payment_check_note'] = str_replace ("%INVOICE_CODE%", $_REQUEST['order_id'], $label['payment_check_note']);

				if (get_default_currency()  != CHECK_CURRENCY) {	
					echo convert_to_default_currency_formatted($order_row[currency], $order_row['price'])." = ".$check_amount;
					echo "<br>";
				}?>
				
				<table width="70%"><tr><td>
				<b><?php echo $label['payment_check_heading'];?></b><br>
				<?php if ( CHECK_NAME != '') { ?>
				<b><?php echo $label['payment_check_payable'];?></b><pre><?php echo CHECK_PAYABLE; ?></pre><br>
				<?php }  ?>
				<?php if ( CHECK_ADDRESS != '') { ?>
				<b><?php echo $label['payment_check_address'];?></b><pre><?php echo CHECK_ADDRESS; ?></pre><br>
				<?php }  ?>
				<?php /*if ( CHECK_ACCOUNT_NAME != '') { ?>
				<b><?php echo $label['payment_check_currency'];?></b><pre><?php echo CHECK_CURRENCY; ?></pre><br>
				<?php } */  ?>
				
					</td></tr>
					</table>
					
					</p>
					</center>
					
					</div>
					<?php


			}


		}



	}



}
?>