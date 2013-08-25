<?php
/**
 * @version		$Id: edit.php 72 2010-09-12 01:31:46Z ryan $
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

process_login();

require ("header.php");


?>
<h3><?php echo $label['advertiser_edit_head']; ?></h3>
<p>
<?php echo $label['advertiser_edit_intro'];?>
<p>

<?php

if ($_REQUEST[action]=='changepass') {

	$sql = "select * from users where ID='".$_SESSION['MDS_ID']."'";
	$result = mysql_query ($sql) or die (mysql_error());
	$row = mysql_fetch_array($result);

	$oldpass = md5 ($_REQUEST['oldpass']);
	$newpass = md5 ($_REQUEST['password']);

	if ($row['Password']==$oldpass) {

		if (strcmp($_REQUEST['password'],$_REQUEST['password2'])==0) {

			$sql = "UPDATE users set password='$newpass' where ID='".$_SESSION['MDS_ID']."'";
			mysql_query ($sql) or die (mysql_error());
			echo "<h3><font color=green>".$label['advertiser_edit_passok'].
			"</font></h3><br>";

		} else {

			echo "<h3><font color=red>".$label['advertiser_edit_pssnomatch']."</font></h3><br>";

		}

	} else {
		echo "<h3><font color=red>".$label['advertiser_edit_badpass']."</font></h3><br>";

	}


}

?>
<form name="form1" method="post">
<table border="0">
<tr><td colspan="2"><h3><?php echo $label['advertiser_edit_chpass'];?></h3></tr>
<tr>
	<td><?php echo $label['advertiser_edit_curpass'];?></td><td><input type="password" name="oldpass"></td>
</tr><tr>
	<td><?php echo $label['advertiser_edit_newpass'];?></td><td><input type="password" name="password"></td>
</tr></tr>
	<td><?php echo $label['advertiser_edit_retypepass'];?></td><td><input type="password" name="password2"></td>
</tr><tr>
	<td colspan="2"><input type="submit" value="<?php echo $label['advertiser_edit_changebutton']; ?>"></td>
</tr>
</table>
<input type="hidden" name="action" value="changepass">
</form>
</p>
<hr>
<p>
<?php

if ($_REQUEST[action]=='update') {

	//print_r ($_REQUEST);

	$sql = "UPDATE users set FirstName='".$_REQUEST['firstname']."', LastName='".$_REQUEST['lastname']."', CompName='".$_REQUEST['compname']."', Email='".$_REQUEST['email']."' where ID='".$_SESSION['MDS_ID']."'";
	mysql_query ($sql) or die (mysql_error());
//echo $sql;

	echo "<h3><font color=green>".$label['advertiser_edit_details_updated']."</font></h3><br>";


}

$sql = "select * from users where ID='".$_SESSION['MDS_ID']."'";

$result = mysql_query ($sql) or die (mysql_error());
$row = mysql_fetch_array($result);
$lastname = $row['LastName'];
$firstname = $row['FirstName'];
$compname = $row['CompName'];
$email = $row['Email'];

?>
<form name="form2" method="post">
<table border="0">
<tr><td colspan="2"><h3><?php echo $label['advertiser_edit_upd_personald'];?></h3></tr>
<tr>
	<td><?php echo $label['advertiser_edit_fname'];?></td><td><input type="text" name="firstname" value="<?php echo ($firstname); ?>" ></td>
</tr><tr>
	<td><?php echo $label['advertiser_edit_lname']; ?></td><td><input type="text" name="lastname" value="<?php echo htmlentities($lastname); ?>"></td>
</tr><tr>
	<td><?php echo $label['advertiser_edit_comp_n']; ?></td><td><input type="text" size="30" name="compname" value="<?php echo htmlentities($compname); ?>"></td>
</tr><tr>
	<td><?php echo $label['advertiser_edit_email']; ?></td><td><input type="text" size="30" name="email" value="<?php echo htmlentities($email); ?>"></td>
</tr><tr>
	<td colspan="2"><input type="submit" value="<?php echo $label['advertiser_edit_savebutton']; ?>"></td>
</tr>
</table>
<input type="hidden" name="action" value="update">
</form>
</p>

<?php

require ("footer.php");

?>



