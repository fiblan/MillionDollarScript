<?php session_start();
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
require "../config.php";

$target_page = $_REQUEST['target_page'];

if ($target_page=='') $target_page='select.php';

?>

<?php include('login_functions.php'); ?>
<html>
<head>
<link rel="stylesheet" type="text/css"
href="style.css" />
<META HTTP-EQUIV="REFRESH" CONTENT="5; URL=<?php echo $target_page; ?>">
</head>
<body>
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="35" height="26">&nbsp;</td>
	  <td height="26" valign="bottom"><center><img alt="" src="<?php echo SITE_LOGO_URL; ?>"/> <br/>
      <h3 ><?php 
	  $label["advertiser_logging_in"] = str_replace ("%SITE_NAME%", SITE_NAME , $label["advertiser_logging_in"]);
	  echo $label["advertiser_logging_in"]; ?> </h3></center> </td>
	</tr>
	<tr>
		<td width="35">&nbsp;</td>
		<td><span>
			<?php
				if (do_login()) {
					$ok = str_replace ( "%username%", $_SESSION['MDS_Username'], $label[advertiser_login_success2]);
					$ok = str_replace ( "%firstname%", $_SESSION['MDS_FirstName'], $ok);
					$ok = str_replace ( "%lastname%", $_SESSION['MDS_LastName'], $ok);
					$ok = str_replace ( "%target_page%", $target_page, $ok);
					echo "<div align='center' >".$ok."</div>";

				} else {
					//echo "<div align='center' >".$label["advertiser_login_error"]."</div>";

				}
			?>
		</span></td>
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
