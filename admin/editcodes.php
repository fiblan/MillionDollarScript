<?php
session_start();
require ('../config.php');
require ('../include/code_functions.php');

require ("admin_common.php");
/*
COPYRIGHT 2008 - see www.milliondollarscript.com for a list of authors

This file is part of the Million Dollar Script.

Million Dollar Script is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Million Dollar Script is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with the Million Dollar Script.  If not, see <http://www.gnu.org/licenses/>.

*/
?>
<html>

<head>

<style>
body {
	background: #fff  url(<?php echo BASE_HTTP_PATH;?>images/grgrad.gif) repeat-x;
	font-family: 'Arial', sans-serif; 
	font-size:10pt;

}


</style>

</head>
<body>
<b>[Configuration]</b>
	<span style="background-color: #F2F2F2; border-style:outset; padding: 5px;"><a href="edit_config.php">Main</a></span>
 <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="editcats.php">Categories</a></span>
 <span style="background-color: #FFFFCC; border-style:outset; padding:5px; "><a href="editcodes.php">Codes</a></span>
 <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="language.php">Languages</a></span>
 <span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="emailconfig.php">Email Templates</a></span>	
<!--span style="background-color: #F2F2F2; border-style:outset; padding:5px; "><a href="filter.php">Filter</a></span-->	
	
<hr>

<?php




function list_code_groups ($form_id) {

	$sql = "select * FROM `form_fields` WHERE form_id='$form_id' AND (field_type='CHECK' OR field_type='RADIO' OR field_type='SELECT' OR field_type='MSELECT' ) ";
	$result = mysql_query ($sql) or die (mysql_error());
	//echo $sql;
	if (mysql_num_rows($result)==0) {
		echo " (0 codes)";
	}
	echo "<ul>";
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

		format_codes_translation_table ($row[field_id]);
?>
		<li><a href="" onclick="window.open('maintain_codes.php?field_id=<?php echo $row[field_id];?>', '', 'toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=no,resizable=1,width=400,height=500,left = 150,top = 150');return false;" ><?php echo $row[field_label]; ?></a>
<?php
	}

	echo "</ul>";


}

if (!$_REQUEST['field_id']) {
  echo "Select the code group that you would like to edit:<p>";
  echo "<b>Ad Form:</b>";
  list_code_groups (1);
 
  die ();

}

?>
</body>