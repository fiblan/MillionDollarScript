<?php
/**
 * @version		$Id: update_order.php 137 2011-04-18 19:48:11Z ryan $
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
define ('NO_HOUSE_KEEP', 'YES');

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

require_once ("../config.php");
$block_id=$_REQUEST['block_id'];
$BID=$f2->bid($_REQUEST['BID']);

if($_SESSION['MDS_ID']=='') {
	echo "error";
	die();
}

load_banner_constants($BID);

if ($_REQUEST['user_id']!='') {
	
	$user_id = $_REQUEST['user_id'];
	if (!is_numeric($_REQUEST['user_id'])) die();

} else {

	$user_id = $_SESSION['MDS_ID'];

}
$sql = "select * from banners where banner_id='$BID'";
$result = mysql_query ($sql) or die (mysql_error().$sql);
$b_row = mysql_fetch_array($result);

$sql = "select Rank from users where ID='$user_id'";
$result = mysql_query ($sql) or die (mysql_error().$sql);
$u_row = mysql_fetch_array($result);

if (!can_user_order($b_row, $_SESSION['MDS_ID'])) {
		$max_orders=true;
		echo 'max_orders';
		die();
}


// check the max pixels
if (G_MAX_BLOCKS>0) {
	$sql = "SELECT * from blocks where user_id='$user_id' and status='reserved' and banner_id='$BID' ";
	$result = mysql_query($sql) or die(mysql_error().$sql);
	
	$count = mysql_num_rows($result);
	
	if (($count) >= G_MAX_BLOCKS) {
		//echo 'max_selected';
		//die();
		$max_selected = true;
	}
}

$sql = "select status, user_id from blocks where banner_id='$BID' AND block_id='$block_id'";

$result = mysql_query($sql) or die(mysql_error());
$row=mysql_fetch_array($result);

//if ($row[user_id]!=$_SESSION['MDS_ID']) {
//	echo 'error';
//	die();

//}
$order_id=$_SESSION['MDS_order_id'];
$update_order = false;

if (($row['status']=='reserved') && ($row[user_id]==$user_id)) {
	// the block was already selected by the client, this is a double click
	echo 'new'; 
	$update_order = true;

} elseif (($row['status']=='reserved')) { // reserved by someone-else
	echo 'ordered'; 
	$update_order = false; // cannot place or remove from order
	
} elseif ($row['status']!='') {
	$update_order = false;
	echo $row['status']; 
} else {

	$update_order = true;
	$echo_oid = true;

	if ($max_selected) {

		echo 'max_selected';
		die();

	}
	
}

if ($update_order) {

	$sql = "select * from banners where banner_id='$BID'";
	$result = mysql_query ($sql) or die (mysql_error().$sql);
	$b_row = mysql_fetch_array($result);

	select_block ('', '', $block_id);

	if ($echo_oid) {

		echo $order_id;

	}

	


}



?>