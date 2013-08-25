<?php
/**
 * @version		$Id: editcats.php 88 2010-10-12 16:43:19Z ryan $
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
require_once ('../config.php');
require ("admin_common.php");
require_once ('../include/category.inc.php');

if ($_REQUEST[form_id] != '') {
	$_SESSION[form_id] = $_REQUEST[form_id];
}
if ($_SESSION[form_id]=='') {
	$_SESSION[form_id] =1;
}
$add = $_REQUEST['add'];

$action = $_REQUEST['action'];
$edit = $_REQUEST['edit'];
$category_id = $_REQUEST['category_id'];
$new_name = $_REQUEST['new_name'];
$allow_records = $_REQUEST['allow_records'];
?>
<?php echo $f2->get_doc(); ?>

<title>Edit Categories</title>

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
<b>[Configuration]</b>
	<span style="background-color: #F2F2F2; border-style:outset; padding: 5px;"><a href="edit_config.php">Main</a></span>
 <span style="background-color: #FFFFCC; border-style:outset; padding:5px; "><a href="editcats.php">Categories</a></span>
 <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="editcodes.php">Codes</a></span>
 <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="language.php">Languages</a></span>
 <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="emailconfig.php">Email Templates</a></span>	
<!--span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="filter.php">Filter</a></span-->	
	
<hr>
<h3>- Select which categories to edit:</h3>
<span style="background-color: <?php if ($_SESSION[form_id]==1) echo "#FFFFCC"; else echo "#F2F2F2"; ?>; border-style:outset; padding:5px; "><a href="editcats.php?form_id=1">Ad Categories</a></span>

   <hr>
<?php
	global $AVAILABLE_LANGS;
	echo "Current Language: [".$_SESSION['MDS_LANG']."] Select language:";

?>
<form name="lang_form">
<input type="hidden" name="cat" value="<?php echo $_REQUEST['cat']; ?>"/>
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
<div >
<hr>
<input type="button" name="process" value="Process Categories" onclick="window.location='<? echo htmlentities($_SERVER['PHP_SELF']); ?>?action=process'">
<?php

//echo 'hello:';
//echo get_search_set(20, '20');



if ($_REQUEST['cat']=='') {
   $_REQUEST['cat']=0;
}
$new_cat = $_REQUEST['new_cat'];
if ($new_cat != '') {
  
   if (strlen($new_cat)>0) {
	   if ($_REQUEST['allow_records']=='ON') {
			$allow_records='Y';
	   } else {
			$allow_records='N';
	   }
      add_cat($new_cat, $_REQUEST['cat'], $_SESSION[form_id], $allow_records);
      echo "$new_cat Category added.";
	  format_cat_translation_table (0);
	  if (($_REQUEST['save']!='') && (CACHE_ENABLED=='YES')) {
		  $CACHE_ENABLED = 'NO';
		  include ('../include/codegen_functions.php');
		  update_category_cache(0);
		  $CACHE_ENABLED='YES';
		}
   } 
   else {
      echo "category name was left blank. Please retry.<br>";
   }   
   
}

if ($action=='edit') {

	//if ($_SESSION['MDS_LANG'] == "EN") {

	if ($_REQUEST['allow_records']=='ON') {
		$allow_records='Y';
	} else {
		$allow_records='N';
	}

	$sql = "update categories set allow_records='$allow_records', list_order='".$_REQUEST['list_order']."' Where category_id='$category_id' ";
	$result = mysql_query($sql) or die (mysql_error());
		
	// update language

	$sql = "REPLACE INTO `cat_name_translations` (`category_id`, `lang`, `category_name`) VALUES (".$category_id.", '".$_SESSION['MDS_LANG']."', '".$new_name."')";
	$result = mysql_query($sql) or die (mysql_error());

	if (($_REQUEST['save']!='') && (CACHE_ENABLED=='YES')) {
		  $CACHE_ENABLED = 'NO';
		  include ('../include/codegen_functions.php');
		  update_category_cache(0);
		  $CACHE_ENABLED='YES';
		}

	echo "Updated to <b>$new_name</b><br>";


}

if ($action == 'del') {

	$_REQUEST['cat'] = getCatParent($_REQUEST['category_id']); // so that we come back to parent..
	
	if (($obj_count = del_cat_recursive ($_REQUEST['category_id'])) < 0) {
		$obj_count = -$obj_count;
		echo "<br><font color='red'><b>Error:</b></font> Cannot delete this category: It looks like you have ".$obj_count." record(s) in this category! <a href='".$_SERVER['PHP_SELF']."?action=del&category_id=".$_REQUEST['category_id']."&confirm=yes'>Click Here to delete anyway.</a></br>";
	}

	if (($_REQUEST['save']!='') && (CACHE_ENABLED=='YES')) {
		  $CACHE_ENABLED = 'NO';
		  include ('../include/codegen_functions.php');
		  update_category_cache(0);
		  $CACHE_ENABLED='YES';
		}	
	
}

if ($_REQUEST['action']=='process') {

	format_cat_translation_table (0);
	if (($_REQUEST['save']!='') && (CACHE_ENABLED=='YES')) {
		  $CACHE_ENABLED = 'NO';
		  include ('../include/codegen_functions.php');
		  update_category_cache(0);
		  $CACHE_ENABLED='YES';
		}

}


echo "<div align='left'><h3><a href=".htmlentities($_SERVER['PHP_SELF']).">Root Category</a> -&gt; ".getPath($_REQUEST['cat'], $_SERVER['PHP_SELF'], $_SESSION[form_id])." ";

if ($_REQUEST['cat'] != 0) {
	$MODE="ADMIN";
?>
<a onClick="return confirmLink(this, 'Delete this category, are you sure? (This will also delete all sub-categories in this category)') " href="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?action=del&category_id=<?php echo $_REQUEST['cat']?>"><IMG src='delete.gif' width='16' height='16' border='0' alt='Delete'></a> 
<a href="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?edit=<?php echo $_REQUEST['cat'];?>&cat=<?php echo $_REQUEST['cat']; ?>">
   <IMG alt="edit" src="edit.gif" width="16" height="16" border="0" alt="Edit">
   </a>
<?php
}
?>
</h3>
</div>

<table cellspacing="1" border="1"  align="left" width="100%">

<?php


if ($edit == '') {
   //echo "<tr>";
   add_new_cat_form($_REQUEST['cat']);
   //echo"</tr>";
   echo "<hr>";

}
if ($edit != '') {

	$row = get_category($edit);

	?>

	<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?action=edit" method="post">
	<table >
	<tr><td>
	<font size="2">Edit Category Name:</font></td><td><input type="text" name="new_name" size="35" value="<?php echo $row['NAME']; ?>"/></td></tr>
	<tr><td></td><td>
	<input type="checkbox" value="ON" name="allow_records" id="id01" <?php if ($row['allow_records']=='Y') {echo " checked ";} ?>> <label for="id01"><font size="2">Allow records to be added to this category.</font></label>
	<br>
	<font size="2">List order <input type='text' name='list_order' size="2" value="<?php echo $row['list_order']; ?>" > (optional: enter an ordinal number to list in special order. 1=first)</font>
	</td>
	</tr>
	<tr><td colspan=2>
	<input type="hidden" name="category_id" value="<?php echo $row['category_id']; ?>">
	<input type="hidden" name="cat"  value="<?php echo $row['parent_category_id']; ?>">
	
	<input type="hidden" name="action" value="edit">
	<input type="submit" value="Save">
	</td>
	</tr>
	</table>
	</form>
	<hr>
	<p>&nbsp</p>

	<?php

}

//if ($new_cat == '') {
	$MODE = "ADMIN";
   showAllCat($_REQUEST['cat'], 1, 3,  $_SESSION['MDS_LANG'], $_SESSION[form_id]);

   
   if ($_SESSION[form_id] == 1) {
	   require_once ('../include/ads.inc.php');
	   build_ad_count ($_REQUEST['cat']);
	  // echo "cats rebuilt.";
   }



//}
?>
</table>
<?php

?>

</div>

</body>
</html>
