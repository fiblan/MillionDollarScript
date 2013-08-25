<?php
/**
 * @version		$Id: payment.php 72 2010-09-12 01:31:46Z ryan $
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
$BID = 1; # Banner ID. Change this later & allow users to select multiple banners
$sql = "select * from banners where banner_id='$BID'";
$result = mysql_query ($sql) or die (mysql_error().$sql);
$b_row = mysql_fetch_array($result);
if ($_REQUEST['order_id']) {
	$_SESSION['MDS_order_id'] = $_REQUEST['order_id'];
}
process_login();
require ("header.php");
?>
<p>
<?php echo $label['advertiser_pay_navmap']; ?>
</p>
<h3><?php echo $label['advertiser_pay_sel_method']; ?></h3>
<?php

if ($_REQUEST['order_id']!='') {

	$order_id = $_REQUEST['order_id'];

} else {
	$order_id = $_SESSION['MDS_order_id'];

}

$sql = "SELECT * from orders where banner_id='$BID' AND order_id='".$order_id."'";
$result = mysql_query($sql) or die(mysql_error().$sql);
$order_row = mysql_fetch_array($result);

########################
# Proceess confirmation
if ($_REQUEST['action']=='confirm') {

	// move temp order to confirmed order

	confirm_order ($_SESSION['MDS_ID'], $_SESSION['MDS_order_id']);

}
##########################

$sql = "SELECT * from orders where order_id='".$_SESSION['MDS_order_id']."'";
$result = mysql_query($sql) or die(mysql_error().$sql);
$order_row = mysql_fetch_array($result);

$dir = dirname(__FILE__);
$dir = preg_split ('%[/\\\]%', $dir);
$blank = array_pop($dir);
$dir = implode('/', $dir);

include $dir.'/payment/payment_manager.php';



payment_option_list($_SESSION['MDS_order_id']);

require ("footer.php");