<?php
session_start();
require "../config.php";
?>

<?php include('login_functions.php'); ?>
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
	
?>
<html>
<head>

<link rel="stylesheet" type="text/css"
href="style.css" />

<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<style type="text/css">
<!--
.style1 {
	color: #FFFFFF;
	font-weight: bold;
}
-->
</style>
<?php

$label["advertiser_signup_heading1"] = str_replace ("%SITE_NAME%", SITE_NAME , $label["advertiser_signup_heading1"]);

?>
</head>
<body>
<center><img src="<?php echo SITE_LOGO_URL; ?>"/> <br/>
      <h3 ><?php echo $label["advertiser_signup_heading1"]; ?></h3></center> 
<table width="60%" align="center" width="100%"  border="0" cellspacing="0" cellpadding="0" >
	<tr>
		<td width="35" height="26">&nbsp;</td>
		<td height="26" valign="bottom"><div align="center"><h3 ><?php echo $label["advertiser_signup_heading2"]; ?></h3> </div></td>
		<td width="35" height="26">&nbsp;</td>
	</tr>
	<tr>
		<td width="35">&nbsp;</td>
		<td>
			<?php
				if ($_REQUEST['form']=="filled") {

					$success = process_signup_form();
					
				} // end submit

				if (!$success) {
					//Signup form is shown below

					display_signup_form($_REQUEST['FirstName'], $_REQUEST['LastName'], $_REQUEST['CompName'], $_REQUEST['Username'], $_REQUEST['Password'], $_REQUEST['Password2'], $_REQUEST['Email'], $_REQUEST['Newsletter'], $_REQUEST['Notification1'], $_REQUEST['Notification2'], $_REQUEST['lang']);
					
				} else {


				}

				
			?>

		</td>
		<td width="35">&nbsp;</td>
	</tr>
	<tr>
		<td width="35" height="26">&nbsp;</td>
		<td height="26"></td>
		<td width="35" height="26">&nbsp;</td>
	</tr>
</table>

</body>

</html>
