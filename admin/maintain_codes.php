<?php
/**
 * @version		$Id: maintain_codes.php 88 2010-10-12 16:43:19Z ryan $
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
ini_set('max_execution_time', 10000);
require ('../include/code_functions.php');
require ('../config.php');
require ("admin_common.php");
$field_id = $_REQUEST['field_id'];
$code = $_REQUEST['code'];
$description = $_REQUEST['description'];
$modify = $_REQUEST['modify'];

if (!$_REQUEST['field_id']) {
  echo "Select the code group that you would like to edit:<p>";
  echo "Ad Form:";
  list_code_groups (1);
  
  die ();

} 

function can_delete_code ($field_id, $code) {

	$sql = "SHOW TABLES";
	$tables = mysql_query ($sql);

	$tables = array ('ads');
	foreach ($tables as $table ) {

		$sql = "SHOW COLUMNS FROM ".$table;
		$cols = mysql_query ($sql);

		while ($c_row = mysql_fetch_row($cols)) {
			if ($c_row[0] == $field_id) {

				$sql = "SELECT * FROM ".$table." WHERE `$field_id` like '%$code%' ";
				$result = mysql_query ($sql);
				if (mysql_num_rows($result)==0) {
					
					return true;
				} else {
					//echo $sql;
					return false;
				}


			}
		}


	}


}


?>
<?php echo $f2->get_doc(); ?>

<style>
body {
	background: #fff  url(<?php echo BASE_HTTP_PATH;?>images/grgrad.gif) repeat-x;
	font-family: 'Arial', sans-serif; 
	font-size:10pt;

}
</style>
</head>

<body>
<?php
//print_r($_REQUEST);
if ($_REQUEST['action'] == 'delete' ) {

	$field_id = $_REQUEST['field_id'];
	$code = $_REQUEST['code'];

	$sql = "DELETE from `codes` where `field_id`=$field_id AND `code`='$code' ";
	mysql_query ($sql) or die (mysql_error());

	$sql = "DELETE from `codes_translations` where `field_id`=$field_id AND `code`='$code' ";
	mysql_query ($sql) or die (mysql_error());

	//echo $sql;


}

if ($_REQUEST['do_change']!='') {

	echo 'Changing id...';

	///$new_description = 'ok';

//	print_r($_REQUEST);

	$error = validate_code ($_REQUEST['field_id'], $_REQUEST['new_code_id'], 'ok');


	if ($error =='') {
		change_code_id ($_REQUEST['field_id'], $_REQUEST['code'], $_REQUEST['new_code_id']);
		echo 'ok<br>';

	} else {

		?>

		<b><font color="#ff0000">ERROR:</font> Cannot save new code because:</b><br>
		  <?php
		   echo $error."<br>";

	

		
		$_REQUEST['action'] ='change';


	}


}

if ($_REQUEST['action'] == 'change' ) {

?>

You can change the code id. Since the options are sorted by code id, you can get the option list to sort in a particular order by changing the code id. <b>Note: changing the code id can be a large / risky operation if you have many records in the database. This is because the script needs to update each record individually. Please be patient and do not close this window until the process is complete.</b>

<form method='post' action='maintain_codes.php'>

<input type="hidden" name="field_id" value="<?php echo htmlentities($field_id); ?>">
<input type="hidden" name="code" value="<?php echo htmlentities($code); ?>"/>
Current code id: <?php echo $code; ?><br>
Enter new code id: <input type="text" name="new_code_id" value=""><br>
<input type='submit' value='Change' name='do_change'>
</form>
<hr>
<?php

}

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




#echo "Field:[".$field_id."] Code[".$code."] Descr[".$description."]";
#echo "New Code[".$new_code."] new Descr[".$new_description."]";
?>

<form method="POST" action="<?php htmlentities($_SERVER['PHP_SELF']);?>">


<p>
<table border="1">
<tr>
<td><b>Code</b></td>
<td><b>Description</b></td>
<td></td>
</tr>
<?php


if ($modify == "yes") {
	
	
   modify_code($field_id, $code, $description);
   $code = '';
    ?>


   <?php
}

function validate_code ($field_id, $new_code, $new_description) {
	if ($new_code == '') {
		$error .= "- Code is blank<br>";
	} 
	if ($new_description== '') {
		$error .= "- Description is blank<br>";
	}

	if ($new_code != '') {

		$sql = "SELECT * from codes where field_id=$field_id AND code like '%$new_code%' ";
		$result = mysql_query ($sql) or die (mysql_error());

		if (mysql_num_rows($result)>0) {
			$error .= "- The new Code is too similar to an already existing code. Please try to come up with a different code.<br>";
		}


	}
	
	

	return $error;


}

if ($_REQUEST['new_code'] != '' ) {

	$error = validate_code ($field_id, $_REQUEST['new_code'], $_REQUEST['new_description']);
	if ($error == '') {
	   insert_code($field_id, $_REQUEST['new_code'], $_REQUEST['new_description']);
	   $_REQUEST['new_code']='';
	   $_REQUEST['new_description']='';

	   ?>


	   <?php
	} else {
		   ?>
<b><font color="#ff0000">ERROR:</font> Cannot save new code because:</b><br>
		   <?php
		   echo $error;
	}

}

if ($_SESSION['MDS_LANG'] == '' ) {
	$sql = "SELECT `code`, `description` FROM `codes` WHERE field_id='".$field_id."'";
} else {
	$sql = "SELECT `code`, `description` FROM `codes_translations` WHERE field_id='".$field_id."' AND `lang`='".$_SESSION['MDS_LANG']."' ";

}



$result = mysql_query ($sql) or die($sql.mysql_error());

while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

   if ($code == $row['code']) {
      echo '<tr bgcolor="FFFFCC">'."\n";
   }
   else {
      echo "<tr>\n";
   }

   echo "<td>\n";
   echo '<A Href="'.htmlentities($_SERVER['PHP_SELF']).'?field_id='.$field_id.'&code='.$row['code'].'">'."\n";
   echo $row['code'];
   echo '</a>'."\n";
   echo "</td>\n";
   echo "<td>\n";
   if ($code == $row['code']) {
      echo '<input name="description" type="text" size="30" value="'.$row['description'].'">';
      echo '<input name="modify" type="hidden" value="yes">';
      echo '<input name="code" type="hidden" value="'.$row['code'].'">';
      echo '<input name="field_id" type="hidden" value="'.$field_id.'">';
   }
   else {
      echo $row['description'];
   }
   echo "</td>\n";
   echo "<td>\n";
   $disabled = ""; $n = "";
   if (!can_delete_code ($field_id, $row['code'])) {
	   $disabled = " disabled ";
	   $n = "*";
   }
   echo '<input type="button" onclick="window.location=\''.htmlentities($_SERVER['PHP_SELF']).'?action=delete&field_id='.$field_id.'&code='.urlencode($row['code']).'\'" name="" value="Delete" '.$disabled.' >'.$n;
   echo '&nbsp;<input type="button" onclick="window.location=\''.htmlentities($_SERVER['PHP_SELF']).'?action=change&field_id='.$field_id.'&code='.urlencode($row['code']).'\'" name="" value="Change id" >';
   echo "</td>\n";
   
   echo "</tr>\n";

}
?>
<tr>
<td><input name="new_code" type="text" size="4" value="<?php echo $_REQUEST['new_code']; ?>" ></td>
<td><input name="new_description" type="text" value="<?php echo $_REQUEST['new_description']; ?>" size="30">
<?php
if ($field_id != '') {
   echo '<input name="field_id" type="hidden" value="'.$field_id.'">';
}
?>

</td>
<td>&lt;-new code</td>
<tr>
<tr>
<td colspan="2">
<input type="submit" name="save" value="Save">
</td>
</tr>

</table>
</form>

<?php


?>



<center><input type="button" name="" value="Close" onclick="window.opener.location.reload();window.close()"></center>

<i>* = Cannot delete because Code is in use by a record. Delete / alter the record(s) before deleting the Code.</i>
</body>

</html>

