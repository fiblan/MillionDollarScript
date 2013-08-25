<?php
/**
 * @version		$Id: code_functions.php 91 2011-01-03 22:47:15Z ryan $
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

function format_codes_translation_table ($field_id) {
	global $AVAILABLE_LANGS;

	$sql = "SELECT * FROM codes WHERE `field_id`=$field_id ";
	//echo $sql;
	$f_result = mysql_query ($sql) or die ($sql.mysql_error());
	while ($f_row = mysql_fetch_array($f_result)) { 

		foreach  ($AVAILABLE_LANGS as $key => $val) {

			$sql = "SELECT t2.code, t2.field_id, t2.description AS FLABEL, lang FROM codes_translations as t1, codes as t2 WHERE t2.code=t1.code AND t2.code='".$f_row[code]."' AND t2.field_id=".$f_row['field_id']." AND lang='$key' ";
			//echo $sql."<br>";
			$result = mysql_query($sql) or die($sql.mysql_error());
			//$row = mysql_fetch_row($result);
			if (mysql_num_rows($result)==0) {
				//$cat_row = get_category($cat);
				$sql = "REPLACE INTO `codes_translations` (`field_id`, `code`, `lang`, `description`) VALUES ('".$f_row['field_id']."', '".$f_row[code]."', '".$key."', '".addslashes($f_row[description])."')";
				//echo "<b>$sql</b>";
				mysql_query($sql) or die (mysql_error());

			}

		}

	}

}

#################################################
# Changes the code id, and updates *all* the records in the database
# with the given field id with the new code_id
function change_code_id ($field_id, $code, $new_code) {

	// find which form the field_id is from

	$sql = "SELECT form_id FROM form_fields where field_id='".$field_id."' ";
	$result = mysql_query($sql) or die(mysql_error().$sql);
	$row = mysql_fetch_array($result);
	$form_id = $row['form_id'];

	$sql = "UPDATE codes SET code='$new_code' where field_id='$field_id' and code='$code' ";
	$result = mysql_query($sql) or die(mysql_error().$sql);
	//echo "$sql<br>";

	$sql = "UPDATE codes_translations SET code='$new_code' where field_id='$field_id' and code='$code' ";
	$result = mysql_query($sql) or die(mysql_error().$sql);
	//echo "$sql<br>";

	switch ($form_id) {

		case '1': // ads form
			$table = 'ads';  $id='ad_id';
			$sql = "select ad_id as ID, `$field_id` FROM ads WHERE `$field_id` LIKE '%$code%' ";
			break;
		

	}

	$result = mysql_query($sql) or die(mysql_error().$sql);
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

		$new_codes = array();
		$codes = explode(',',$row[$field_id]);

//echo "<p>";
		//print_r($codes);
//echo "</p>";
		foreach ($codes as $c) {

			if ($c == $code) {
				//echo "$c replace with new code!$new_code<br>";
				$new_codes[] = $new_code;
			} else {
				//echo "$c<br>";
				$new_codes[] = $c;

			}

		}

		$codes = implode(',', $new_codes);

		$sql = "UPDATE $table SET `$field_id`='".$codes."' WHERE $id = '".$row['ID']."' ";
		mysql_query($sql) or die(mysql_error().$sql);
		//echo $sql."<br>";

	}




}

######################################################################

function getCodeDescription ($field_id, $code) {

	if ($_SESSION['MDS_LANG'] != '') {

		$sql = "SELECT `description` FROM `codes_translations` WHERE field_id='$field_id' AND `code` = '$code' and lang='".$_SESSION['MDS_LANG']."' ";

	} else {
		
		$sql = "SELECT `description` FROM `codes` WHERE field_id='$field_id' AND `code` = '$code'";
	}
   
   global $f2;
   $result = mysql_query($sql) or die($sql.mysql_error());
   if ($row = mysql_fetch_array($result)) {
       return $row[description];
   }

}

###################################################

function insert_code ($field_id, $code, $description) {

   $sql = "SELECT `code` FROM `codes` WHERE field_id='$field_id' AND `code` = '$code'";
   $result = mysql_query($sql) or die($sql.mysql_error());

   if (mysql_num_rows($result) > 0 ) {
      echo '<font color="#FF0000">';
      echo "CANNOT INSERT a new Code: $code already exists in the database!<p>";
      echo '</font>';
      return;

   }

   $sql = "INSERT INTO `codes` ( `field_id` , `code` , `description` )  VALUES ('$field_id', '$code', '$description')";

    mysql_query($sql) or die($sql.mysql_error());

   if ($_SESSION['MDS_LANG'] != '') {

		$sql = "INSERT INTO `codes_translations` ( `field_id` , `code` , `description`, `lang` )  VALUES ('$field_id', '$code', '$description', '".$SESSION['lang']."')";
		mysql_query($sql) or die($sql.mysql_error());

   }

   format_codes_translation_table ($field_id);

  

}
################################################################
function modify_code ($field_id, $code, $description) {
   $sql = "UPDATE `codes` SET `description` = '$description' ".
          "WHERE `field_id` = '$field_id' AND `code` = '$code'";
   mysql_query($sql) or die($sql.mysql_error());

   if ($_SESSION['MDS_LANG'] != '') {

		$sql = "UPDATE `codes_translations` SET `description` = '$description' ".
          "WHERE `field_id` = '$field_id' AND `code` = '$code' AND `lang`='".$_SESSION['MDS_LANG']."' ";
		mysql_query($sql) or die($sql.mysql_error());

   }

   

}
#####################################################
/*
   This is the reverse of function getCodeDescription();
*/
function getCodeFromDescription ($field_id, $description) {
   $sql = "SELECT `code` FROM `codes` WHERE field_id='$field_id' AND `description` = '$description'";
   //echo "$sql <br>";
   $result = mysql_query($sql) or die($sql.mysql_error());
   if ($row = mysql_fetch_array($result)) {
       return $row[code];
   }

}

?>