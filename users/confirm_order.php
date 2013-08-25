<?php
/**
 * @version		$Id: confirm_order.php 137 2011-04-18 19:48:11Z ryan $
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

##############################
function display_edit_order_button ($order_id) {
	global $BID, $label;
	if (USE_AJAX=='SIMPLE') {
		$order_page = 'order_pixels.php';
	} else {
		$order_page = 'select.php';
	}
?>
	<input type='button' class='big_button' value="<?php echo $label['advertiser_o_edit_button']; ?>" Onclick="window.location='<?php echo $order_page; ?>?&BID=<?php echo $BID; ?>&order_id=<?php echo $order_id;?>'">

<?php


}

##############################################

// check if we have pixels...

update_temp_order_timestamp();

$sql = "select * from temp_orders where session_id='".addslashes(session_id())."' ";
$order_result = mysql_query ($sql) or die(mysql_error());


if (mysql_num_rows($order_result)==0) {
	require ("header.php");
	?>
<h1><?php echo $label['no_order_in_progress']; ?></h1>
<p><?php $label['no_order_in_progress_go_here'] = str_replace ('%ORDER_PAGE%', $order_page ,  $label['no_order_in_progress_go_here']); echo $label['no_order_in_progress_go_here']; ?></p>

	<?php
	require ("footer.php");
	die();

} else {
	$order_row =mysql_fetch_array($order_result);

}


//print_r($order_row);
// get the banner ID
$BID = $order_row['banner_id'];

$b_row = load_banner_constants($BID);

/*

Login ->

Select paln ->

Conform order

*/
require ("login_functions.php");

