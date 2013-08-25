<?php
session_start();
require ("../config.php");
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
$now = (gmdate("Y-m-d H:i:s"));
$sql = "UPDATE `users` SET `logout_date`='$now' WHERE `Username`='".$_SESSION['MDS_Username']."'";
      //echo $sql;
 mysql_query($sql);
      

unset($_SESSION['MDS_ID']);
$_SESSION['MDS_ID']='';
$_SESSION['MDS_Domain']='';
session_destroy();
//require ('header.php'); 

?>
<html>
<head>
<link rel="stylesheet" type="text/css"
href="style.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
</head>
<body>
<center><img alt="" src="<?php echo SITE_LOGO_URL; ?>"/> <br/>
      <h3><?php echo $label['advertiser_logout_ok']; ?></h3> <a href="../"><?php 
	  $label["advertiser_logout_home"] = str_replace ("%SITE_NAME%", SITE_NAME , $label["advertiser_logout_home"]);
	  echo $label['advertiser_logout_home']; ?></a></center> 
</body>

<?php

//require ('footer.php'); 

?>