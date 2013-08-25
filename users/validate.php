<?php
/**
 * @version		$Id: validate.php 72 2010-09-12 01:31:46Z ryan $
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
include ("../config.php");
include ("login_functions.php");


?>
 <head>

   <title><?php echo $label["advertiser_loginform_title"]; ?></title>

   <link rel="stylesheet" type="text/css" href="style.css" />

   </head>
   <body>
   <center><img alt="" src="<?php echo SITE_LOGO_URL; ?>"/> <br/>
<p>&nbsp;</p>



<?php
$show_form=true;
if ($_REQUEST['email']!='') {

	// validate

	

	$sql = "SELECT * FROM users where Email='".$_REQUEST['email']."' ";
	$result = mysql_query($sql);


	if ($row = mysql_fetch_array($result)) {

		$code = substr(md5($row[Email].$row[Password]),0, 8);

		if ($_REQUEST['code']==$code) {

			$sql = "UPDATE users SET Validated=1 WHERE Email='".$_REQUEST['email']."'";
			mysql_query($sql);

			echo "<p>&nbsp;</p><center><h3><font color='green'>".$label[advertiser_valid_complete]."</font></h3></center>";

			echo "<p>&nbsp;</p><center><h3><a href='index.php'>".$label['advertiser_valid_login']."</a></h3></center>";

			//process_login();
			$show_form=false;

		} else {
			echo "<p>&nbsp;</p><center><h3>".$label[advertiser_valid_error]."</h3></center>";
			$show_form=true;
		}

	} else {

		$show_form=true;

		echo "<h3>Error: Email address invalid.</h3>";

	}


}

if ($show_form) {
?>
	<center>
<form method="POST" action="<?php echo $_SERVER[PHP_SELF];?>">
<p>
<table><tr><td>
<?php echo $label['advertiser_valid_entemail']; ?></td><td> <input type="text" size="35" name='email' value="<?php echo $_REQUEST[email];?>"></td></tr>
<tr><td>
<?php echo $label['advertiser_valid_entcode']; ?></td><td><input type="text" name='code'></td></tr>
<tr><td colspan="2">

<input type="submit" value="Submit">
</td></tr>
</table>
</p>
</form>
</center>
<?php

}







//require ("footer.php");
?>