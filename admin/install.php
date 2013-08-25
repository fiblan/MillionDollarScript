<?php
/**
 * @version		$Id: install.php 162 2012-12-12 16:48:21Z ryan $
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
if ($_REQUEST[action]=='install') {
	
	save_db_config();
	require("../config.php");

	if ($conn=check_connection ($_REQUEST[mysql_user], $_REQUEST[mysql_pass],$_REQUEST[mysql_host])) {
		 if (check_db ( $_REQUEST[mysql_db], $conn)) {


			 install_db();

		 } else {

			 echo "<p><font color='red'><b>Install failed: Cannot select database.  ".mysql_error()."</b></font></p>";

		 }
	}
	else {

			 echo "<p><font color='red'><b>Install failed: Cannot connect to database. ".mysql_error()."</b></font></p>";

	}

} else {
	
if(file_exists("../config.php")) {
	require ("../config.php");
} else {
	echo "config.php not found. Maybe you have to rename config-default.php to config.php";
	die();
}



}
$sql = "select * from users";
if ($result = @mysql_query($sql)) {
	echo "<h3>Database successfully Installed.</h3>";
	echo "<p>";
	echo "Next Steps:<br>";
	echo "1. Delete this file (install.php) from the server<br>";
	echo "2. Note: You must go to Admin-&gt;Main Config now and set up the rest of the script.<br>";
	echo " <a href='".BASE_HTTP_PATH."admin/'>Go to Admin</a>. <b>(The default admin password is 'ok'. Please don't forget to change the default password.)</b><br>";
	
	die();

}

function check_connection ($user, $pass,$host) {
	if (!($connection = @mysql_connect("$host","$user", "$pass"))) {

		return false;

	}

	return $connection;
	
}

function check_db ( $db_name, $connection) {
	if (!($db = @mysql_select_db( $db_name,  $connection))){
	 return false;
	}
	return true;
}


?>
<h3>Million Dollar Script - Database Installation</h3>
<p>
Please fill in the form and click install.<br>
Please make sure that the MySQL user has all the permissions to use the database (Admin privileges).<br>
</p>
<?php
if (is_writable("../config.php")) {
	echo "- config.php is writeable. (OK)<br>";
} else {
	echo "- Note: config.php is not writable. Give write permissions (666) to config.php if you want to save the changes<br>";
}
if (is_writable("../pixels/")) {
	echo "- pixels/ directory is writeable. (OK)<br>";
} else {
	echo "- pixels/ directory is not writable. Give write permissions (777) to pixels/ directory<br>";
}

if (is_writable("temp/")) {
	echo "- admin/temp directory is writeable. (OK)<br>";
} else {
	echo "- admin/temp directory is not writable. Give write permissions (777) to admin/temp directory<br>";
}

if (is_writable("../lang/english.php")) {
	echo "- lang/english.php file is writeable. (OK)<br>";
} else {
	echo "- lang/english.php file is not writable. Give write permissions (666) to lang/english.php file<br>";
}

if (is_writable("../upload_files/docs/")) {
	echo "- upload_files/docs/ directory is writeable. (OK)<br>";
} else {
	echo "- upload_files/docs/ directory is not writable. Give write permissions (777) to upload_files/docs/ directory<br>";
}

if (is_writable("../upload_files/images/")) {
	echo "- upload_files/images/ directory is writeable. (OK)<br>";
} else {
	echo "- upload_files/images/ directory is not writable. Give write permissions (777) to upload_files/docs/ directory<br>";
}

// check HTMLPurifier permissions
if (is_writable("../library/HTMLPurifier/DefinitionCache/Serializer")) {
	echo "- library/HTMLPurifier/DefinitionCache/Serializer is writeable. (OK)<br>";
} else {
	echo "- Note: library/HTMLPurifier/DefinitionCache/Serializer is not writable. Give write permissions (try 755 or 777 if that doesn't work) to library/HTMLPurifier/DefinitionCache/Serializer<br>";
}

?>
<form method="post" action="install.php">
<input type="hidden" name="action" value="install">

 <?php 
  
  //print_r($_SERVER);

  $host = $_SERVER['SERVER_NAME']; // hostname
  $http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
  $http_url = explode ("/", $http_url);
  array_pop($http_url); // get rid of filename
  array_pop($http_url); // get rid of /admin
  $http_url = implode ("/", $http_url);
 // echo "<b> $http_url </b>";
  $file_path = $_SERVER['SCRIPT_FILENAME']; // eg e:/apache/htdocs/ojo/admin/edit_config.php
  $file_path = explode ("/", $file_path);
  array_pop($file_path); // get rid of filename
  array_pop($file_path); // get rid of /admin
  $file_path = implode ("/", $file_path);
 // echo "<b> $file_path </b>";

 if (BASE_HTTP_PATH=='') {
	$BASE_HTTP_PATH = "http://".$host.$http_url."/";

 } else {
	$BASE_HTTP_PATH = BASE_HTTP_PATH;
 }

 if (SERVER_PATH_TO_ADMIN=='') {
	$SERVER_PATH_TO_ADMIN = str_replace('\\', '/', getcwd())."/";

 } else {
	 $SERVER_PATH_TO_ADMIN = SERVER_PATH_TO_ADMIN;

 }

 if (!defined('UPLOAD_PATH')) {
	$dir = dirname(__FILE__);
	$dir = preg_split ('%[/\\\]%', $dir);
	$blank = array_pop($dir);
	$dir = implode('/', $dir);
	
	define ('UPLOAD_PATH', $dir.'/upload_files/');
}
if (UPLOAD_PATH=='') {
	$UPLOAD_PATH = str_replace('\\', '/', $file_path."/upload_files/");
} else {
	$UPLOAD_PATH = UPLOAD_PATH;
}

if (!defined('UPLOAD_HTTP_PATH')) {

	$host = $_SERVER['SERVER_NAME']; // hostname
	$http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
	$http_url = explode ("/", $http_url);
	array_pop($http_url); // get rid of filename
	array_pop($http_url); // get rid of /admin
	$http_url = implode ("/", $http_url);

	define ('UPLOAD_HTTP_PATH', "http://".$host.$http_url."/upload_files/");
}
if (UPLOAD_HTTP_PATH=='') {
	$UPLOAD_HTTP_PATH = "http://" . str_replace('\\', '/', $host.$http_url."/upload_files/");
} else {
	$UPLOAD_HTTP_PATH = UPLOAD_HTTP_PATH;
}

  ?>
  <p>&nbsp;</p>
  <table border="0" cellpadding="5" cellspacing="2" style="border-style:groove" id="AutoNumber1" width="100%" bgcolor="#FFFFFF">
    <tr>
      <td colspan="2" bgcolor="#e6f2ea">
      <p ><font face="Verdana" size="1"><b>Paths and Locations</b><br></font></td>
    </tr>
    <tr>
      <td width="20%" bgcolor="#e6f2ea"><font face="Verdana" size="1">Site's HTTP URL (address)</font></td>
      <td bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="base_http_path" size="49" value="<?php echo htmlentities($BASE_HTTP_PATH); ?>"><br>Recommended: <b>http://<?php echo $BASE_HTTP_PATH; ?></b></font></td>
    </tr>
   
	 <tr>
      <td bgcolor="#e6f2ea"><font face="Verdana" size="1">Server Path to Admin</font></td>
      <td bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="server_path_to_admin" size="49" value="<?php echo htmlentities($SERVER_PATH_TO_ADMIN); ?>" ><br>Recommended: <b><?php echo $SERVER_PATH_TO_ADMIN;?>/</b></font></td>
    </tr>
	<tr>
      <td bgcolor="#e6f2ea"><font face="Verdana" size="1">Path to upload directory</font></td>
      <td bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="upload_path" size="55" value="<?php echo htmlentities($UPLOAD_PATH); ?>" ><br>Recommended: <b><?php echo $UPLOAD_PATH;?></b></font></td>
    </tr>
	<tr>
      <td bgcolor="#e6f2ea"><font face="Verdana" size="1">HTTP URL to upload directory</font></td>
      <td bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="upload_http_path" size="55" value="<?php echo htmlentities($UPLOAD_HTTP_PATH); ?>" ><br>Recommended: <b><?php echo $UPLOAD_HTTP_PATH;?></b></font></td>
    </tr>
	<tr>
	<td colspan="2">
	<font face="Verdana" size="1">
NOTES<br>
 - Server Path to Admin is the full path to your admin directory, <font color="red">including a slash at the end</font><br>
 - The Site's HTTP URL must include a<font color="red"> slash at the end</font><br>
 - Use the recommended settings unless you are sure otherwise<br>
 Also, don't forget to set the permissions of the admin/temp/ directory to 777.<br> The script must be able to write  to temp/ dir in the admin<br>
 The script also needs to be able to write to the pixels/ directory (chmod 777) <br>
 -Sometimes your web server configuration may desire different permissions than what is listed here in order for files to execute properly.  i.e. if you are running suExec, etc.<br />
You should check with your host if you are unsure.
 </font>
	</td>

	</tr>
</table>

<p>&nbsp;</p>
  <table border="0" cellpadding="5" cellspacing="2" style="border-style:groove" id="AutoNumber1" width="100%" bgcolor="#FFFFFF">


    <tr>
      <td colspan="2"  bgcolor="#e6f2ea">
      <font face="Verdana" size="1"><b>Mysql Settings</b></font></td>
    </tr>
    <tr>
      <td width="20%" bgcolor="#e6f2ea"><font face="Verdana" size="1">Mysql 
      Database Username</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="mysql_user" size="29" value="<?php echo MYSQL_USER; ?>"></font></td>
    </tr>
	 <tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Mysql 
      Database Password</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="password" name="mysql_pass" size="29" value="<?php echo MYSQL_PASS; ?>"></font></td>
    </tr>
    <tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Mysql 
      Database Name</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="mysql_db" size="29" value="<?php echo MYSQL_DB; ?>"></font></td>
    </tr>
    <tr>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">Mysql 
      Server Hostname</font></td>
      <td  bgcolor="#e6f2ea"><font face="Verdana" size="1">
      <input type="text" name="mysql_host" size="29" value="<?php echo MYSQL_HOST; ?>"></font></td>
    </tr>
	<tr><td colspan="2">
	
	</td></tr>
	</table>
	<p>
	<input type="submit" value="Install">
</p>
</form>
<?php


function save_db_config() {
	require_once '../include/functions2.php';
	$f2 = new functions2();

	$filename = "../config.php";
	$handle  = fopen($filename, "r");
	$contents = fread($handle , filesize($filename));
	fclose ($handle);
	$handle  = fopen($filename, "w");

	$contents = preg_replace ( "/.*define\('MYSQL_HOST',[ ]*'[^']*'\);[ ]*/U", "define('MYSQL_HOST', '".$_REQUEST['mysql_host']."');", $contents) ;
	$contents = preg_replace ( "/.*define\('MYSQL_USER',[ ]*'[^']*'\);[ ]*/U", "define('MYSQL_USER', '".$_REQUEST['mysql_user']."');", $contents) ;
	$contents = preg_replace ( "/.*define\('MYSQL_PASS',[ ]*'[^']*'\);[ ]*/U", "define('MYSQL_PASS', '".$_REQUEST['mysql_pass']."');", $contents) ;
	$contents = preg_replace ( "/.*define\('MYSQL_DB',[ ]*'[^']*'\);[ ]*/U", "define('MYSQL_DB', '".    $_REQUEST['mysql_db']."');", $contents) ;
	
	$contents = preg_replace ( "/.*define\('SERVER_PATH_TO_ADMIN',[ ]*'[^']*'\);[ ]*/U", "define('SERVER_PATH_TO_ADMIN', '".$_REQUEST['server_path_to_admin']."');", $contents) ;
	
	$contents = preg_replace ( "/.*define\('BASE_HTTP_PATH',[ ]*'[^']*'\);[ ]*/U", "define('BASE_HTTP_PATH', '". $_REQUEST['base_http_path']."');", $contents) ;

	$contents = preg_replace ( "/.*define\('UPLOAD_PATH',[ ]*'[^']*'\);[ ]*/U", "define('UPLOAD_PATH', '". $_REQUEST['upload_path']."');", $contents) ;

	$contents = preg_replace ( "/.*define\('UPLOAD_HTTP_PATH',[ ]*'[^']*'\);[ ]*/U", "define('UPLOAD_HTTP_PATH', '". $_REQUEST['upload_http_path']."');", $contents) ;

	fwrite($handle , $contents);

	fclose ($handle);
	//echo " done.";
}
###################################
function query_parser($q){
   // strip the comments from the query
   while($n=strpos($q,'--')){
       $k=@strpos($q,"\n",$n+1);
       if(!$k) $k=strlen($q);
       $q=substr($q,0,$n).substr($q,$k+1);
   }

   $queries = preg_split("/;;;/", $q);

  
   return $queries;
}
#############################################
function multiple_query($q){
   $queries=query_parser($q);
   $n=count($queries);
   $results=array();

   for($i=0;$i<$n;$i++)
       $results[$i]=array(
           mysql_query($queries[$i]),
           mysql_errno(),
           mysql_error(),
			$queries[$i]
       );

   return $results;
}

