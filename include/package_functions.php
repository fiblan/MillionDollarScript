<?php
/**
 * @version		$Id: package_functions.php 137 2011-04-18 19:48:11Z ryan $
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

# Lists pacakes for advertiser to choose
function display_package_options_table($banner_id, $selected='', $selection_ability) {
	global $label, $f2;
	echo $banner_id;
	$banner_id = $banner_id;
	
	$sql = "SELECT * FROM packages WHERE banner_id='$banner_id' ORDER BY price ASC ";
	$result = mysql_query($sql) or die (mysql_error());

	if (mysql_num_rows($result)> 0) {
		?>
		
		<div class='fancy_heading' width="85%"><?php echo $label['advertiser_package_table'];?></div>
		<p>
		<?php 
		if ($selection_ability) {
			echo $label['advertiser_pa_intro_sel']; 
		} else {
			echo $label['advertiser_pa_intro_show'];
		}
			?>&nbsp;
		</p>
		<table border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9" width="50%">
		<tr>
		<?php
		if ($selection_ability) {	
			?>
			<td><b><font face="Arial" size="2"><?php echo $label['pack_head_select']; ?></font></b></td>
			<?php
		}
		?>
		<td><b><font face="Arial" size="2"><?php echo $label['pack_head_price']; ?></font></b></td>
		<td><b><font face="Arial" size="2"><?php echo $label['pack_head_exp'];?></font></b></td>
		<td><b><font face="Arial" size="2"><?php echo $label['pack_head_mo']; ?></font></b></td>
		</tr>

		<?php
		while ($row = mysql_fetch_array($result)) {

			if ($selected!='') {

				if ($row['package_id'] == $selected) {
					$sel = " checked ";
				} else {
					$sel ='';
				}

			} else { 
			// make sure the first item is selected by default.
				if ($first_sel == false) {
					$sel = 'checked';
					$first_sel = true;
				} else {
					$sel = '';
				}
			}
?>
		<tr bgcolor="#ffffff">

			<?php
		if ($selection_ability) {	
			?>

			<td><font face="Arial" size="2"><input <?php echo $sel; ?> type="radio" id="P<?php echo $row['package_id'];?>" name="pack" value="<?php echo $row['package_id'];?>"></font></td>

			<?php
			}	
			?>

			<td><font face="Arial" size="2"><label for="P<?php echo $row['package_id'];?>"><?php  if ($row['price']==0) { echo $label['free'];} else { echo convert_to_default_currency_formatted($row['currency'], $row['price'], true) ; echo " ".$label['pack_price_per100'];} ?> <?php  ?></label></font></td>
			<td><font face="Arial" size="2"><?php 
				
			if ($row['days_expire']=='0') {
				echo $label['pack_never'];
			} 
				
			else {
				
				$str = str_replace ('%DAYS_EXPIRE%', $row['days_expire'], $label['pack_expires_in']);
				
				echo $str; 
			} 
			
			?></font></td>
			<td><font face="Arial" size="2"><?php if ($row['max_orders']=='0') {echo $label['pack_unlimited'];} else { echo $row['max_orders']; }
				 ?></font></td>

		</tr>

	<?php } ?>

		</table>
		</p>
		<?php

	}


}

#####################################

/*

Returns:

$pack['max_orders']
$pack['price']
$pack['currency']
$pack['days_expire']

*/
function get_package($package_id) {

	$sql = "SELECT * FROM packages where package_id='$package_id'";
	$result = mysql_query($sql) or die(mysql_error().$sql);
	$row = mysql_fetch_array($result);

	$pack['max_orders'] = $row['max_orders'];
	$pack['price'] = $row['price'];
	$pack['currency'] = $row['currency'];
	$pack['days_expire'] = $row['days_expire'];

	return $pack;


}

#######################################
/*

Returns true or false if the user can select this package
looks at user's previous orders to determine how many times
the package was ordered, and compres it with max_orders
*/
function can_user_get_package($user_id, $package_id) {


	$sql = "SELECT max_orders, banner_id FROM packages WHERE package_id='".$package_id."'";
	$result = mysql_query($sql) or die(mysql_error().$sql);
	$p_row = mysql_fetch_array($result);
//echo $sql;
	if ($p_row['max_orders']==0) {

		return true;

	}
	
	// count the orders the user made for this package

	$sql = "SELECT count(*) AS order_count, banner_id FROM orders WHERE status <> 'deleted' AND status <> 'new' AND package_id='".$package_id."' AND user_id='$user_id' GROUP BY user_id LIMIT 1";
	//echo " $sql ";
	$result = mysql_query($sql) or die(mysql_error().$sql);
	$u_row = mysql_fetch_array($result);

	if ($u_row['order_count'] < $p_row['max_orders']) {
		
		return true;
	} else {
		return false;

	}



}

##############################################
/*
Checkes the grid for packages and returns the result
return True or False

*/
function banner_get_packages($banner_id) {
	global $f2;
	$banner_id = $f2->bid($banner_id);

	$sql = "SELECT * FROM packages WHERE banner_id=$banner_id";
	$result = mysql_query($sql) or die (mysql_error().$sql);
	if (mysql_num_rows($result)>0) {
		return $result;
	}
		
	return false;
}


##############################################

function get_default_package($banner_id) {
	global $f2;
	$banner_id = $f2->bid($banner_id);
	
	$sql = "SELECT package_id FROM packages WHERE banner_id=$banner_id AND is_default='Y' ";
	$result = mysql_query($sql) or die (mysql_error().$sql);
	$row = mysql_fetch_array($result);
	return $row['package_id'];

}


#################################################

function add_package_to_order($order_id, $package_id) {

	$pack = get_package($package_id);

	//user_id, order_id, blocks, status, order_date, price, quantity, banner_id, currency, days_expire, date_stam

	$sql = "SELECT * FROM orders WHERE order_id='$order_id'";
	$result = mysql_query($sql) or die (mysql_error().$sql);
	$row = mysql_fetch_array($result);

	$total = ($row['quantity'] / 100) * $pack['price'];
	$total = convert_to_default_currency($pack['currency'], $total);

	$sql = "UPDATE orders set price='$total', currency='".get_default_currency()."' expire_days='".$pack['expire_days']."' WHERE order_id=$order_id ";

}

?>