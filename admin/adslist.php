<?php
/**
 * @version		$Id: adslist.php 126 2011-02-10 03:29:31Z ryan $
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
require_once ('../include/category.inc.php');
require_once ('../include/ads.inc.php');
require_once ('../include/dynamic_forms.php');
error_reporting(E_ALL & ~E_NOTICE);

$mode = $_REQUEST['mode'];


?>
<?php echo $f2->get_doc(); ?>

<link rel="stylesheet" type="text/css" href="../main.css" >

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

<BODY style=" font-family: 'Arial', sans-serif; font-size:10pt; background: #fff  url( <?php echo BASE_HTTP_PATH;?>images/grgrad.gif) repeat-x; ">
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000; "></div>
<b>[Ads List]</b><span style="background-color: <?php if (($_REQUEST['mode']!='edit')) { echo "#F2F2F2"; }  ?>; border-style:outset; padding: 5px;"><a href="adform.php?mode=view">View Form</a></span> <span style="background-color:  <?php if (($_REQUEST['mode']=='edit') && ($_REQUEST['NEW_FIELD']=='')) { echo "#FFFFCC"; }  ?>; border-style:outset; padding: 5px;"><a href="adform.php?mode=edit">Edit Fields</a></span> <span style="background-color: <?php if (($_REQUEST['mode']=='edit') && ($_REQUEST['NEW_FIELD']!='')) { echo "#FFFFCC"; }  else { echo "#F2F2F2";}?> ; border-style:outset; padding: 5px;"><a href="adform.php?NEW_FIELD=YES&mode=edit">New Field</a></span> &nbsp; &nbsp; <span style="background-color: <?php  echo "#FFFFCC";?> ; border-style:outset; padding: 5px;"><a href="adslist.php">Ads List</a></span>
	
	<hr>

<?php




if ($_REQUEST['action']=='del') {

	$sql = "DELETE FROM form_lists WHERE column_id='".$_REQUEST['column_id']."' ";
	$result = mysql_query ($sql);


}

if ($_REQUEST['column_id']!='') {
	$sql = "SELECT * FROM form_lists WHERE column_id='".$_REQUEST['column_id']."' ";
	$result = mysql_query ($sql);
	$col_row = mysql_fetch_array($result);

}


if ($_REQUEST['save_col']!='') {

	if ($_REQUEST['field_id']=='') {
		$error = "Did not select a field ";
	}

	if (!is_numeric($_REQUEST['sort_order'])) {
		$error .= "'Sort order' must be a number. <br>";
	}

	if (!is_numeric($_REQUEST['truncate_length'])) {
		$error .= "'Truncate' must be a number. <br>";
	}



	if (is_numeric($_REQUEST['field_id'])) {

		$sql = "SELECT * from form_fields WHERE form_id=1 AND field_id='".$_REQUEST['field_id']."'  ";
		$result = mysql_query ($sql);
		$field_row = mysql_fetch_array($result);

	} else {

		$field_row['field_type']='TEXT'; // default storage type.
		$field_row['field_id'] = $_REQUEST['field_id'];

		switch ($_REQUEST['field_id']) {

			case 'ad_date':
				$field_row['template_tag']='DATE';
				$field_row['field_type']='TIME';
				break;
			case 'ad_id':
				$field_row['template_tag']='AD_ID';
				break;
			case 'user_id':
				$field_row['template_tag']='USER_ID';
				break;
			case 'order_id':
				$field_row['template_tag']='ORDER_ID';
				break;
			case 'banner_id':
				$field_row['template_tag']='BID';
				break;
			

		}


	}

	if ($field_row['template_tag']=='') { // need to fix the template tag!

		$field_row['template_tag'] = generate_template_tag(1);

		// update form field

		$sql = "UPDATE form_fields SET `template_tag`='".$field_row['template_tag']."' WHERE form_id=1 AND field_id='".$_REQUEST['field_id']."'";
		mysql_query ($sql);

	}

	if ($_REQUEST['admin_only']=='') {
		$_REQUEST['admin_only']='N';
	}

	if ($_REQUEST['linked']=='') {
		$_REQUEST['linked']='N';
	}


	$sql = "REPLACE INTO form_lists (`column_id`, `template_tag`, `field_id`, `sort_order`, `field_type`, `form_id`, `admin`, `truncate_length`, `linked`, `clean_format`, `is_bold`, `no_wrap`, `is_sortable`) VALUES ('".$_REQUEST['column_id']."', '".$field_row['template_tag']."', '".$field_row['field_id']."', '".$_REQUEST['sort_order']."', '".$field_row['field_type']."', '1', '".$_REQUEST['admin_only']."', '".$_REQUEST['truncate_length']."', '".$_REQUEST['linked']."',  '".$_REQUEST['clean_format']."', '".$_REQUEST['is_bold']."', '".$_REQUEST['no_wrap']."', '".$_REQUEST['is_sortable']."')";

	
	if ($error=='') {
		$result = mysql_query ($sql) or die (mysql_error().$sql);
		//echo $sql;
		if ((CACHE_ENABLED=='YES')) {
			$CACHE_ENABLED = 'NO';
			include ('../include/codegen_functions.php');
			generate_form_cache(1);
	
			$CACHE_ENABLED='YES';
		}
		echo "Column Updated.<br>";
	} else {
		echo "<font color='red'>Cannot save due to the following errors:</font><br>";
		echo $error;

	}

	// load new values

	$sql = "SELECT * FROM form_lists WHERE column_id='".$_REQUEST['column_id']."' ";
	$result = mysql_query ($sql);
	$col_row = mysql_fetch_array($result);

}

?>
<?php
if ($col_row['column_id']!='') {

echo '<a href="adslist.php">+ Add new column</a>';

}

?>
	<form method="POST" action="adslist.php">

	<input type="hidden" name="form_id" value="1">
	<input type="hidden" name="column_id" value="<?php echo $col_row['column_id'];?>">
	<table border=1>
	<tr>
	<td colspan="2">
	<?php
if ($col_row['column_id']=='') {
	?>
<b>Add a new column to the list</b>
<?php
	} else {
?>
<b>Edit column</b>

<?php

	}

?>
	</td>
	</tr>
	<tr>
		<td>Column</td>
		<td><select name="field_id" size=4>

		
		
		<?php
		
		field_type_option_list (1, $col_row['field_id']);
		
		?>
			</select></td>
	</tr>

	<?php

	if ($_REQUEST['column_id']=='') { // get the last sort order

		$sql = "SELECT max(sort_order) FROM form_lists WHERE field_id=1 GROUP BY column_id ";
		$result = mysql_query ($sql) or die (mysql_error().$sql);
		$row = mysql_fetch_row($result);
		$sort_order = $row[0];

	}


	?>

	<tr>
		<td>Order</td>
		<td><input type="text" name="sort_order" size="1" value="<?php echo $col_row['sort_order'];?>" >(1=first, 2=2nd, etc.)</td>
	</tr>
	<tr>
		<td>Linked?</td>
		<td> <input <?php if ($col_row['linked']!='Y') echo ' checked '; ?> type="radio" name="linked" value='N'>No  / <input <?php if ($col_row['linked']=='Y') echo ' checked '; ?> type="radio" name="linked" value='Y'> Yes - link to view full record

	</tr>
	<tr>
		<td>Admin Only?</td>
		<td> <input <?php if ($col_row['admin']!='Y') echo ' checked '; ?> type="radio" name="admin_only" value='N'>No  / <input <?php if ($col_row['admin']=='Y') echo ' checked '; ?> type="radio" name="admin_only" value='Y'> Yes

	</tr>
	<tr>
		<td>Clean format?</td>
		<td> <input <?php if ($col_row['clean_format']!='Y') echo ' checked '; ?> type="radio" name="clean_format" value='N'>No  / <input <?php if ($col_row['clean_format']=='Y') echo ' checked '; ?> type="radio" name="clean_format" value='Y'>  Yes - Clean punctuation. Eg. if someone writes A,B,C the system will change to A, B, C

	</tr>
	<tr>
		<td>Is sortable?</td>
		<td> <input <?php if ($col_row['is_sortable']!='Y') echo ' checked '; ?> type="radio" name="is_sortable" value='N'>No  / <input <?php if ($col_row['is_sortable']=='Y') echo ' checked '; ?> type="radio" name="is_sortable" value='Y'> Yes - users can sort the records by this coulum, when clicked.

	</tr>
	<tr>
		<td>Is in Bold?</td>
		<td> <input <?php if ($col_row['is_bold']!='Y') echo ' checked '; ?> type="radio" name="is_bold" value='N'>No  / <input <?php if ($col_row['is_bold']=='Y') echo ' checked '; ?> type="radio" name="is_bold" value='Y'> Yes

	</tr>
	<tr>
		<td>No Wrap?</td>
		<td> <input <?php if ($col_row['no_wrap']!='Y') echo ' checked '; ?> type="radio" name="no_wrap" value='N'>No  / <input <?php if ($col_row['no_wrap']=='Y') echo ' checked '; ?> type="radio" name="no_wrap" value='Y'> Yes

	</tr>
	<tr>
		<td>Truncate (cut) to:</td>
		<td> <input type="text" name="truncate_length" size="2" value='<?php if ($col_row['truncate_length']=='') {$col_row['truncate_length']='0';} echo $col_row['truncate_length'];?>' size=''> characters. (0 = do not truncate)

	</tr>
	<tr>
	<td colspan="2"><input type="submit" name="save_col" value="Save"> </td>
	</tr>


	</table>


	</form>

<hr>
Here are the columns that will appear on the ad list:
<table border='0' width="99%"  id='resumelist' cellspacing="1" cellpadding="5" align="center">
	<?php
global $tag_to_field_id;
	//$tag_to_field_id = ad_tag_to_field_id_init();
	echo_list_head_data_admin(1);

	?>
</table>