##################################################

function install_db() {

	$sql = "
	
	CREATE TABLE `ads` (
  `ad_id` int(11) NOT NULL auto_increment,
  `user_id` varchar(255) NOT NULL default '0',
  `ad_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `order_id` int(11) default '0',
  `banner_id` int(11) NOT NULL default '0',
  `1` varchar(255) NOT NULL default '',
  `2` varchar(255) NOT NULL default '',
  `3` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`ad_id`)
);;;
	
	
	CREATE TABLE `banners` (
  `banner_id` int(11) NOT NULL auto_increment,
  `grid_width` int(11) NOT NULL default '0',
  `grid_height` int(11) NOT NULL default '0',
  `days_expire` mediumint(9) default '0',
  `price_per_block` float NOT NULL default '0',
  `name` varchar(255) NOT NULL default '',
  `currency` char(3) NOT NULL default 'USD',
  `publish_date` datetime default NULL,
  `max_orders` int(11) NOT NULL default '0',
  `block_width` int(11) NOT NULL default '10',
  `block_height` int(11) NOT NULL default '10',
  `grid_block` text NOT NULL,
  `nfs_block` text NOT NULL,
  `tile` text NOT NULL,
  `usr_grid_block` text NOT NULL,
  `usr_nfs_block` text NOT NULL,
  `usr_ord_block` text NOT NULL,
  `usr_res_block` text NOT NULL,
  `usr_sel_block` text NOT NULL,
  `usr_sol_block` text NOT NULL,
  `max_blocks` int(11) NOT NULL default '0',
  `min_blocks` int(11) NOT NULL default '0',
  `date_updated` datetime NOT NULL default '0000-00-00 00:00:00',
  `bgcolor` varchar(7) NOT NULL default '#FFFFFF',
  `auto_publish` char(1) NOT NULL default 'N',
  `auto_approve` char(1) NOT NULL default 'N',
  `time_stamp` int(11) default NULL,
  PRIMARY KEY  (`banner_id`)
);;;


	INSERT INTO `banners` VALUES (1, 100, 100, 1, 100, 'Million Pixels. (1000x1000)', 'USD', NULL, 1, 10, 10, 'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAAHklEQVR4nGO8cuUKA27AwsDAoK2tjUuaCY/W4SwNAJbvAxP1WmxKAAAAAElFTkSuQmCC', 'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAAFUlEQVR4nGP8//8/A27AhEduBEsDAKXjAxF9kqZqAAAAAElFTkSuQmCC', 'iVBORw0KGgoAAAANSUhEUgAAAHgAAAB4AQMAAAADqqSRAAAABlBMVEXW19b///9ZVCXjAAAAJklEQVR4nGNgQAP197///Y8gBpw/6r5R9426b9R9o+4bdd8wdB8AiRh20BqKw9IAAAAASUVORK5CYII=', 'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAAHklEQVR4nGO8cuUKA27AwsDAoK2tjUuaCY/W4SwNAJbvAxP1WmxKAAAAAElFTkSuQmCC', 'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAAFUlEQVR4nGP8//8/A27AhEduBEsDAKXjAxF9kqZqAAAAAElFTkSuQmCC', 'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAAFElEQVR4nGP83+DAgBsw4ZEbwdIAJ/sB02xWjpQAAAAASUVORK5CYII=', 'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAAE0lEQVR4nGP8/58BD2DCJzlypQF0BwISHGyJPgAAAABJRU5ErkJggg==', 'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAAE0lEQVR4nGNk+M+ABzDhkxy50gBALQETmXEDiQAAAABJRU5ErkJggg==', 'iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAAEklEQVR4nGP8z4APMOGVHbHSAEEsAROxCnMTAAAAAElFTkSuQmCC', 500, 0, '2007-02-17 10:48:32', '#FFffFF', 'Y', 'Y', 1171775611);;;

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL default '0',
  `category_name` varchar(255) NOT NULL default '',
  `parent_category_id` int(11) NOT NULL default '0',
  `obj_count` int(11) NOT NULL default '0',
  `form_id` int(11) NOT NULL default '0',
  `allow_records` set('Y','N') NOT NULL default 'Y',
  `list_order` smallint(6) NOT NULL default '1',
  `search_set` text NOT NULL,
  `seo_fname` varchar(100) default NULL,
  `seo_title` varchar(255) default NULL,
  `seo_desc` varchar(255) default NULL,
  `seo_keys` varchar(255) default NULL,
  PRIMARY KEY  (`category_id`),
  KEY `composite_index` (`parent_category_id`,`category_id`)
);;;


CREATE TABLE `form_fields` (
  `form_id` int(11) NOT NULL default '0',
  `field_id` int(11) NOT NULL auto_increment,
  `section` tinyint(4) NOT NULL default '1',
  `reg_expr` varchar(255) NOT NULL default '',
  `field_label` varchar(255) NOT NULL default '-noname-',
  `field_type` varchar(255) NOT NULL default 'TEXT',
  `field_sort` tinyint(4) NOT NULL default '0',
  `is_required` set('Y','N') NOT NULL default 'N',
  `display_in_list` set('Y','N') NOT NULL default 'N',
  `is_in_search` set('Y','N') NOT NULL default 'N',
  `error_message` varchar(255) NOT NULL default '',
  `field_init` varchar(255) NOT NULL default '',
  `field_width` tinyint(4) NOT NULL default '20',
  `field_height` tinyint(4) NOT NULL default '0',
  `list_sort_order` tinyint(4) NOT NULL default '0',
  `search_sort_order` tinyint(4) NOT NULL default '0',
  `template_tag` varchar(255) NOT NULL default '',
  `is_hidden` char(1) NOT NULL default '',
  `is_anon` char(1) NOT NULL default '',
  `field_comment` text NOT NULL,
  `category_init_id` int(11) NOT NULL default '0',
  `is_cat_multiple` set('Y','N') NOT NULL default 'N',
  `cat_multiple_rows` tinyint(4) NOT NULL default '1',
  `is_blocked` char(1) NOT NULL default 'N',
  `multiple_sel_all` char(1) NOT NULL default 'N',
  `is_prefill` char(1) NOT NULL default 'N',
  PRIMARY KEY  (`field_id`)
);;;

INSERT INTO `form_fields` VALUES (1, 1, 1, 'not_empty', 'Ad Text', 'TEXT', 1, 'Y', '', '', 'was not filled in', '', 80, 0, 0, 0, 'ALT_TEXT', '', '', '', 0, '', 0, '', '', '');;;
INSERT INTO `form_fields` VALUES (1, 2, 1, 'url', 'URL', 'TEXT', 2, 'Y', '', '', 'is not valid.', '', 80, 0, 0, 0, 'URL', '', '', '', 0, '', 0, '', '', '');;;
INSERT INTO `form_fields` VALUES (1, 3, 1, '', 'Additional Image', 'IMAGE', 3, '', '', '', '', '', 0, 0, 0, 0, 'IMAGE', '', '', '(This image will be displayed when a mouse pointer is placed over your ad)', 0, '', 0, '', '', '');;;



CREATE TABLE `form_field_translations` (
  `field_id` int(11) NOT NULL default '0',
  `lang` char(2) NOT NULL default '',
  `field_label` text NOT NULL,
  `error_message` varchar(255) NOT NULL default '',
  `field_comment` text NOT NULL,
  PRIMARY KEY  (`field_id`,`lang`),
  KEY `field_id` (`field_id`)
) ;;;

INSERT INTO `form_field_translations` VALUES (1, 'EN', 'Ad Text', 'was not filled in', '');;;
INSERT INTO `form_field_translations` VALUES (2, 'EN', 'URL', 'is not valid.', '');;;
INSERT INTO `form_field_translations` VALUES (3, 'EN', 'Additional Image', '', '(This image will be displayed when a mouse pointer is placed over your ad)');;;


CREATE TABLE `form_lists` (
  `form_id` int(11) NOT NULL default '0',
  `field_type` varchar(255) NOT NULL default '',
  `sort_order` int(11) NOT NULL default '0',
  `field_id` varchar(255) NOT NULL default '0',
  `template_tag` varchar(255) NOT NULL default '',
  `column_id` int(11) NOT NULL auto_increment,
  `admin` set('Y','N') NOT NULL default '',
  `truncate_length` smallint(4) NOT NULL default '0',
  `linked` set('Y','N') NOT NULL default 'N',
  `clean_format` set('Y','N') NOT NULL default '',
  `is_bold` set('Y','N') NOT NULL default '',
  `is_sortable` set('Y','N') NOT NULL default 'N',
  `no_wrap` set('Y','N') NOT NULL default '',
  PRIMARY KEY  (`column_id`)
) ;;;

INSERT INTO `form_lists` VALUES (1, 'TIME', 1, 'ad_date', 'DATE', 1, 'N', 0, 'N', 'N', 'N', 'Y', 'N');;;
INSERT INTO `form_lists` VALUES (1, 'EDITOR', 2, '1', 'ALT_TEXT', 2, 'N', 0, 'Y', 'N', 'N', 'Y', 'N');;;
INSERT INTO `form_lists` VALUES (1, 'TEXT', 3, '2', 'URL', 3, 'N', 0, 'N', 'N', 'N', 'N', 'N');;;


CREATE TABLE `temp_orders` (
  `session_id` varchar(32) NOT NULL default '',
  `blocks` text NOT NULL,
  `order_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `price` float NOT NULL default '0',
  `quantity` int(11) NOT NULL default '0',
  `banner_id` int(11) NOT NULL default '1',
  `currency` char(3) NOT NULL default 'USD',
  `days_expire` int(11) NOT NULL default '0',
  `date_stamp` datetime default NULL,
  `package_id` int(11) NOT NULL default '0',
  `ad_id` int(11) default '0',
  `block_info` text NOT NULL,
  PRIMARY KEY  (`session_id`)
);;;

	CREATE TABLE `blocks` (
	  `block_id` int(11) NOT NULL default '0',
	  `user_id` int(11) default NULL,
	  `status` set('reserved','sold','free','ordered','nfs') NOT NULL default '',
	  `x` int(11) NOT NULL default '0',
	  `y` int(11) NOT NULL default '0',
	  `image_data` text NOT NULL,
	  `url` varchar(255) NOT NULL default '',
	  `alt_text` text NOT NULL default '',
	  `file_name` varchar(255) NOT NULL default '',
	  `mime_type` varchar(100) NOT NULL default '',
	  `approved` set('Y','N') NOT NULL default '',
	  `published` set('Y','N') NOT NULL default '',
	  `currency` char(3) NOT NULL default 'USD',
	  `order_id` int(11) NOT NULL default '0',
	  `price` float default NULL,
	  `banner_id` int(11) NOT NULL default '1',
		`ad_id` INT(11)  NOT NULL default '0',
		`click_count` INT NOT NULL,
	  PRIMARY KEY  (`block_id`,`banner_id`)
	);;;

	CREATE TABLE `clicks` (
		`banner_id` INT NOT NULL ,
		`block_id` INT NOT NULL ,
		`user_id` INT NOT NULL ,
		`date` date NOT NULL default '0000-00-00',
		`clicks` INT NOT NULL ,
		PRIMARY KEY ( `banner_id` , `block_id` ,  `date` ) 
	);;;


	CREATE TABLE `config` (
	  `key` varchar(255) NOT NULL default '',
	  `val` varchar(255) NOT NULL default '',
	  PRIMARY KEY  (`key`)
	);;;

	INSERT INTO `config` VALUES ('EXPIRE_RUNNING', 'NO');;;
	INSERT INTO `config` VALUES ('LAST_EXPIRE_RUN', '1138243912');;;
	INSERT INTO `config` VALUES ('SELECT_RUNNING', 'NO');;;





	CREATE TABLE `currencies` (
	  `code` char(3) NOT NULL default '',
	  `name` varchar(50) NOT NULL default '',
	  `rate` decimal(10,4) NOT NULL default '1.0000',
	  `is_default` set('Y','N') NOT NULL default 'N',
	  `sign` varchar(8) NOT NULL default '',
	  `decimal_places` smallint(6) NOT NULL default '0',
	  `decimal_point` char(3) NOT NULL default '',
	  `thousands_sep` char(3) NOT NULL default '',
	  PRIMARY KEY  (`code`)
	);;;


	INSERT INTO `currencies` VALUES ('AUD', 'Australian Dollar', 1.0075, 'N', '$', 2, '.', ',');;;
	INSERT INTO `currencies` VALUES ('CAD', 'Canadian Dollar', 0.99489, 'N', '$', 2, '.', ',');;;
	INSERT INTO `currencies` VALUES ('EUR', 'Euro', 0.77476, 'N', '€', 2, '.', ',');;;
	INSERT INTO `currencies` VALUES ('GBP', 'British Pound', 0.64337, 'N', '£', 2, '.', ',');;;
	INSERT INTO `currencies` VALUES ('JPY', 'Japanese Yen', 83.149, 'N', '¥', 0, '.', ',');;;
	INSERT INTO `currencies` VALUES ('USD', 'U.S. Dollar', 1.0000, 'Y', '$', 2, '.', ',');;;



	CREATE TABLE `lang` (
	  `lang_code` char(2) NOT NULL default '',
	  `lang_filename` varchar(32) NOT NULL default '',
	  `lang_image` varchar(32) NOT NULL default '',
	  `is_active` set('Y','N') NOT NULL default '',
	  `name` varchar(32) NOT NULL default '',
	  `charset` varchar(32) NOT NULL default '',
	  `image_data` text NOT NULL,
	  `mime_type` varchar(255) NOT NULL default '',
	  `is_default` char(1) NOT NULL default 'N',
	  PRIMARY KEY  (`lang_code`)
	);;;

	 

	INSERT INTO `lang` VALUES ('EN', 'english.php', 'english.gif', 'Y', 'English', 'en_US.utf8', 'R0lGODlhGQARAMQAAAURdBYscgNNfrUOEMkMBdAqE9UTMtItONNUO9w4SdxmaNuObhYuh0Y5lCxVlFJcpqN2ouhfjLCrrOeRmeHKr/Wy3Lje4dPW3PDTz9/q0vXm1ffP7MLt5/f0+AAAAAAAACwAAAAAGQARAAAF02AAMIDDkOgwEF3gukCZIICI1jhFDRmOS4dF50aMVSqEjehFIWQ2kJLUMRoxCCsNzDFBZDCuh1RMpQY6HZYIiOlIYqKy9JZIqHeZTqMWnvoZCgosCkIXDoeIAGJkfmgEB3UHkgp1dYuKVWJXWCsEnp4qAwUcpBwWphapFhoanJ+vKxOysxMRgbcDHRlfeboZF2mvwp+5Eh07YC9naMzNzLmKuggTDy8G19jZ2NAiFB0LBxYuC+TlC7Syai8QGU0TAs7xaNxLDLoDdsPDuS98ABXfQgAAOw==', 'image/gif', 'Y');;;



	CREATE TABLE `orders` (
  `user_id` int(11) NOT NULL default '0',
  `order_id` int(11) NOT NULL auto_increment,
  `blocks` text NOT NULL,
  `status` set('pending','completed','cancelled','confirmed','new','expired','deleted','renew_wait','renew_paid') NOT NULL default '',
  `order_date` datetime NOT NULL default '0000-00-00 00:00:00',
  `price` float NOT NULL default '0',
  `quantity` int(11) NOT NULL default '0',
  `banner_id` int(11) NOT NULL default '1',
  `currency` char(3) NOT NULL default 'USD',
  `days_expire` int(11) NOT NULL default '0',
  `date_published` datetime default NULL,
  `date_stamp` datetime default NULL,
  `expiry_notice_sent` set('Y','N') NOT NULL default '',
  `package_id` int(11) NOT NULL default '0',
  `ad_id` int(11) default NULL,
  `approved` set('Y','N') NOT NULL default 'N',
  `published` set('Y','N') NOT NULL default '',
  `subscr_status` varchar(32) NOT NULL default '',
  `original_order_id` int(11) default NULL,
  `previous_order_id` int(11) NOT NULL default '0',
  PRIMARY KEY  (`order_id`)
);;;


	CREATE TABLE `packages` (
  `banner_id` int(11) NOT NULL default '0',
  `days_expire` int(11) NOT NULL default '0',
  `price` float NOT NULL default '0',
  `currency` char(3) NOT NULL default '',
  `package_id` int(11) NOT NULL auto_increment,
  `is_default` set('Y','N') default NULL,
  `max_orders` mediumint(9) NOT NULL default '0',
  `description` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`package_id`)
);;;


	CREATE TABLE `prices` (
	  `price_id` int(11) NOT NULL auto_increment,
	  `banner_id` int(11) NOT NULL default '0',
	  `row_from` int(11) NOT NULL default '0',
	  `row_to` int(11) NOT NULL default '0',
	  `block_id_from` int(11) NOT NULL default '0',
	  `block_id_to` int(11) NOT NULL default '0',
	  `price` float NOT NULL default '0',
	  `currency` char(3) NOT NULL default '',
	  `color` varchar(50) NOT NULL default '',
		col_from int(11) default NULL,
		col_to int(11) default NULL,
	  PRIMARY KEY  (`price_id`)
	);;;



	CREATE TABLE `transactions` (
	  `transaction_id` int(11) NOT NULL auto_increment,
	  `date` datetime NOT NULL default '0000-00-00 00:00:00',
	  `order_id` int(11) NOT NULL default '0',
	  `type` varchar(32) NOT NULL default '',
	  `amount` float NOT NULL default '0',
	  `currency` char(3) NOT NULL default '',
	  `txn_id` varchar(128) NOT NULL default '',
	  `reason` varchar(64) NOT NULL default '',
	  `origin` varchar(32) NOT NULL default '',
	  PRIMARY KEY  (`transaction_id`)
	);;;

	 

	CREATE TABLE `users` (
	  `ID` int(11) NOT NULL auto_increment,
	  `IP` varchar(50) NOT NULL default '',
	  `SignupDate` datetime NOT NULL default '0000-00-00 00:00:00',
	  `FirstName` varchar(50) NOT NULL default '',
	  `LastName` varchar(50) NOT NULL default '',
	  `Rank` int(11) NOT NULL default '1',
	  `Username` varchar(50) NOT NULL default '',
	  `Password` varchar(50) NOT NULL default '',
	  `Email` varchar(255) NOT NULL default '',
	  `Newsletter` int(11) NOT NULL default '1',
	  `Notification1` int(11) NOT NULL default '0',
	  `Notification2` int(11) NOT NULL default '0',
	  `Aboutme` longtext NOT NULL,
	  `Validated` int(11) NOT NULL default '0',
	  `CompName` varchar(255) NOT NULL default '',
	  `login_date` datetime NOT NULL default '0000-00-00 00:00:00',
	  `logout_date` datetime NOT NULL default '0000-00-00 00:00:00',
	  `login_count` int(11) NOT NULL default '0',
	  `last_request_time` datetime NOT NULL default '0000-00-00 00:00:00',
	  `click_count` int(11) NOT NULL default '0',
	  PRIMARY KEY  (`ID`),
	  UNIQUE KEY `Username` (`Username`)
	);;;
		
	CREATE TABLE `mail_queue` (
		`mail_id` int(11) NOT NULL auto_increment,
		`mail_date` datetime NOT NULL default '0000-00-00 00:00:00',
		`to_address` varchar(128) NOT NULL default '',
		`to_name` varchar(128) NOT NULL default '',
		`from_address` varchar(128) NOT NULL default '',
		`from_name` varchar(128) NOT NULL default '',
		`subject` varchar(255) NOT NULL default '',
		`message` text NOT NULL,
		`html_message` text NOT NULL,
		`attachments` set('Y','N') NOT NULL default '',
		`status` set('queued','sent','error') NOT NULL default '',
		`error_msg` varchar(255) NOT NULL default '',
		`retry_count` smallint(6) NOT NULL default '0',
		`template_id` int(11) NOT NULL default '0',
		`att1_name` varchar(128) NOT NULL default '',
		`att2_name` varchar(128) NOT NULL default '',
		`att3_name` varchar(128) NOT NULL default '',
		`date_stamp` datetime NOT NULL default '0000-00-00 00:00:00',
		PRIMARY KEY  (`mail_id`));;;

	CREATE TABLE `cat_name_translations` (
  `category_id` int(11) NOT NULL default '0',
  `lang` char(2) NOT NULL default '',
  `category_name` text NOT NULL,
  PRIMARY KEY  (`category_id`,`lang`),
  KEY `category_id` (`category_id`)
) ;;;

CREATE TABLE `codes` (
  `field_id` varchar(30) NOT NULL default '',
  `code` varchar(5) NOT NULL default '',
  `description` varchar(30) NOT NULL default '',
  PRIMARY KEY  (`field_id`,`code`)
) ;;;

	CREATE TABLE `codes_translations` (
  `field_id` int(11) NOT NULL default '0',
  `code` varchar(10) NOT NULL default '',
  `description` varchar(255) NOT NULL default '',
  `lang` char(2) NOT NULL default '',
  PRIMARY KEY  (`field_id`,`code`,`lang`)
) 
	";

	mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS) or die(mysql_error());
	
	mysql_select_db(MYSQL_DB) or die(mysql_error());

	/* You can use it like this */

	$queries=multiple_query($sql);

	for($i=0;$i<count($queries);$i++)
		if($queries[$i][1]==0){
       /* some code.... with the result in $queries[$i][0] */
	}
	else
		echo "<pre>Error: ".$queries[$i][2]."(".$queries[$i][3].")<br>\n</pre>";

	//$result = mysql_query ($sql) or die (mysql_error());
	//$rows = mysql_affected_rows ($result);;;
	echo count($queries)." Operations Completed.<br>";


}


?>