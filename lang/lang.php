<?php
/**
 * @version		$Id: lang.php 137 2011-04-18 19:48:11Z ryan $
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

//if(!empty($_GET)) extract($_GET);
//if(!empty($_POST)) extract($_POST);
global $f2;
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

		// check if db is setup yet
		if(isset($dbhost) && isset($dbusername) && isset($database_name)) {
			if(!empty($dbhost) && !empty($dbusername) && !empty($database_name)) {
				
				// set lang and locale
				$sql = "SELECT * FROM lang WHERE `is_default`='Y' ";
				if ($result = mysql_query ($sql)) {
					$row = mysql_fetch_array($result, MYSQL_ASSOC);
					$_SESSION['MDS_LANG'] = $row['lang_code'];
					if ($row['charset']!='') {
						setlocale(LC_TIME, $row['charset']);
					}
				}
			} else {
				// no db so use defaults
				$_SESSION['MDS_LANG'] = 'EN';
				setlocale(LC_TIME, 'en_US.utf8');
			}
		}

	}
	
}

global $AVAILABLE_LANGS;
global $LANG_FILES;



// check if db is setup yet
if(isset($dbhost) && isset($dbusername) && isset($database_name)) {
	if(!empty($dbhost) && !empty($dbusername) && !empty($database_name)) {

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

	} else {
		// no db so use defaults
		include dirname(__FILE__)."/english.php";
	}
}

?>