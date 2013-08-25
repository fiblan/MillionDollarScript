<?php
/**
 * @version		$Id: index.php 72 2010-09-12 01:31:46Z ryan $
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

$sql = "select block_id from blocks where user_id='".$_SESSION['MDS_ID']."' and status='sold' ";
$result = mysql_query($sql) or die(mysql_error());
$pixels = mysql_num_rows($result) * 100;

$sql = "select block_id from blocks where user_id='".$_SESSION['MDS_ID']."' and status='ordered' ";
$result = mysql_query($sql) or die(mysql_error());
$ordered = mysql_num_rows($result) * 100;

$sql = "select * from users where ID='".$_SESSION['MDS_ID']."' ";
$result = mysql_query($sql) or die(mysql_error());
$user_row = mysql_fetch_array($result);

?>
<h3><?php echo $label['advertiser_home_welcome'];?></h3>
<p>
<?php echo $label['advertiser_home_line2']."<br>"; ?>
<p>
<p>
<?php
$label['advertiser_home_blkyouown'] = str_replace("%PIXEL_COUNT%", $pixels, $label['advertiser_home_blkyouown']);
echo $label['advertiser_home_blkyouown']."<br>";

$label['advertiser_home_blkonorder'] = str_replace("%PIXEL_ORD_COUNT%", $ordered, $label['advertiser_home_blkonorder']);


if (USE_AJAX=='SIMPLE') {
	$label['advertiser_home_blkonorder'] = str_replace('select.php', 'order_pixels.php', $label['advertiser_home_blkonorder']);
} 
echo $label['advertiser_home_blkonorder']."<br>";

$label['advertiser_home_click_count'] = str_replace("%CLICK_COUNT%", number_format($user_row['click_count']), $label['advertiser_home_click_count']);
echo $label['advertiser_home_click_count']."<br>";
?>
</p>

<h3><?php echo $label['advertiser_home_sub_head']; ?></h3>
<p>
<?php 

if (USE_AJAX=='SIMPLE') {
	$label['advertiser_home_selectlink'] = str_replace('select.php', 'order_pixels.php', $label['advertiser_home_selectlink']);
} 

echo $label['advertiser_home_selectlink']; ?><br>
<?php echo $label['advertiser_home_managelink']; ?><br>
<?php echo $label['advertiser_home_ordlink']; ?><br>
<?php echo $label['advertiser_home_editlink']; ?><br>
</p>
<p>
<?php echo $label['advertiser_home_quest']; ?> <?php echo SITE_CONTACT_EMAIL; ?>
</p>

<?php

require ("footer.php");
?>