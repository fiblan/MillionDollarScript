<?php
/**
 * @version		$Id: write_ad.php 157 2012-10-04 15:09:35Z ryan $
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
require_once ("../include/ads.inc.php");

$BID = $f2->bid($_REQUEST['BID']);

$sql = "select * from temp_orders where session_id='".addslashes(session_id())."' ";

$order_result = mysql_query ($sql) or die(mysql_error());

if (mysql_num_rows($order_result)==0) {
	require ("header.php");
	?>
<h1><?php echo $label['no_order_in_progress']; ?></h1>
<p><?php echo $label['no_order_in_progress_go_here'] = str_replace ('%ORDER_PAGE%', $order_page ,  $label['no_order_in_progress_go_here']); //echo $label['no_order_in_progress_go_here']; ?></p>

	<?php
	require ("footer.php");
	die();

}

require ("header.php");
$row = mysql_fetch_array($order_result);

//print_r (unserialize( $row['block_info']));
update_temp_order_timestamp();

$has_packages = banner_get_packages($BID);

//$sql = "select * from banners where banner_id='$BID'";
//$result = mysql_query ($sql) or die (mysql_error().$sql);
//$b_row = mysql_fetch_array($result);


?>
<p>
<?php 
show_nav_status (2);

?>
</p>
<h3><?php $label['write_ad_instructions']; ?></h3>
<?php

$_REQUEST['user_id'] = addslashes(session_id());
//	print_r($_REQUEST);

// TODO: fixing save / edit form

// saving
if ($_REQUEST['save'] != "" ) {
	
	$error = validate_ad_data(1);
	if ($error != '') { // we have an error
		$mode = "user";
		//display_ad_intro();
		display_ad_form (1, $mode, '');
	} else {
		$ad_id = insert_ad_data();

		// save ad_id with the temp order...

		$sql = "UPDATE temp_orders SET ad_id='$ad_id' where session_id='".addslashes(session_id())."' ";
		//echo $sql;
		$result = mysql_query($sql) or die(mysql_error());


		$prams = load_ad_values ($ad_id);
		//print_r($prams);

		?>
		<center><div class='ok_msg_label'><input type="button"  class='big_button' value="<?php echo $label['write_ad_saved']." ".$label['write_ad_continue_button']; ?>" onclick="window.location='confirm_order.php'"></div></center>
		<p>&nbsp;</p>
		<?php
		display_ad_form (1, "user", $prams);
	}
} else {

	// get the ad_id form the temp_orders table..

	$sql = "SELECT ad_id FROM temp_orders WHERE session_id='".addslashes(session_id())."' ";
	$result = mysql_query($sql) or die(mysql_error());
	$row = mysql_fetch_array($result);
	$ad_id = $row['ad_id'];
	//echo "adid is: ".$ad_id;
	$prams = load_ad_values ($ad_id); // user is not logged in

	//print_r($prams);
	display_ad_form (1, 'user', $prams);

}

require ("footer.php");
?>