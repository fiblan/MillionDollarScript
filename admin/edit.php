<?php
/**
 * @version		$Id: edit.php 62 2010-09-12 01:17:36Z ryan $
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

require("../config.php");
require ('admin_common.php');



$user_id = $_REQUEST[user_id];

?>
<h3>Edit User's Account Details</h3>
<p>
Here you can edit a user's name, email, company name and change their password.
<p>

<?php

if ($_REQUEST[action]=='changepass') {

	$sql = "select * from users where ID=".$user_id;
	$result = mysql_query ($sql) or die (mysql_error());
	$row = mysql_fetch_array($result);

	$oldpass = md5 ($_REQUEST['oldpass']);
	$newpass = md5 ($_REQUEST['password']);

	//if ($row['Password']==$oldpass) {

		if (strcmp($_REQUEST['password'],$_REQUEST['password2'])==0) {

			$sql = "UPDATE users set password='$newpass' where ID=".$user_id;
			mysql_query ($sql) or die (mysql_error());
			echo "<h3><font color=green>OK: Password was changed.</font></h3><br>";

		} else {

			echo "<h3><font color=red>Error: New passwords do not match</font></h3><br>";

		}

	//} else {
	//	echo "<h3><font color=red>Error: Incorrect current password.</font></h3><br>";

	//}


}

?>
<form name="form1" method="post">
<table border="0">
<tr><td colspan="2"><h3>Change Password</h3></tr>
<tr>
	<td>&nbsp;</td>
</tr><tr>
	<td>New Password</td><td><input type="password" name="password"></td>
</tr></tr>
	<td>Re-type Password</td><td><input type="password" name="password2"></td>
</tr><tr>
	<td colspan="2"><input type="submit" value="Change Password"></td>
</tr>
</table>
<input type="hidden" name="action" value="changepass">
<input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
</form>
</p>
<hr>
<p>
<?php

if ($_REQUEST[action]=='update') {

	//print_r ($_REQUEST);

	$sql = "UPDATE users set FirstName='".$_REQUEST['firstname']."', LastName='".$_REQUEST['lastname']."', CompName='".$_REQUEST['compname']."', Email='".$_REQUEST['email']."' where ID=".$user_id;
	mysql_query ($sql) or die (mysql_error());
//echo $sql;

	echo "<h3><font color=green>OK: User's details were updated.</font></h3><br>";

}

if ($_REQUEST['action']=='rank') {

	$sql = "UPDATE users set Rank='".$_REQUEST['rank']."' where ID=".$user_id;
	mysql_query ($sql) or die (mysql_error());
//echo $sql;

	//echo "<h3><font color=green>OK: User's details were updated.</font></h3><br>";

}

$sql = "select * from users where ID=".$_REQUEST['user_id'];
$result = mysql_query ($sql) or die (mysql_error());
$row = mysql_fetch_array($result);
$lastname = $row['LastName'];
$firstname = $row['FirstName'];
$compname = $row['CompName'];
$email = $row['Email'];

?>
<form name="form2" method="post">
<table border="0">
<tr><td colspan="2"><h3>Update Personal Details</h3></tr>
<tr>
	<td>First Name</td><td><input type="text" name="firstname" value="<?php echo $firstname; ?>" ></td>
</tr><tr>
	<td>Last Name</td><td><input type="text" name="lastname" value="<?php echo $lastname; ?>"></td>
</tr><tr>
	<td>Company Name</td><td><input type="text" size="30" name="compname" value="<?php echo $compname; ?>"></td>
</tr><tr>
	<td>Email Address</td><td><input type="text" size="30" name="email" value="<?php echo $email; ?>"></td>
</tr><tr>
	<td colspan="2"><input type="submit" value="Save"></td>
</tr>
</table>
<input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
<input type="hidden" name="action" value="update">
</form>
</p>
<hr>
<?php

$rank = $row['Rank'];

?>
<form name="form2" method="post">
<table border="0">
<tr><td colspan="2"><h3>Account Status</h3></tr>
<tr>
	<td>Status:</td><td><input type="radio" name="rank" value="1" <?php if ($rank != 2) echo " checked "; ?> > - Normal Customer<br>
	<input type="radio" name="rank" value="2" <?php if ($rank == 2) echo " checked " ; ?>> - Privileged User (All pixels are free!)<br></td>
</tr>
<tr>
	<td colspan="2"><input type="submit" value="Save"></td>
</tr>
</table>
<input type="hidden" name="user_id" value="<?php echo $_REQUEST['user_id']; ?>">
<input type="hidden" name="action" value="rank">
</form>

<hr>

<a href="customers.php">Return to the list</a>