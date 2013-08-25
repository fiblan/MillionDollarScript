<?php
/**
 * @version		$Id: edit_config.php 140 2011-04-19 05:08:19Z ryan $
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
error_reporting(E_ALL & ~E_NOTICE);
require ('admin_common.php');

error_reporting(E_ALL & ~E_NOTICE);

// filter vars
$footer = $purifier->purify( $_REQUEST['footer'] );
$header = $purifier->purify( $_REQUEST['header'] );
foreach ($_REQUEST as $key=>$val) {
	$_REQUEST[$key] =  $val;
}

if ($_REQUEST['save'] != '') {
   if (get_magic_quotes_gpc()==0) { // magic is OFF?
	   // need to add slashes here..
	   $_REQUEST['site_name'] = addslashes($_REQUEST['site_name']);
	   $_REQUEST['site_heading'] = addslashes($_REQUEST['site_heading']);
	   $_REQUEST['site_description'] = addslashes($_REQUEST['site_description']);
	   $_REQUEST['site_keywords'] = addslashes($_REQUEST['site_keywords']);

   } else {
	   // Magic Quotes is on, need to get rid of slashes here
	   $header = stripslashes($header);
	   $footer = stripslashes($footer);

   }
echo "updating config....";

define('VERSION_INFO', $_REQUEST['version_info']);

define('BASE_HTTP_PATH', $_REQUEST['base_http_path']);
define('SERVER_PATH_TO_ADMIN', $_REQUEST['server_path_to_admin']);
define('UPLOAD_PATH', $_REQUEST['upload_path']);
define('UPLOAD_HTTP_PATH', $_REQUEST['upload_http_path']);
define('SITE_CONTACT_EMAIL', $_REQUEST['site_contact_email']);
define('SITE_LOGO_URL', $_REQUEST['site_logo_url']);
define('SITE_NAME', $_REQUEST['site_name']);
define('SITE_SLOGAN', $_REQUEST['site_slogan']);
define('MDS_RESIZE', $_REQUEST['mds_resize']);

define('MYSQL_HOST', $_REQUEST['mysql_host']);
define('MYSQL_USER', $_REQUEST['mysql_user']);
define('MYSQL_PASS', $_REQUEST['mysql_pass']);
define('MYSQL_DB', $_REQUEST['mysql_db']);

define('ADMIN_PASSWORD', $_REQUEST['admin_password']);

define('DATE_FORMAT', $_REQUEST['date_format']);
define('GMT_DIF', $_REQUEST['gmt_dif']);
define('DATE_INPUT_SEQ', $_REQUEST['date_input_seq']);

define('OUTPUT_JPEG', $_REQUEST['output_jpeg']);
define('JPEG_QUALITY', $_REQUEST['jpeg_quality']);
define('INTERLACE_SWITCH', $_REQUEST['interlace_switch']);
define('USE_LOCK_TABLES', $_REQUEST['use_lock_tables']);
define('BANNER_DIR', $_REQUEST['banner_dir']);
define('DISPLAY_PIXEL_BACKGROUND', $_REQUEST['display_pixel_background']);

define('EMAIL_USER_ORDER_CONFIRMED', $_REQUEST['email_user_order_confirmed']);
define('EMAIL_ADMIN_ORDER_CONFIRMED', $_REQUEST['email_admin_order_confirmed']);
define('EMAIL_USER_ORDER_COMPLETED', $_REQUEST['email_user_order_completed']);
define('EMAIL_ADMIN_ORDER_COMPLETED', $_REQUEST['email_admin_order_completed']);
define('EMAIL_USER_ORDER_PENDED', $_REQUEST['email_user_order_pended']);
define('EMAIL_ADMIN_ORDER_PENDED', $_REQUEST['email_admin_order_pended']);
define('EMAIL_USER_ORDER_EXPIRED', $_REQUEST['email_user_order_expired']);
define('EMAIL_ADMIN_ORDER_EXPIRED', $_REQUEST['email_admin_order_expired']);
define('EM_NEEDS_ACTIVATION', $_REQUEST['em_needs_activation']);
define('EMAIL_USER_EXPIRE_WARNING', $_REQUEST['email_user_expire_warning']);
define('EMAIL_ADMIN_ACTIVATION', $_REQUEST['email_admin_activation']);
define('EMAIL_ADMIN_PUBLISH_NOTIFY', $_REQUEST['email_admin_publish_notify']);
define('EMAILS_DAYS_KEEP', $_REQUEST['emails_days_keep']);

define('DAYS_RENEW', $_REQUEST['days_renew']);
define('DAYS_CONFIRMED', $_REQUEST['days_confirmed']);
define('HOURS_UNCONFIRMED', $_REQUEST['hours_unconfirmed']);
define('DAYS_CANCEL', $_REQUEST['days_cancel']);
define('ENABLE_MOUSEOVER', $_REQUEST['enable_mouseover']);
define('ENABLE_CLOAKING', $_REQUEST['enable_cloaking']);
define('VALIDATE_LINK', $_REQUEST['validate_link']);
define('ADVANCED_CLICK_COUNT', $_REQUEST['advanced_click_count']);
define('USE_SMTP', $_REQUEST['use_smtp']);
define('EMAIL_HOSTNAME', $_REQUEST['email_hostname']);
define('EMAIL_SMTP_SERVER', $_REQUEST['email_smtp_server']);
define('EMAIL_POP_SERVER', $_REQUEST['email_pop_server']);
define('EMAIL_SMTP_USER', $_REQUEST['email_smtp_user']);
define('EMAIL_SMTP_PASS', $_REQUEST['email_smtp_pass']);
define('EMAIL_SMTP_AUTH_HOST', $_REQUEST['email_smtp_auth_host']);
define('POP3_PORT', $_REQUEST['pop3_port']);
define('EMAIL_POP_BEFORE_SMTP', $_REQUEST['email_pop_before_smtp']);

define('EMAILS_PER_BATCH', $_REQUEST['emails_per_batch']);
define('EMAILS_MAX_RETRY', $_REQUEST['emails_max_retry']);
define('EMAILS_ERROR_WAIT', $_REQUEST['emails_error_wait']);

define('USE_AJAX', $_REQUEST['use_ajax']);
define('ANIMATION_SPEED', $_REQUEST['animation_speed']);
define('MAX_BLOCKS', $_REQUEST['max_blocks']);

define('MEMORY_LIMIT', $_REQUEST['memory_limit']);

define('REDIRECT_SWITCH', $_REQUEST['redirect_switch']);
define('REDIRECT_URL', $_REQUEST['redirect_url']);

define('ADVANCED_CLICK_COUNT', $_REQUEST['advanced_click_count']);

define('ADVANCED_CLICK_COUNT', $_REQUEST['advanced_click_count']);
define('TRANSITION_EFFECT', $_REQUEST['transition_effect']);
define('ENABLE_TRANSITIONS', $_REQUEST['enable_transitions']);
define('TRANSITION_DURATION', $_REQUEST['transition_duration']);
define('HIDE_TIMEOUT', $_REQUEST['hide_timeout']);
define('MDS_AGRESSIVE_CACHE', $_REQUEST['mds_agressive_cache']);

   $config_str = "<?php
error_reporting(E_ALL & ~E_NOTICE);

#########################################################################
# CONFIGURATION
# Note: Please do not edit this file. Edit the config from the admin section.
#########################################################################

define('VERSION_INFO', 'v 2.1 (Oct 2010)');

define('BASE_HTTP_PATH', '".BASE_HTTP_PATH."'); 
define('SERVER_PATH_TO_ADMIN', '".SERVER_PATH_TO_ADMIN."');
define('UPLOAD_PATH', '".UPLOAD_PATH."');
define('UPLOAD_HTTP_PATH', '".UPLOAD_HTTP_PATH."');
define('MYSQL_HOST', '".MYSQL_HOST."'); # mysql database host
define('MYSQL_USER', '".MYSQL_USER."'); #mysql user name
define('MYSQL_PASS', '".MYSQL_PASS."'); # mysql password
define('MYSQL_DB', '".MYSQL_DB."'); # mysql database name

define('MDS_RESIZE', '".MDS_RESIZE."');

# SITE_CONTACT_EMAIL

define('SITE_CONTACT_EMAIL', stripslashes('".SITE_CONTACT_EMAIL."'));

# SITE_LOGO_URL

define('SITE_LOGO_URL', stripslashes('".SITE_LOGO_URL."'));

# SITE_NAME
# change to your website name
define('SITE_NAME', stripslashes('".SITE_NAME."')); 

# SITE_SLOGAN
# change to your website slogan
define('SITE_SLOGAN', stripslashes('".SITE_SLOGAN."')); 

# ADMIN_PASSWORD

define('ADMIN_PASSWORD',  '".ADMIN_PASSWORD."');

# date formats
define('DATE_FORMAT', '".DATE_FORMAT."');
define('GMT_DIF', '".GMT_DIF."');
define('DATE_INPUT_SEQ', '".DATE_INPUT_SEQ."');

# Output the image in JPEG? Y or N. 

define ('OUTPUT_JPEG', '".OUTPUT_JPEG."'); # Y or N
define ('JPEG_QUALITY', '".JPEG_QUALITY."'); # a number from 0 to 100
define('INTERLACE_SWITCH','".INTERLACE_SWITCH."');
# Note: Please do not edit this file. Edit from the admin section.

# USE_LOCK_TABLES
# The script can lock/unlock tables when a user is selecting pixels
define ('USE_LOCK_TABLES', '".USE_LOCK_TABLES."');

define('BANNER_DIR', '".BANNER_DIR."');

# IM_CONVERT_PATH

define('IM_CONVERT_PATH', '".IM_CONVERT_PATH."');

# Note: Please do not edit this file. Edit from the admin section.
define('EMAIL_USER_ORDER_CONFIRMED', '".EMAIL_USER_ORDER_CONFIRMED."');
define('EMAIL_ADMIN_ORDER_CONFIRMED', '".EMAIL_ADMIN_ORDER_CONFIRMED."');
define('EMAIL_USER_ORDER_COMPLETED', '".EMAIL_USER_ORDER_COMPLETED."');
define('EMAIL_ADMIN_ORDER_COMPLETED', '".EMAIL_ADMIN_ORDER_COMPLETED."');
define('EMAIL_USER_ORDER_PENDED', '".EMAIL_USER_ORDER_PENDED."');
define('EMAIL_ADMIN_ORDER_PENDED', '".EMAIL_ADMIN_ORDER_PENDED."');
define('EMAIL_USER_ORDER_EXPIRED', '".EMAIL_USER_ORDER_EXPIRED."');
define('EMAIL_ADMIN_ORDER_EXPIRED', '".EMAIL_ADMIN_ORDER_EXPIRED."');

define('EM_NEEDS_ACTIVATION', '".EM_NEEDS_ACTIVATION."');
define('EMAIL_ADMIN_ACTIVATION', '".EMAIL_ADMIN_ACTIVATION."');
define('EMAIL_ADMIN_PUBLISH_NOTIFY', '".EMAIL_ADMIN_PUBLISH_NOTIFY."');
define('USE_PAYPAL_SUBSCR', '".USE_PAYPAL_SUBSCR."');
define('EMAIL_USER_EXPIRE_WARNING', '".EMAIL_USER_EXPIRE_WARNING."');
define('DAYS_RENEW', '".DAYS_RENEW."');
define('DAYS_CONFIRMED', '".DAYS_CONFIRMED."');
define('HOURS_UNCONFIRMED', '".HOURS_UNCONFIRMED."');
define('DAYS_CANCEL', '".DAYS_CANCEL."');
define('ENABLE_MOUSEOVER', '".ENABLE_MOUSEOVER."');
define('ENABLE_CLOAKING', '".ENABLE_CLOAKING."');
define('VALIDATE_LINK', '".VALIDATE_LINK."');
define('DISPLAY_PIXEL_BACKGROUND', '".DISPLAY_PIXEL_BACKGROUND."');
define('USE_SMTP', '".USE_SMTP."');
define('EMAIL_HOSTNAME', '".EMAIL_HOSTNAME."');
define('EMAIL_SMTP_SERVER', '".EMAIL_SMTP_SERVER."');
define('EMAIL_SMTP_USER', '".EMAIL_SMTP_USER."');
define('EMAIL_SMTP_PASS', '".EMAIL_SMTP_PASS."');
define('EMAIL_SMTP_AUTH_HOST', '".EMAIL_SMTP_AUTH_HOST."');
define('POP3_PORT', '".POP3_PORT."');
define('EMAIL_POP_SERVER', '".EMAIL_POP_SERVER."');
define('EMAIL_POP_BEFORE_SMTP', '".EMAIL_POP_BEFORE_SMTP."');

define('EMAILS_PER_BATCH', '".EMAILS_PER_BATCH."');
define('EMAILS_MAX_RETRY', '".EMAILS_MAX_RETRY."');
define('EMAILS_ERROR_WAIT', '".EMAILS_ERROR_WAIT."');
define('EMAILS_DAYS_KEEP', '".EMAILS_DAYS_KEEP."');
define('USE_AJAX', '".USE_AJAX."');
define('ANIMATION_SPEED', '".ANIMATION_SPEED."');
define('MAX_BLOCKS', '".MAX_BLOCKS."');
define('MEMORY_LIMIT', '".MEMORY_LIMIT."');

define('REDIRECT_SWITCH', '".REDIRECT_SWITCH."');
define('REDIRECT_URL', '".REDIRECT_URL."');
define('ADVANCED_CLICK_COUNT', '".ADVANCED_CLICK_COUNT."');

define('TRANSITION_EFFECT', '".TRANSITION_EFFECT."');
define('ENABLE_TRANSITIONS', '".ENABLE_TRANSITIONS."');
define('TRANSITION_DURATION', '".TRANSITION_DURATION."');
define('HIDE_TIMEOUT', '".HIDE_TIMEOUT."');
define('MDS_AGRESSIVE_CACHE', '".MDS_AGRESSIVE_CACHE."');

if (defined('MEMORY_LIMIT')) {
	ini_set('memory_limit', MEMORY_LIMIT);
} else {
	ini_set('memory_limit', '32M');
}

\$dbhost = MYSQL_HOST;
\$dbusername = MYSQL_USER;
\$dbpassword = MYSQL_PASS;
\$database_name = MYSQL_DB;

\$connection = @mysql_connect(\"\$dbhost\",\"\$dbusername\", \"\$dbpassword\")
	or \$DB_ERROR = \"Couldn't connect to server.\";
	
\$db = @mysql_select_db(\"\$database_name\", \$connection)
	or \$DB_ERROR = \"Couldn't select database.\";

if (\$DB_ERROR=='') {

	// load HTMLPurifier
    require_once dirname(__FILE__).'/library/HTMLPurifier.auto.php';
    \$purifier = new HTMLPurifier(); 
	
	require_once dirname(__FILE__).'/include/functions2.php';
	\$f2 = new functions2();

	include dirname(__FILE__).'/lang/lang.php';
	require_once (dirname(__FILE__).'/mail/email_message.php');
	require_once (dirname(__FILE__).'/mail/smtp_message.php');
	require_once (dirname(__FILE__).'/mail/smtp.php');
	require_once dirname(__FILE__).'/include/mail_manager.php';
	require_once dirname(__FILE__).'/include/currency_functions.php';
	require_once dirname(__FILE__).'/include/price_functions.php';
	require_once dirname(__FILE__).'/include/functions.php';
	require_once dirname(__FILE__).'/include/image_functions.php';
	if (!get_magic_quotes_gpc()) unfck_gpc();
	//escape_gpc();
}

function get_banner_dir() {
	if (BANNER_DIR=='BANNER_DIR') {	

		\$file_path = SERVER_PATH_TO_ADMIN; // eg e:/apache/htdocs/ojo/admin/

		\$p = preg_split ('%[/\\\]%', \$file_path);
		
		array_pop(\$p);
		array_pop(\$p);
	
		\$dest = implode('/', \$p);
		\$dest = \$dest.'/banners/';

		if (file_exists(\$dest)) {
			\$BANNER_DIR = 'banners/';
		} else {
			\$BANNER_DIR = 'pixels/';
		}
	} else {
		\$BANNER_DIR = BANNER_DIR;
	}
	return \$BANNER_DIR;
 
}

?>";

  // echo "<pre>[$config_str]</pre>";

   /// write out the config..

    $file =fopen ("../config.php", "w");
    fwrite($file, $config_str);

} else {
// load in the headers and footers..

}
require "../config.php";

echo $f2->get_doc(); ?>

<style>
body {
	font-family: 'Arial', sans-serif; 
	font-size:10pt;

}
</style>
<script language="javascript">

	function test_email_window () {

		prams = 
			'host='+document.form1.email_hostname.value+
			'&email_pop_server='+document.form1.email_pop_server.value+
			'&user='+document.form1.email_smtp_user.value+
			'&pass='+document.form1.email_smtp_pass.value+
			'&auth_host='+document.form1.email_smtp_auth_host.value+
			'&php3_port='+document.form1.pop3_port.value;

		window.open('test_email.php?'+prams, '', 'toolbar=no, scrollbars=yes, location=no, statusbar=no, menubar=no, resizable=1, width=800, height=500, left = 50, top = 50');

	}

	</script>
</head>
<body>

<h3>
Main Configuration</h3>
Options on this page affect the running of the pixel advertising system.<p>
Note: <i>Make sure that config.php has write permissions <b>turned on</b> when editing this form. You should turn off write permission after editing this form.</i><br>
<p>
<b>Tip:</b> Looking for where to settings for the grid? It is set in 'Pixel Inventory' -> <a href="inventory.php">Manage Grids</a>. Click on Edit to edit the grid parameters.
</p>

<?php
echo "<p>";
if (is_writable("../config.php")) {
	echo "- config.php is writeable.<br>";
} else {
	echo "- <font color='red'> Note: config.php is not writable. Give write permissions to config.php if you want to save the changes</font><br>";
}

require ('config_form.php');

?>


<p>&nbsp;</p>
</body>