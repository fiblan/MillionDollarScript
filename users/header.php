<?php
/**
 * @version		$Id: header.php 90 2010-12-14 21:30:27Z ryan $
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

echo $f2->get_doc(); ?>

<link rel='StyleSheet' type="text/css" href="style.css" >

<style type="text/css">


</style>

<title><?php echo SITE_NAME; ?></title>

</head>

<body>
<?php

if (USE_AJAX=='SIMPLE') {
	$order_page = 'order_pixels.php';
} else {
	$order_page = 'select.php';
}

?>
<img src="<?php echo SITE_LOGO_URL; ?>">
<div style='background-color: #ffffff; border-color:#C0C0C0; border-style:solid;padding:10px;'>
<div class="menu_bar">
<a href="index.php" class="menu_bar"><?php echo $label['advertiser_header_nav1']; ?></a> | <a href="<?php echo $order_page; ?>" class="menu_bar"><?php echo $label['advertiser_header_nav2'];?></a>  | <a href="publish.php"  class="menu_bar"><?php echo $label['advertiser_header_nav3'];?></a> | <a href="orders.php"  class="menu_bar"><?php echo $label['advertiser_header_nav4'];?></a>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  <?php
if ($_SESSION['MDS_ID']!='') { ?>
<a href='logout.php'  class="menu_bar" ><?php echo $label['advertiser_header_nav5']; ?></a>
<?php } ?>
</div>
