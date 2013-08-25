<?php
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
//if(!empty($_GET)) extract($_GET);
//if(!empty($_POST)) extract($_POST);

if ($_REQUEST["lang"]!='') {
	$sql = "SELECT * FROM lang WHERE `lang_code`='".$_REQUEST['lang']."'";
	$result = mysql_query($sql) or die (mysql_error());
	if (mysql_num_rows($result)>0) {
		$_SESSION['MDS_LANG'] = $_REQUEST["lang"];
		// save the requested language
		@setcookie("MDS_SAVED_LANG", $_REQUEST["lang"], 2147483647);
		
	} else {

		$sql = "SELECT * FROM lang WHERE `is_default`='Y'";
		$result = mysql_query($sql) or die (mysql_error());
		$row = mysql_fetch_array($result, MYSQL_ASSOC);
		$_SESSION['MDS_LANG'] = $row["lang_code"];
		// save the requested language
		@setcookie("MDS_SAVED_LANG", $row["lang_code"], 2147483647);
		echo "Invalid language. Reverting to default language.";
	}
}

elseif ($_SESSION['MDS_LANG']=='') {
	// get the default language, or saved language

	if ($_COOKIE['MDS_SAVED_LANG']!='') {
		$_SESSION['MDS_LANG'] = $_COOKIE['MDS_SAVED_LANG'];

	} else {

		$sql = "SELECT * FROM lang WHERE `is_default`='Y' ";
		if ($result = mysql_query ($sql)) {
			$row = mysql_fetch_array($result, MYSQL_ASSOC);
			$_SESSION['MDS_LANG'] = $row['lang_code'];
			if ($row['charset']!='') {
				setlocale(LC_TIME, $row['charset']);
			}
		}
	}
	
}

global $AVAILABLE_LANGS;
global $LANG_FILES;

// load languages into array.. map the language code to the filename
// if mapping didn't work, default to english..

$sql = "SELECT * FROM lang ";
if ($result = mysql_query ($sql)) {
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$AVAILABLE_LANGS [$row['lang_code']] = $row['name'];
		$LANG_FILES [$row['lang_code']] = $row['lang_filename'];
	}
	
	if (($_SESSION['MDS_LANG'] != '') ) {
		include dirname(__FILE__)."/".$LANG_FILES[$_SESSION['MDS_LANG']];
		
	} else {
		include dirname(__FILE__)."/english.php";
	}

} else {
	$DB_ERROR = mysql_error();

}



?>