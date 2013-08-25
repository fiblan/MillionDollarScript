<?php
/**
 * @version		$Id: adtemplate.php 126 2011-02-10 03:29:31Z ryan $
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
 session_start();
require ('../config.php');
require ("admin_common.php");
require_once ("../include/ads.inc.php");


?>
<?php echo $f2->get_doc(); ?>

<script language="JavaScript" type="text/javascript">

	function confirmLink(theLink, theConfirmMsg) {
       // Check if Confirmation is not needed
       // or browser is Opera (crappy js implementation)
       if (theConfirmMsg == '' || typeof(window.opera) != 'undefined') {
           return true;
       }

       var is_confirmed = confirm(theConfirmMsg + '\n');
       if (is_confirmed) {
           theLink.href += '&is_js_confirmed=1';
       }

       return is_confirmed;
	}
</script>

</head>

<body style=" font-family: 'Arial', sans-serif; font-size:10pt; background: #fff  url( <?php echo BASE_HTTP_PATH;?>images/grgrad.gif) repeat-x; ">
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000; "></div>
<b>[Ads Template] </b><span style="background-color: <?php if (($_REQUEST['mode']!='edit')) { echo "#FFFFff"; }  ?>; border-style:outset; padding: 5px;"><a href="adform.php?mode=view">View Form</a></span> <span style="background-color:  <?php if (($_REQUEST['mode']=='edit') && ($_REQUEST['NEW_FIELD']=='')) { echo "#FFFFCC"; }  ?>; border-style:outset; padding: 5px;"><a href="adform.php?mode=edit">Edit Fields</a></span> <span style="background-color: <?php if (($_REQUEST['mode']=='edit') && ($_REQUEST['NEW_FIELD']!='')) { echo "#FFFFCC"; }  ?>; border-style:outset; padding: 5px;"><a href="adform.php?NEW_FIELD=YES&mode=edit">New Field</a></span>&nbsp; &nbsp; <span style="background-color: <?php  echo "#ffffcc";?> ; border-style:outset; padding: 5px;"><a href="adtemplate.php">Edit Template</a></span> <span style="background-color: <?php  echo "#F2F2F2";?> ; border-style:outset; padding: 5px;"><a href="adslist.php">Ad List</a></span>
	
	<hr>
	Here you can edit the template for the ads. The ads are displayed when a mouse is moved over the pixels. <b>You will need to edit this template after inserting or removing a field on the Ad Form.</b><p>The rules are simple... if you want <b>to display to the value of a field, put two % signs around the field's template tag</b>, like this %TEMPLATE_TAG%. If you want <b>to display the field's label, put two $ signs around the field's template tag</b>, like this $TEMPLATE_TAG$.  Use normal HTML to format the ad.</p>

<hr>

<?php



global $AVAILABLE_LANGS;
	echo "Current Language: [".$_SESSION['MDS_LANG']."] Select language:";

?>

<form name="lang_form">
<input type="hidden" name="field_id" value="<?php echo $field_id; ?>"/>
<input type="hidden" name="mode" value="<?php echo $mode; ?>"/>
<select name='lang' onChange="document.lang_form.submit()">
<?php
	foreach  ($AVAILABLE_LANGS as $key => $val) {
		$sel = '';
		if ($key==$_SESSION['MDS_LANG']) { $sel = " selected ";}
		echo "<option $sel value='".$key."'>".$val."</option>";

}




?>

</select>
</form>
<?php


$lang_filename = $LANG_FILES[$_SESSION['MDS_LANG']];
if (!is_writable("../lang/$lang_filename")) {
	echo "../lang/$lang_filename is not writeable. Please give permission for writing to this file before editing the template.<br>";
}

if (($_REQUEST['save'])) {

	// save the file.

	
	include ("../lang/english_default.php");
	$source_label = $label; // default english labels
	include ("../lang/".$lang_filename);
	$dest_label = $label; // dest labels

	$out = "<?php\n";
	foreach ($source_label as $key=>$val) {
		//$source_label[$key] = addslashes($dest_label[$key]);
		if ($key=='mouseover_ad_template') {
			$dest_label[$key] = stripslashes($_REQUEST['mouseover_ad_template']);
		}
		$source_label[$key] = str_replace("'", "\'",$dest_label[$key] ); // slash it
		$out .= "\$label['$key']='". $source_label[$key]."'; \n";
	}
	$out .= "?>\n"; 

	//echo $out;

	$handler = fopen ("../lang/".$lang_filename, "w");
	fputs ($handler, $out);
	fclose ($handler);

}

if ($_REQUEST['mouseover_ad_template']=='') {
	$_REQUEST['mouseover_ad_template'] = $label['mouseover_ad_template'];
}

?>
<form method="POST" action="adtemplate.php">

<textarea name='mouseover_ad_template' rows=10 cols=50><?php echo escape_html(stripslashes($_REQUEST['mouseover_ad_template'])); ?></textarea><br>
<input type="submit" name='save' value="Save">
</form>

<hr>
<p>Template Preview:</p>

<?php

foreach ($ad_tag_to_field_id as $field) {
	$prams[$field['field_id']] = 'example_value';
	$prams[$field['field_label']] = 'example_label';
}

//print_r($ad_tag_to_field_id);

echo assign_ad_template($prams);

?>