<?php
/**
 * @version		$Id: language.php 88 2010-10-12 16:43:19Z ryan $
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

require("../config.php");
require ('admin_common.php');
require_once ('../include/dynamic_forms.php');
require_once ('../include/code_functions.php');
require_once ('../include/category.inc.php');


?>
<?php echo $f2->get_doc(); ?>

<style>
body {

	font-family: 'Arial', sans-serif; 
	font-size:10pt;

}
</style>

<script language="JavaScript" type="text/javascript">

function confirmLink(theLink, theConfirmMsg)
   {

       if (theConfirmMsg == '' || typeof(window.opera) != 'undefined') {
           return true;
       }

       var is_confirmed = confirm(theConfirmMsg + '\n');
       if (is_confirmed) {
           theLink.href += '&is_js_confirmed=1';
       }

       return is_confirmed;
   } // end of the 'confirmLink()' function

</script>

</head>

<body>

<!--span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="filter.php">Filter</a></span-->	
<hr>
<?php


function validate_input () {
	if ($_REQUEST['lang_code']=='') {
		
		$error .= "- Language Code is blank <br>";
	}
	if ($_REQUEST['lang_filename']=='') {

		$error .= "- No language file selected <br>";

	}
	if (($_FILES['lang_image']['name']=='') && ($_REQUEST['action']!='edit')) {
		$error .= "- No image uploaded <br>";
	}
	if ($_REQUEST['name']=='') {

		$error .= "- Language name is blank<br>";

	}

	return $error;



}

if ($_REQUEST['action']=='activate') {

	$sql = "UPDATE lang set is_active='Y' where lang_code='".$_REQUEST['code']."' ";
	mysql_query($sql) or die (mysql_error());

}

if ($_REQUEST['action']=='deactivate') {

	$sql = "UPDATE lang set is_active='N' where lang_code='".$_REQUEST['code']."' ";
	mysql_query($sql) or die (mysql_error());

}

if ($_REQUEST['action']=='default') {

	$sql = "UPDATE lang set is_default='N' ";
	mysql_query($sql) or die (mysql_error());

	$sql = "UPDATE lang set is_default='Y' where lang_code='".$_REQUEST['code']."' ";
	mysql_query($sql) or die (mysql_error());

}

if ($_REQUEST['action']=='delete') {

	$sql = "DELETE FROM lang WHERE lang_code='".$_REQUEST['code']."' ";
	mysql_query($sql) or die (mysql_error());

	


}


if ($_REQUEST['submit']!='') {

	$error = validate_input();

	if ($error == '') {
		//print_r ($_REQUEST);


		if ($_FILES['lang_image']['tmp_name'] != '') {
			$data = base64_encode(fread(fopen($_FILES['lang_image']['tmp_name'], "r"),$_FILES['lang_image']['size']));
			$image_file = $_FILES['lang_image']['name'];	
			$mime_type = $_FILES['lang_image']['type'];

			$image_sql = "image_data='$data', mime_type='$mime_type', lang_image='$image_file',";


		}

		if ($_REQUEST['action']=='edit') {

			$sql = "UPDATE `lang` SET name='".$_REQUEST['name']."', $image_sql charset='".$_REQUEST['charset']."', lang_filename='".$_REQUEST['lang_filename']."' WHERE `lang_code`='$_REQUEST[lang_code]' ";

		} else {
		
			$sql = "INSERT INTO `lang` ( `lang_code` , `lang_filename` , `lang_image` , `is_active` , `name` , `image_data`, `mime_type`, `is_default`, `charset` ) VALUES ('".$_REQUEST['lang_code']."', '".$_REQUEST['lang_filename']."', '$image_file', 'Y', '".$_REQUEST['name']."', '$data', '$mime_type', 'N', '".$_REQUEST['charset']."')";
		}
		//echo "Temp file is: ".$_FILES['lang_image']['tmp_name']."<br>";
		//echo "$sql";

		mysql_query($sql) or die (mysql_error().$sql);

		$_REQUEST['new']='';
		$_REQUEST['edit']='';

		// reload available langs..

		global $AVAILABLE_LANGS;
		global $LANG_FILES;
		$sql = "SELECT * FROM lang ";
		$result = mysql_query ($sql) or die (mysql_error());
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			$AVAILABLE_LANGS [$row['lang_code']] = $row['name'];
			$LANG_FILES [$row['lang_code']] = $row['lang_filename'];
		}

		// update category translations
		// (copy English to new lang)
		//format_cat_translation_table (0);

		// update code translations
		// (copy English to new lang)
		
		$sql = "SELECT * FROM form_fields WHERE `field_type`='RADIO' or `field_type`='CHECK' or `field_type`='MSELECT' or `field_type`='SELECT'  ";
		$result = mysql_query($sql) or die (mysql_error().$sql);
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
			format_codes_translation_table ($row[field_id]);
		}

		// update forms
		// (copy English to new lang)

		format_field_translation_table (1);

		
	} else {

		

	}

}



?>



<table border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9" >
			<tr bgColor="#eaeaea">
				<td><b><font size="2">Language</b></font></td>
				<td><b><font size="2">Code</b></font></td>
				<td><b><font size="2">File</b></font></td>
				<td><b><font size="2">Image</b></font></td>
				<td><b><font size="2">Active</b></font></td>
				<td><b><font size="2">Tool</b></font></td>
			</tr>
<?php
			$result = mysql_query("select * FROM lang ") or die (mysql_error());
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

				?>

				<tr bgcolor="#ffffff">

				<td><font size="2"><?php echo $row['name'];?></font></td>
				<td><font size="2"><?php echo $row['lang_code'];?></font></td>
				<td><font size="2"><?php echo $row['lang_filename'];?></font></td>
				<td><font size="2"><img alt="<?php echo $row['lang_code'];?>" src="lang_image.php?code=<?php echo $row['lang_code'];?>"/></font></td>
				<td><font size="2">  <?php if ($row[is_active]=='Y') { ?><IMG SRC="active.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALT=""><?php } else { ?><IMG SRC="notactive.gif" WIDTH="16" HEIGHT="16" BORDER="0" ALT=""><?php } ;?> <?php if ($row[is_active]!='Y') {?> [<a href="<?php echo $_SERVER['PHP_SELF'];?>?action=activate&code=<?php echo $row['lang_code'];?>">Activate</a>] <?php } if ($row[is_active]=='Y') {?> [<a href="<?php echo $_SERVER['PHP_SELF'];?>?action=deactivate&code=<?php echo $row['lang_code'];?>">Deactivate</a>] <?php }?>[<a href="<?php echo $_SERVER['PHP_SELF'];?>?action=edit&code=<?php echo $row['lang_code'];?>">Edit</a>] <?php if ($row['is_default']!='Y') {  ?> [<a onclick=" return confirmLink(this, 'Delete, are you sure?') " href="<?php echo $_SERVER['PHP_SELF'];?>?action=delete&code=<?php echo $row['lang_code'];?>">Delete</a>] <?php } ?> 
				[<a href="<?php echo $_SERVER['PHP_SELF'];?>?action=default&code=<?php echo $row['lang_code'];?>"><?php if ($row[is_default]=='N') { echo "Set Default";  }?></a> <?php if ($row[is_default]=='Y') { echo "Default";} ; ?>]
				</font></td>
				<?php
				$new_window = "onclick=\"window.open('translation_tool.php?target_lang=".$row[lang_code]."', '', 'toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=yes,resizable=1,width=800,height=500,left = 50,top = 50');return false;\"";	
				
				?>
				<td>
				<font size="2"><a href="" <?php echo $new_window; ?> >Translation / Editing Tool</a>
				
				</td>

				</tr>


				<?php

			}
?>
</table>
<input type="button" value="New Language..." onclick="window.location='language.php?new=1'">
<p>
Note: Before adding a new language, please copy english_default.php and name this file to the language of your choice. Eg copy english_default.php to spanish.php. 
</p>
<p>
Please modify the langauge files from the web using the editing tool above.
</p>
<hr>
<?php

if ($error !='') {
	echo "<b><font color='red'>ERROR:</font></b> Cannot save langauge into database.<br>";
	echo $error;

}

function lang_file_options () {

	//print_r($_REQUEST);

	$dh = opendir ("../lang");
	while (($file = readdir($dh)) !== false) {
		if ($_REQUEST['lang_filename'] ==  $file) {
			$sel = " selected ";
		} else {
			$sel = "";
		}
		if (($file != '.') && ($file != '..') && ($file != 'lang.php' ) && ($file != 'english_default.php' ) && ($file != 'index.html' )){
           echo "<option value='$file' $sel>$file</option>\n";
		}
     }
     closedir($dh);



}

if ($_REQUEST['charset']=='') {
	//$_REQUEST['charset'] = "windows-1252";

}

if ($_REQUEST['action']=='edit') {

	$sql = "SELECT * FROM lang WHERE `lang_code`='".$_REQUEST['code']."' ";
	$result = mysql_query($sql) or die (mysql_error());
	$row = mysql_fetch_array($result, MYSQL_ASSOC);
	
	$_REQUEST['name'] = $row['name'];
	$_REQUEST['lang_code'] = $row['lang_code'];
	$_REQUEST['lang_filename'] = $row["lang_filename"];
	//$_REQUEST['lang_image'] = "lang_image";
	$_REQUEST['charset'] = $row["charset"];

	$disabled = " disabled ";

}

?>

<form enctype="multipart/form-data" method="post">
<?php

if ($_REQUEST['new']=='1') {
	echo "<h4>New Language:</h4>";
	//echo "<p>Note: Make sure that you create a file for your new language in the /lang directory.</p>";
}
if ($_REQUEST['action']=='edit') {
	echo "<h4>Edit Language:</h4>";
}

if (($_REQUEST['new']!='') || ($_REQUEST['action']=='edit')) {


?>
<input type="hidden" value="<?php echo $_REQUEST['action']?>" name="action" >
<input type="hidden" value="<?php echo $_REQUEST['lang_code']?>" name="lang_code" >
<table border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9">
<tr bgcolor="#ffffff" ><td><font size="2">Language Name:</font></td><td><input size="30" type="text" name="name" value="<?php echo $_REQUEST['name']; ?>"/> eg. English</td></tr>
<tr bgcolor="#ffffff" ><td><font size="2">Language Code:</font></td><td><input <?php echo $disabled; ?> size="2" type="text" name="lang_code" value="<?php echo $_REQUEST['lang_code']; ?>"/> eg. EN</td></tr>
<tr bgcolor="#ffffff" ><td><font size="2">Language File:</font></td><td><select  name="lang_filename" ><option></option><?php lang_file_options(); ?></td></tr>
<tr bgcolor="#ffffff" ><td><font size="2">Image:</font></td><td><input size="15" type="file" name="lang_image" value=""></td></tr>
<!--
<tr bgcolor="#ffffff" ><td><font size="2">Parameter for setlocale() function:</font></td><td><input size="15" type="text" name="charset" value="<?php echo $_REQUEST['charset']; ?>"><font size="2">(List of valid locale strings for windows can be found at: <a href="http://msdn.microsoft.com/library/default.asp?url=/library/en-us/vclib/html/_crt_language_strings.asp" target="new_">http://msdn.microsoft.com/library/default.asp?url=/library/en-us/vclib/html/_crt_language_strings.asp</a>. Documentation of setlocale function available at: <a href="http://php.net/setlocale">http://php.net/setlocale</a> Leave this field blank if unsure.)</a></td></tr>
-->
</table>
<input type="submit" name="submit" value="Submit">
</form>
<?php

}

?>
</body>

</html>