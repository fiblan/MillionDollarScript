<?php
/**
 * @version		$Id: logout.php 88 2010-10-12 16:43:19Z ryan $
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
require ("../config.php");

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
<?php echo $f2->get_doc(); ?>


<link rel="stylesheet" type="text/css" href="style.css" />
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