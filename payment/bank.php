<?php
/**
 * @version		$Id: bank.php 69 2010-09-12 01:31:15Z ryan $
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

$_PAYMENT_OBJECTS['bank'] =  new bank;
define (IPN_LOGGING, 'Y');

function b_mail_error($msg) {

	$date = date("D, j M Y H:i:s O"); 
	
	$headers = "From: ". SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Reply-To: ".SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Return-Path: ".SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "X-Mailer: PHP" ."\r\n";
	$headers .= "Date: $date" ."\r\n"; 
	$headers .= "X-Sender-IP: $REMOTE_ADDR" ."\r\n";

	@mail(SITE_CONTACT_EMAIL, "Error message from ".SITE_NAME." Jamit bank payment mod. ", $msg, $headers);

}

function b_log_entry ($entry_line) {

	if (IPN_LOGGING == 'Y') {

		$entry_line =  "BANK:$entry_line\r\n "; 
		$log_fp = fopen("logs.txt", "a"); 
		fputs($log_fp, $entry_line); 
		fclose($log_fp);

	}


}



###########################################################################
# Payment Object


class bank {

	var $name="Bank";
	var $description="Wire Transfer - Funds transfer to a bank account .";
	var $className="bank";
	

	function bank() {

		if ($this->is_installed()) {

			$sql = "SELECT * FROM config where `key`='BANK_ENABLED' OR `key`='BANK_NAME' OR `key`='BANK_ADDRESS' OR `key`='BANK_ACCOUNT_NAME' or `key`='BANK_BRANCH_NUMBER' OR `key`='BANK_ACCOUNT_NUMBER' OR `key`='BANK_SWIFT' OR `key`='BANK_ENABLED' OR `key`='BANK_CURRENCY' OR `key`='BANK_EMAIL_CONFIRM'";
			$result = mysql_query($sql) or die (mysql_error().$sql);

			while ($row=mysql_fetch_array($result)) {
				define ($row['key'], $row['val']);
			}

		}

	}

	function get_currency() {

		return BANK_CURRENCY;

	}


	function install() {

		$sql = "REPLACE INTO config (`key`, val) VALUES ('BANK_ENABLED', '')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('BANK_CURRENCY', 'USD')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('BANK_NAME', '')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('BANK_ADDRESS', '')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('BANK_ACCOUNT_NAME', '')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('BANK_ACCOUNT_NUMBER', '')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('BANK_BRANCH_NUMBER', '')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('BANK_SWIFT', '')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('BANK_EMAIL_CONFIRM', '')";
		mysql_query($sql);




		

	}

	function uninstall() {

		$sql = "DELETE FROM config where `key`='BANK_ENABLED'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='BANK_NAME'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='BANK_ADDRESS'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='BANK_ACCOUNT_NAME'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='BANK_ACCOUNT_NUMBER'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='BANK_SWIFT'";
		mysql_query($sql);


		$sql = "DELETE FROM config where `key`='BANK_CURRENCY'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='BANK_EMAIL_CONFIRM'";
		mysql_query($sql);

	
		



	}

	function payment_button($order_id) {

		global $label;

		$sql = "SELECT * from orders where order_id='".$order_id."'";
		$result = mysql_query($sql) or die(mysql_error().$sql);
		$order_row = mysql_fetch_array($result);
	
				
			?>
			<center>
			
			<input type="button" value="<?php echo $label['payment_bank_button']; ?>" onclick="window.location='<?php echo BASE_HTTP_PATH."users/thanks.php?m=".$this->className."&order_id=".$order_row['order_id']."&nhezk5=3"; ?>'">
			</center>

			

	<?php

	}

	function config_form() {

		if ($_REQUEST['action']=='save') {
		
			$bank_name = $_REQUEST['bank_name'];
			$bank_address = $_REQUEST['bank_address'];
			$bank_account_name = $_REQUEST['bank_account_name'];
			$bank_account_number = $_REQUEST['bank_account_number'];
			$bank_branch_number = $_REQUEST['bank_branch_number'];
			$bank_swift = $_REQUEST['bank_swift'];
			$bank_currency = $_REQUEST['bank_currency'];
			$bank_email_confirm = $_REQUEST['bank_email_confirm'];
		} else {
			$bank_name = BANK_NAME;
			$bank_address = BANK_ADDRESS;
			$bank_account_name = BANK_ACCOUNT_NAME;
			$bank_account_number = BANK_ACCOUNT_NUMBER;
			$bank_branch_number = BANK_BRANCH_NUMBER;
			$bank_swift = BANK_SWIFT;
			$bank_currency = BANK_CURRENCY;
			$bank_email_confirm = BANK_EMAIL_CONFIRM;
		
		}

		
		?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
		 <table border="0" cellpadding="5" cellspacing="2" style="border-style:groove" id="AutoNumber1" width="100%" bgcolor="#FFFFFF">

		 
     <tr>
      <td colspan="2"  bgcolor="#e6f2ea">
      <font face="Verdana" size="1"><b>Bank Payment Settings</b><br>(If you leave any field field blank, then it will not show up on the checkout)</font></td>
    </tr>
    <tr>
      <td width="20%" bgcolor="#e6f2ea"><font face="Verdana" size="1">Bank Name</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="bank_name" size="29" value="<?php echo $bank_name; ?>"></font></td>
    </tr>
	 <tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Bank Address</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="bank_address" size="29" value="<?php echo $bank_address; ?>"></font></td>
    </tr>
	 <tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Bank Account Name</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="bank_account_name" size="29" value="<?php echo $bank_account_name; ?>"></font></td>
    </tr>
	
    <tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Bank Account Number</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="bank_account_number" size="29" value="<?php echo $bank_account_number; ?>"></font></td>
    </tr>
    <tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Bank Branch Number</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="bank_branch_number" size="29" value="<?php echo $bank_branch_number; ?>"></font></td>
    </tr>
	 <tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">SWIFT Code</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="bank_swift" size="29" value="<?php echo $bank_swift; ?>"></font></td>
    </tr>
	
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Bank Account Currency</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <select  name="bank_currency" ><?php echo currency_option_list ($bank_currency); ?></select></font></td>
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

		
		$sql = "REPLACE INTO config (`key`, val) VALUES ('BANK_NAME', '".$_REQUEST['bank_name']."')";
		mysql_query($sql) or die (mysql_error().$sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('BANK_ADDRESS', '".$_REQUEST['bank_address']."')";
		mysql_query($sql) or die (mysql_error().$sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('BANK_ACCOUNT_NAME', '".$_REQUEST['bank_account_name']."')";
		mysql_query($sql) or die (mysql_error().$sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('BANK_BRANCH_NUMBER', '".$_REQUEST['bank_branch_number']."')";
		mysql_query($sql) or die (mysql_error().$sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('BANK_ACCOUNT_NUMBER', '".$_REQUEST['bank_account_number']."')";
		mysql_query($sql) or die (mysql_error().$sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('BANK_CURRENCY', '".$_REQUEST['bank_currency']."')";
		mysql_query($sql) or die (mysql_error().$sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('BANK_SWIFT', '".$_REQUEST['bank_swift']."')";
		mysql_query($sql) or die (mysql_error().$sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('BANK_EMAIL_CONFIRM', '".$_REQUEST['bank_email_confirm']."')";
		mysql_query($sql) or die (mysql_error().$sql);


	}

	// true or false
	function is_enabled() {

		$sql = "SELECT val from `config` where `key`='BANK_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);
		$row = mysql_fetch_array($result);
		if ($row['val']=='Y') {
			return true;

		} else {
			return false;

		}

	}


	function is_installed() {

		$sql = "SELECT val from config where `key`='BANK_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);
		//$row = mysql_fetch_array($result);

		if (mysql_num_rows($result)>0) {
			return true;

		} else {
			return false;

		}

	}

	function enable() {

		$sql = "UPDATE config set val='Y' where `key`='BANK_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);


	}

	function disable() {

		$sql = "UPDATE config set val='N' where `key`='BANK_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);

	}

	function process_payment_return() {

		global $label;

		if (($_REQUEST['order_id']!='') && ($_REQUEST['nhezk5']!='')) {

			//session_start();

			//print_r($_SESSION);

			if ($_SESSION['MDS_ID']=='') {

				echo "Error: You must be logged in to view this page";

			} else {

				?>

			<div style='background-color: #ffffff; border-color:#C0C0C0; border-style:solid;padding:10px'>
		<p align="center"><center>
				<?php

				$sql = "SELECT * from orders where order_id='".$_REQUEST['order_id']."' and user_id='".$_SESSION['MDS_ID']."'";
				$result = mysql_query($sql) or die(mysql_error().$sql);
				$order_row = mysql_fetch_array($result);

					
				$bank_amount = convert_to_currency($order_row['price'], $order_row['currency'], BANK_CURRENCY);
				$bank_amount = format_currency($bank_amount, BANK_CURRENCY, true);

				$label['payment_bank_heading'] = str_replace ("%INVOICE_AMOUNT%", $bank_amount, $label['payment_bank_heading']);
				$label['payment_bank_note'] = str_replace ("%CONTACT_EMAIL%", SITE_CONTACT_EMAIL, $label['payment_bank_note']);
				$label['payment_bank_note'] = str_replace ("%INVOICE_CODE%", $_REQUEST['order_id'], $label['payment_bank_note']);

				if (get_default_currency()  != BANK_CURRENCY) {	
					echo convert_to_default_currency_formatted($order_row[currency], $order_row['price'])." = ".$bank_amount;
					echo "<br>";
				}?>
				
				<table width="70%"><tr><td>
				<b><?php echo $label['payment_bank_heading'];?></b><br>
				<?php if ( BANK_NAME != '') { ?>
				<b><?php echo $label['payment_bank_name'];?></b> <?php echo BANK_NAME; ?><br>
				<?php }  ?>
				<?php if ( BANK_ADDRESS != '') { ?>
				<b><?php echo $label['payment_bank_addr'];?></b> <?php echo BANK_ADDRESS; ?><br>
				<?php }  ?>
				<?php if ( BANK_ACCOUNT_NAME != '') { ?>
				<b><?php echo $label['payment_bank_ac_name'];?></b> <?php echo BANK_ACCOUNT_NAME; ?><br>
				<?php }  ?>
				<?php if ( BANK_ACCOUNT_NUMBER != '') { ?>
				<b><?php echo $label['payment_bank_ac_number'];?></b> <?php echo BANK_ACCOUNT_NUMBER; ?><br>
					<?php }  ?>
					<?php if ( BANK_BRANCH_NUMBER != '') { ?>
					<b><?php echo $label['payment_bank_branch_number'];?></b> <?php echo BANK_BRANCH_NUMBER; ?><br>
					<?php }  ?>
					<?php if ( BANK_SWIFT != '') { ?>

					<b><?php echo $label['payment_bank_swift']; ?></b> <?php echo BANK_SWIFT; ?><br>

					<?php }  ?>
					<?php echo $label['payment_bank_note'];?>
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