<?php
/**
 * @version		$Id: authorizeNetSIM.php 69 2010-09-12 01:31:15Z ryan $
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

$_PAYMENT_OBJECTS['authorizeNet'] = new authorizeNet;

define (IPN_LOGGING, 'Y');


function authnet_mail_error($msg) {

	$date = date("D, j M Y H:i:s O"); 
	
	$headers = "From: ". SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Reply-To: ".SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Return-Path: ".SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "X-Mailer: PHP" ."\r\n";
	$headers .= "Date: $date" ."\r\n"; 
	$headers .= "X-Sender-IP: $REMOTE_ADDR" ."\r\n";

	$entry_line =  "(authnet error detected) $msg\r\n "; 
	$log_fp = @fopen("logs.txt", "a"); 
	@fputs($log_fp, $entry_line); 
	@fclose($log_fp);


	@mail(SITE_CONTACT_EMAIL, "Error message from ".SITE_NAME." Jamit authnet script. ", $msg, $headers);

}

function authnet_log_entry ($entry_line) {

	if (IPN_LOGGING == 'Y') {

		$entry_line =  "$entry_line\r\n "; 
		$log_fp = @fopen("logs.txt", "a"); 
		@fputs($log_fp, $entry_line); 
		@fclose($log_fp);

	}


}

function authnet_hmac ($key, $data) {
   // RFC 2104 HMAC implementation for php.
   // Creates an md5 HMAC.
   // Eliminates the need to install mhash to compute a HMAC
   // Hacked by Lance Rushing
   $b = 64; // byte length for md5
   if (strlen($key) > $b) {
       $key = pack("H*",md5($key));
   }
   $key  = str_pad($key, $b, chr(0x00));
   $ipad = str_pad('', $b, chr(0x36));
   $opad = str_pad('', $b, chr(0x5c));
   $k_ipad = $key ^ $ipad ;
   $k_opad = $key ^ $opad;
   return md5($k_opad  . pack("H*",md5($k_ipad . $data)));
}


// compute HMAC-MD5
// Uses PHP mhash extension. Pl sure to enable the extension
//function authnet_hmac ($key, $data) {
//	return (bin2hex (authnet_mhash(AUTHNET_MHASH_MD5, $data, $key)));
//}

// Calculate and return fingerprint
// Use when you need control on the HTML output
function authnet_CalculateFP ($loginid, $x_tran_key, $amount, $sequence, $tstamp, $currency = "") {
	return (authnet_hmac ($x_tran_key, $loginid . "^" . $sequence . "^" . $tstamp . "^" . $amount . "^" . $currency));
}

if (AUTHNET_TEST_MODE=='YES') {
	echo "Test Mode data:<p>";
	print_r($_REQUEST);
	echo "</p>";

}




###########################################################################
# Payment Object



class authorizeNet {

	var $name;
	var $description;
	var $className='authorizeNet';
	

	function authorizeNet() {

		global $label;
		$this->description = $label['payment_authnet_description'];
		$this->name = $label['payment_authnet_name'] ;
	
		if ($this->is_installed()) {

			
			$sql = "SELECT * FROM config where `key`='AUTHNET_LOGIN_ID' OR `key`='AUTHNET_CURRENCY' OR `key`='AUTHNET_TEST_MODE' OR `key`='AUTHNET_X_RELAY_URL' OR `key`='AUTHNET_X_RECEIPT_LINK_METHOD' OR `key`='AUTHNET_X_RECEIPT_LINK_URL' OR `key`='AUTHNET_X_RECEIPT_LINK_TEXT' OR `key`='AUTHNET_X_TRAN_KEY' OR `key`='AUTHNET_X_BACKGROUND_URL' OR `key`='AUTHNET_X_COLOR_LINK' OR `key`='AUTHNET_X_COLOR_TEXT' OR `key`='AUTHNET_X_LOGO_URL' OR `key`='AUTHNET_X_COLOR_BACKGROUND' OR `key`='AUTHNET_X_HEADER_HTML_PAYMENT_FORM' or `key`='AUTHNET_X_FOOTER_HTML_PAYMENT_FORM' ";
			$result = mysql_query($sql) or die (mysql_error().$sql);

			while ($row=mysql_fetch_array($result)) {

				define ($row['key'], $row['val']);

			}

			

		}


	}

	function get_currency() {

		return AUTHNET_CURRENCY;

	}


	function install() {

		echo "Install Authorize.net ..<br>";

		$host = $_SERVER['SERVER_NAME']; // hostname
		$http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
		$http_url = explode ("/", $http_url);
		array_pop($http_url); // get rid of filename
		array_pop($http_url); // get rid of /admin
		$http_url = implode ("/", $http_url);

	
		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_ENABLED', 'N')";
		mysql_query($sql);
		
		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_CURRENCY', 'USD')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_TEST_MODE', 'NO')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_LOGIN_ID', '')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_X_RELAY_URL', '')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_X_RECEIPT_LINK_URL', 'http://$host".$http_url."/users/index.php"."')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_X_RECEIPT_LINK_METHOD', 'POST"."')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_X_RECEIPT_LINK_TEXT', '".SITE_NAME."')";
		mysql_query($sql);


		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_X_TRAN_KEY', '')";
		mysql_query($sql);


		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_X_LOGO_URL', '".SITE_LOGO_URL."')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_X_BACKGROUND_URL', '')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_X_COLOR_BACKGROUND', '#FFFFFF')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_X_COLOR_LINK', '#0000FF')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_X_COLOR_TEXT', '#000000')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_X_HEADER_HTML_PAYMENT_FORM', '')";
		mysql_query($sql);

		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_X_FOOTER_HTML_PAYMENT_FORM', '')";
		mysql_query($sql);

		

		
		
		
	}

	function uninstall() {

		echo "Uninstall Authorize.net ..<br>";

	
		$sql = "DELETE FROM config where `key`='AUTHNET_ENABLED'";
		mysql_query($sql);
		
		$sql = "DELETE FROM config where `key`='AUTHNET_LOGIN_ID'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='AUTHNET_CURRENCY'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='AUTHNET_TEST_MODE'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='AUTHNET_X_RELAY_URL'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='AUTHNET_X_RECEIPT_LINK_METHOD'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='AUTHNET_X_RECEIPT_LINK_URL'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='AUTHNET_X_RECEIPT_LINK_TEXT'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='AUTHNET_X_TRAN_KEY'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='AUTHNET_X_BACKGROUND_URL'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='AUTHNET_X_LOGO_URL'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='AUTHNET_X_COLOR_BACKGROUND'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='AUTHNET_X_COLOR_LINK'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='AUTHNET_X_COLOR_TEXT'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='AUTHNET_X_HEADER_HTML_PAYMENT_FORM'";
		mysql_query($sql);

		$sql = "DELETE FROM config where `key`='AUTHNET_X_FOOTER_HTML_PAYMENT_FORM'";
		mysql_query($sql);

		

		

		
		
	}

	function payment_button($order_id) {

		global $label;

		$sql = "SELECT * from orders where order_id='".$order_id."'";
		$result = mysql_query($sql) or die(mysql_error().$sql);
		$order_row = mysql_fetch_array($result);

?>
<center>
<?php
		

		if (AUTHNET_TEST_MODE == 'YES') {
		?>
		
			<FORM action="https://test.authorize.net/gateway/transact.dll" method="POST">
		
		<?php } else { ?>

			<FORM action="https://secure.authorize.net/gateway/transact.dll" method="POST"> 
		
		<?php

		}

		
		$loginid = AUTHNET_LOGIN_ID;
		$x_tran_key = AUTHNET_X_TRAN_KEY;
		$x_Amount = $order_row['price'];

		$amount = convert_to_currency($x_Amount, $order_row['currency'], AUTHNET_CURRENCY) ;

		// Seed random number for security and better randomness.

		srand(time());
		$sequence = rand(1, 1000);
		

		$tstamp = time ();

		$fingerprint = authnet_hmac ($x_tran_key, $loginid . "^" . $sequence . "^" . $tstamp . "^" . $amount . "^" . AUTHNET_CURRENCY);

		echo ('<input type="hidden" name="x_fp_sequence" value="' . $sequence . '">' );
		echo ('<input type="hidden" name="x_fp_timestamp" value="' . $tstamp . '">' );
		echo ('<input type="hidden" name="x_fp_hash" value="' . $fingerprint . '">' );

		// Insert rest of the form elements similiar to the legacy weblink integration
		//echo ("<input type=\"hidden\" name=\"x_description\" value=\"" . $x_Description . "\">\n" );
		echo ("<input type=\"hidden\" name=\"x_login\" value=\"" . $loginid . "\">\n");
		echo ("<input type=\"hidden\" name=\"x_amount\" value=\"" . $amount . "\">\n");

		// *** IF YOU ARE PASSING CURRENCY CODE uncomment the line below *****
		echo ("<input type=\"hidden\" name=\"x_currency_code\" value=\"" . AUTHNET_CURRENCY . "\">\n");

		?>

		<INPUT type="hidden" name="x_background_url" value="<?php echo AUTHNET_X_BACKGROUND_URL;?>">
		<INPUT type="hidden" name="x_logo_url" value="<?php echo AUTHNET_X_LOGO_URL;?>">
		<INPUT type="hidden" name="x_color_background" value="<?php echo AUTHNET_X_COLOR_BACKGROUND;?>">
		<INPUT type="hidden" name="x_color_link" value="<?php echo AUTHNET_X_COLOR_LINK;?>">
		<INPUT type="hidden" name="x_color_text" value="<?php echo AUTHNET_X_COLOR_TEXT;?>">

		<INPUT type="hidden" name="x_receipt_link_method" value="<?php echo AUTHNET_X_RECEIPT_LINK_METHOD;?>">
		<INPUT type="hidden" name="x_receipt_link_url" value="<?php echo AUTHNET_X_RECEIPT_LINK_URL;?>">
		<INPUT type="hidden" name="x_receipt_link_text" value="<?php echo AUTHNET_X_RECEIPT_LINK_TEXT;?>">
		<INPUT type="hidden" name="x_header_html_payment_form" value="<?php echo AUTHNET_X_HEADER_HTML_PAYMENT_FORM;?>">
		<INPUT type="hidden" name="x_footer_html_payment_form" value="<?php echo AUTHNET_X_FOOTER_HTML_PAYMENT_FORM;?>">
		
	
		<INPUT type="hidden" name="x_cust_id" value="<?php echo $order_row['user_id'];?>">
		<INPUT type="hidden" name="x_relay_response" value="TRUE">
		<INPUT type="hidden" name="x_relay_url" value="<?php echo AUTHNET_X_RELAY_URL; ?>">
		<INPUT type="hidden" name="x_invoice_num" value="<?php echo $order_row['order_id'];?>">
		<INPUT type="hidden" name="x_description" value="<?php echo SITE_NAME;?>">
		<INPUT type="hidden" name="x_cust_id" value="<?php echo $order_row['user_id'];?>">
		<INPUT type="hidden" name="x_show_form" value="PAYMENT_FORM">
		<?php if (AUTHNET_TEST_MODE == 'YES') { ?>
			<INPUT type="hidden" name="x_test_request" value="TRUE">
		<?php } else { ?>
			<INPUT type="hidden" name="x_test_request" value="FALSE">
		<?php } ?>
		<INPUT type="submit" value="<?php echo $label['pay_by_authnet_button']; ?>">
		</FORM>
</center>


		<?php

	}

	function config_form() {

		echo "Note: The Authorize.net module is currently experimentail in this version<br>";

		if ($_REQUEST['action']=='save') {

			$authnet_login_id = $_REQUEST['authnet_login_id'];
			$authnet_currency = $_REQUEST['authnet_currency'];
			$authnet_test_mode = $_REQUEST['authnet_test_mode'];
			$authnet_x_relay_url = $_REQUEST['authnet_x_relay_url'];
			$authnet_x_receipt_link_method = $_REQUEST['authnet_x_receipt_link_method'];
			$authnet_x_receipt_link_url = $_REQUEST['authnet_x_receipt_link_url'];
			$authnet_x_receipt_link_text = $_REQUEST['authnet_x_receipt_link_text'];
			$authnet_x_tran_key = $_REQUEST['authnet_x_tran_key'];
			$authnet_x_background_url = $_REQUEST['authnet_x_background_url'];
			$authnet_x_logo_url = $_REQUEST['authnet_x_logo_url'];
			$authnet_x_color_background = $_REQUEST['authnet_x_color_background'];
			$authnet_x_color_link = $_REQUEST['authnet_x_color_link'];
			$authnet_x_color_text = $_REQUEST['authnet_x_color_text'];
			$authnet_x_header_html_payment_form = $_REQUEST['authnet_x_header_html_payment_form'];
			$authnet_x_footer_html_payment_form = $_REQUEST['authnet_x_footer_html_payment_form'];

		} else {

			$authnet_login_id = AUTHNET_LOGIN_ID;
			$authnet_currency = AUTHNET_CURRENCY;
			$authnet_test_mode = AUTHNET_TEST_MODE;
			$authnet_x_relay_url = AUTHNET_X_RELAY_URL;
			$authnet_x_receipt_link_method = AUTHNET_X_RECEIPT_LINK_METHOD;
			$authnet_x_receipt_link_url = AUTHNET_X_RECEIPT_LINK_URL;
			$authnet_x_receipt_link_text = AUTHNET_X_RECEIPT_LINK_TEXT;
			$authnet_x_tran_key = AUTHNET_X_TRAN_KEY;
			$authnet_x_background_url = AUTHNET_X_BACKGROUND_URL;
			$authnet_x_logo_url = AUTHNET_X_LOGO_URL;
			$authnet_x_color_background = AUTHNET_X_COLOR_BACKGROUND;
			$authnet_x_color_link = AUTHNET_X_COLOR_LINK;
			$authnet_x_color_text = AUTHNET_X_COLOR_TEXT;
			$authnet_x_header_html_payment_form = AUTHNET_X_HEADER_HTML_PAYMENT_FORM;
			$authnet_x_footer_html_payment_form = AUTHNET_X_FOOTER_HTML_PAYMENT_FORM;
			$authnet_x_header_html_payment_form = AUTHNET_X_HEADER_HTML_PAYMENT_FORM;
			$authnet_x_footer_html_payment_form = AUTHNET_X_FOOTER_HTML_PAYMENT_FORM;
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
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Authorize.Net 
      Login ID</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="authnet_login_id" size="33" value="<?php echo $authnet_login_id; ?>"></font></td>
    </tr>



	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Test Mode (Y/N)</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
       <input type="radio" name="authnet_test_mode" value="YES"  <?php if ($authnet_test_mode=='YES') { echo " checked "; } ?> >Yes <br>
	  <input type="radio" name="authnet_test_mode" value="NO"  <?php if ($authnet_test_mode=='NO') { echo " checked "; } ?> >No<br></font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Authorize.Net 
      Currency</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <select name="authnet_currency"  value="<?php echo $authnet_currency; ?>"> 
	  <?php currency_option_list ($authnet_currency); ?>
	  </select>(Please select a currency that is supported by Authorize.Net. If the currency is not on this list, you may add it under the Configuration section)
	  </font></td>
    </tr>
	
	 
		<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Authorize.Net 
      Relay Response URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="authnet_x_relay_url" size="50" value="<?php echo $authnet_x_relay_url; ?>"><br>(Recommended: <b>http://<?php echo $host.$http_url."/users/thanks.php?m=".$this->className; ?> </b> )</font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Authorize.Net 
      Receipt Link Method</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <select type="text" name="authnet_x_receipt_link_method"  value="<?php echo $authnet_x_receipt_link_method; ?>">
	  <option value="POST">POST (recommended)</option>
	  <option value="GET">GET</option>
	  <option value="LINK">LINK (hyperlink)</option>

	  </select>
	  (What way to return to the MDS script.)</font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Authorize.Net 
      Receipt link URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="authnet_x_receipt_link_url" size="50" value="<?php echo $authnet_x_receipt_link_url; ?>"><br>(eg. http://<?php echo $host.$http_url."/users/index.php"; ?> - where customers return back to the MDS)</font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Authorize.Net 
      Receipt link Text</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="authnet_x_receipt_link_text" size="50" value="<?php echo $authnet_x_receipt_link_text; ?>"><br>(Anchor text for the Receipt link URL - where customers return back to the MDS)</font></td>
    </tr>



	
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Authorize.net Transaction Key</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="authnet_x_tran_key" size="50" value="<?php echo $authnet_x_tran_key; ?>"><br>(Note: 1. Log in to the Merchant Interface, 2. Select 'Settings' from the Main Menu, 3. Click on the Obtain Transaction Key in the Security section, 4. Type in the answer to your secret question, 5. Click Submit, 6. The transaction key is returned by the Merchant Interface.)</font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Logo URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="authnet_x_logo_url" size="50" value="<?php echo $authnet_x_logo_url; ?>"><br>(Logo on the Payment form & Receipt Page, eg http://www.example.com/test.gif)</font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Background Image URL</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="authnet_x_background_url" size="50" value="<?php echo $authnet_x_background_url; ?>"><br>(Background image on the Payment form & Receipt Page)</font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Background color</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="authnet_x_color_background" size="50" value="<?php echo $authnet_x_color_background; ?>"><br>(Background Color of the Payment form & Receipt Page, any HTML color or hex code, eg #FFFFFF)</font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Link color</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="authnet_x_color_link" size="50" value="<?php echo $authnet_x_color_link; ?>"><br>(Logo on the Payment form & Receipt Page)</font></td>
    </tr>

	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Payment form: Header HTML</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <textarea name="authnet_x_header_html_payment_form" ><?php echo $authnet_x_header_html_payment_form; ?></textarea><br>(The text submitted in this field will be dispalyed as the header on the Payment Form)</font></td>
    </tr>
	<tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Payment form: Footer HTML</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <textarea name="authnet_x_footer_html_payment_form" ><?php echo $authnet_x_footer_html_payment_form; ?></textarea><br>(The text submitted in this field will be dispalyed as the footer on the Payment Form)</font></td>
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

	
		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_LOGIN_ID', '".$_REQUEST['authnet_login_id']."')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_CURRENCY', '".$_REQUEST['authnet_currency']."')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_TEST_MODE', '".$_REQUEST['authnet_test_mode']."')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_X_RELAY_URL', '".$_REQUEST['authnet_x_relay_url']."')";
		mysql_query($sql);
		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_X_RECEIPT_LINK_METHOD', '".$_REQUEST['authnet_x_receipt_link_method']."')";
		mysql_query($sql);	
		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_X_RECEIPT_LINK_URL', '".$_REQUEST['authnet_x_receipt_link_url']."')";
		mysql_query($sql);	
		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_X_RECEIPT_LINK_TEXT', '".$_REQUEST['authnet_x_receipt_link_text']."')";
		mysql_query($sql);	
		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_X_TRAN_KEY', '".$_REQUEST['authnet_x_tran_key']."')";
		mysql_query($sql);	
		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_X_BACKGROUND_URL', '".$_REQUEST['authnet_x_background_url']."')";
		mysql_query($sql);	
		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_X_COLOR_BACKGROUND', '".$_REQUEST['authnet_x_color_background']."')";
		mysql_query($sql);	
		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_X_COLOR_LINK', '".$_REQUEST['authnet_x_color_link']."')";
		mysql_query($sql);	
		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_X_COLOR_TEXT', '".$_REQUEST['authnet_x_color_text']."')";
		mysql_query($sql);	
		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_X_LOGO_URL', '".$_REQUEST['authnet_x_logo_url']."')";
		mysql_query($sql);	
		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_X_HEADER_HTML_PAYMENT_FORM', '".$_REQUEST['authnet_x_header_html_payment_form']."')";
		mysql_query($sql);	
		$sql = "REPLACE INTO config (`key`, val) VALUES ('AUTHNET_X_FOOTER_HTML_PAYMENT_FORM', '".$_REQUEST['authnet_x_footer_html_payment_form']."')";
		mysql_query($sql);	
		

		
		

	}

	// true or false
	function is_enabled() {

		$sql = "SELECT val from config where `key`='AUTHNET_ENABLED' ";
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

		$sql = "SELECT val from config where `key`='AUTHNET_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);
		//$row = mysql_fetch_array($result);

		if (mysql_num_rows($result)>0) {
			return true;

		} else {
			return false;

		}

	}

	function enable() {

		$sql = "UPDATE config set val='Y' where `key`='AUTHNET_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);


	}

	function disable() {

		$sql = "UPDATE config set val='N' where `key`='AUTHNET_ENABLED' ";
		$result = mysql_query($sql) or die(mysql_error().$sql);

	}

	function process_payment_return() {

		global $label;

		if ($_POST['x_response_code']!='') { 

			//$_POST['x_md5_hash'];

			$working_sig = strtoupper (md5($merchant_id.$transaction_id.$secret.$mb_amount.$mb_currency.$status));

			$sql = "SELECT * FROM orders where order_id='".$_POST['x_invoice_num']."'";
			$result = mysql_query ($sql) or die (mysql_error().$sql);
			$order_row = mysql_fetch_array($result);

			$myhash = strtoupper (md5 ( AUTHNET_X_TRAN_KEY.AUTHNET_LOGIN_ID.$_POST['x_trans_id'].$_POST['x_amount'] ));



			if ($_POST['x_md5_hash']==$myhash) {

				switch ($_POST['x_response_code']) {

					case "1": // approved
						debit_transaction($_POST['x_invoice_num'], $_POST['x_amount'], AUTHNET_CURRENCY, $_POST['x_trans_id'], $_POST['x_response_reason_text'], 'authorize.net');
						complete_order ($order_row['user_id'], $_POST['x_invoice_num']);
						break;
					case "2": // declined
						
						break;
					case "3": // Error
						break;
					

				}


			} else {

				authnet_log_entry( "Authorize.net: Invalid signiture");


			}




		}


	}

}



?>