if ($_SESSION['MDS_ID']=='') {   // not logged in..
	require ("header.php");

	?>
	<h3>
	<?php echo $label['not_logged_in']; ?>
	</h3>

<table cellpadding=5 border=1 style="border-collapse: collapse; border-style:solid; border-color:#D2D2D2">

<tr>
<td width="50%" bgcolor='#EBEBEB'>

<?php 
	
/// signup



	//Signup form is shown below

	if ($_REQUEST['form']=="filled") {

		$success = process_signup_form('confirm_order.php');
					
	} // end submit

	if (!$success) {

		?>

		<h2><?php echo $label['conirm_signup']; ?></h2>
		<h3><?php echo $label['confirm_instructions']; ?></h3>

		<?php

		display_signup_form($_REQUEST['FirstName'], $_REQUEST['LastName'], $_REQUEST['CompName'], $_REQUEST['Username'], $_REQUEST['Password'], $_REQUEST['Password2'], $_REQUEST['Email'], $_REQUEST['Newsletter'], $_REQUEST['Notification1'], $_REQUEST['Notification2'], $_REQUEST['lang']);
	} else {
					
	}

?></td>
<td valign=top>
<h2><?php echo $label['confirm_login']; ?></h2>
<h3><?php echo $label['confirm_member']; ?></h3>
<?php echo login_form(false, 'confirm_order.php'); ?></td>
</tr>

</table>
<p>&nbsp;</p>

<?php

} else { // The user is singed in

	$has_packages = banner_get_packages($BID);
	
	require ("header.php");

	?>

<p>
<?php 
show_nav_status (3);
?>

</p>
	

	<?php

	

	$cannot_get_package = false; 

	if ($has_packages && $_REQUEST['pack']!='') { // has packages, and a package was selected...

		// check to make sure this advertiser can order this package

		if (can_user_get_package($_SESSION['MDS_ID'], $_REQUEST['pack'])) {

			
			$sql = "SELECT quantity FROM temp_orders WHERE session_id='".addslashes(session_id())."'";
			$result = mysql_query ($sql) or die (mysql_error().$sql);
			$row = mysql_fetch_array($result);
			$quantity = $row['quantity'];

			$block_count = $quantity / (BLK_WIDTH*BLK_HEIGHT);
			
			// Now update the order (overwite the total & days_expire with the package)

			$pack = get_package($_REQUEST['pack']);
			$total = $pack['price'] * $block_count;
			// convert & round off
		
			$total = convert_to_default_currency($pack['currency'], $total);
		

			$sql = "UPDATE temp_orders SET package_id='".$_REQUEST['pack']."', price='".$total."',  days_expire='".$pack['days_expire']."', currency='".get_default_currency()."' WHERE session_id='".addslashes(session_id())."'";
 
			mysql_query ($sql) or die (mysql_error().$sql);

			$order_row['price']=$total;
			$order_row['pack']=$_REQUEST['pack'];
			$order_row['days_expire']=$pack['days_expire'];
			$order_row['currency']=get_default_currency();

		} else {
			$selected_pack = $_REQUEST['pack'];
			$_REQUEST['pack']='';
			$cannot_get_package=true;

		}


	}


	if (($has_packages) && ($_REQUEST['pack']=='')) {

		echo "<form method='post' action='".$_SERVER['PHP_SELF']."'>";
		?>
		<input type="hidden" name="selected_pixels" value="<?php echo $_REQUEST['selected_pixels'];?>">
		<input type="hidden" name="order_id" value="<?php echo $_REQUEST['order_id'];?>">
		<input type="hidden" name="BID" value="<?php echo $f2->bid($_REQUEST['BID']);?>">
		<?php
		display_package_options_table($BID, $_REQUEST['pack'], true);
		echo "<input class='big_button' type='button' value='".$label['advertiser_pack_prev_button']."' onclick='window.location=\"write_ad.php?&BID=$BID&ad_id=".$order_row['ad_id']."\"' >" ;
		echo "&nbsp; <input class='big_button' type='submit' value='".$label['advertiser_pack_select_button']."'>";
		echo "<form>";

		if ($cannot_get_package) {

			$sql = "SELECT * from packages where package_id='".$selected_pack."'";
			$p_result = mysql_query($sql) or die(mysql_error());
		    $p_row = mysql_fetch_array($p_result);
			$p_max_ord = $p_row['max_orders'];

			$label['pack_cannot_select'] = str_replace ("%MAX_ORDERS%", $p_row['max_orders'], $label['pack_cannot_select']);

			echo "<p>".$label['pack_cannot_select']."</p>";

		}

	} else {
		display_order(session_id(), $BID);

		$sql = "select * from users where ID='".$_SESSION['MDS_ID']."'";
		$result = mysql_query ($sql) or die (mysql_error().$sql);
		$u_row = mysql_fetch_array($result);

		?>
		<p>
		<?php display_edit_order_button ('temp');?> &nbsp;
		<?php

		//echo "can ordr:".can_user_order($b_row, $_SESSION['MDS_ID'], $_REQUEST['pack']);
		if (!can_user_order($b_row, $_SESSION['MDS_ID'], $_REQUEST['pack'])) { // one more check before continue

			if (!$p_max_ord) {
				$max = G_MAX_ORDERS;
			} else {	
				$max = $p_max_ord;
			}

			$label['pack_cannot_select'] = str_replace ("%MAX_ORDERS%", $max, $label['pack_cannot_select']);

			echo "<p>".$label['advertiser_max_order']."</p>";
		} else {


			if (($order_row['price']==0) || ($u_row['Rank']==2)) { // go straight to publish...
				//http://localhost/MillionDollarScript-2.0.13/users/publish.php?action=complete&BID=2&order_id=temp
				?>
				
				<input type='button' class='big_button' value="<?php echo htmlentities( $label['advertiser_o_completebutton']); ?>" Onclick="window.location='publish.php?action=complete&BID=<?php echo $BID; ?>&order_id=temp'"> 
				<?php

			} else { // go to payment
		 
				?>
		
				<input type='button' class='big_button' value="<?php echo htmlentities($label['advertiser_o_confpay_button']); ?>" Onclick="window.location='checkout.php?action=confirm&BID=<?php echo $BID; ?>'"> 
				
				<?php  
			}

		}
		?>
		</p>
		<hr>

		<?php
	}

	?>

	
	<?php

	

} 

require ("footer.php");