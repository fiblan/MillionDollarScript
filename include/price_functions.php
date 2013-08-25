<?php
/**
 * @version		$Id: price_functions.php 159 2012-10-11 17:31:31Z ryan $
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

$price_table = array();

function load_price_zones($banner_id) {

	global $price_table;

	if (isset($price_table['loaded'])) {
		if($price_table['loaded']==1) {
			return;
		}
	}

	$sql = "SELECT * FROM prices where banner_id='$banner_id' ";
	$result = mysql_query($sql) or die (mysql_error().$sql);
	$key=0;
	while ($row = mysql_fetch_array($result)) {
		$price_table[$key]['row_from']=$row['row_from'];
		$price_table[$key]['row_to']=$row['row_to'];
		$price_table[$key]['col_from']=$row['col_from'];
		$price_table[$key]['col_to']=$row['col_to'];
		$price_table[$key]['color']=$row['color'];
		$price_table[$key]['price']=$row['price'];
		$price_table[$key]['currency']=$row['currency'];
		$key++;
	}

	$price_table['loaded'] = 1;


}

##################################################

function get_zone_color($banner_id, $row, $col) {
	$row = $row+1;
	$col = $col+1;
	global $price_table;
	if ($price_table['loaded']!=1) {
		load_price_zones($banner_id);
	}

	$color = "white";

	foreach ($price_table as $key=>$val) {
		if ((($price_table[$key]['row_from'] <= $row) && ($price_table[$key]['row_to'] >= $row)) &&
			(($price_table[$key]['col_from'] <= $col) && ($price_table[$key]['col_to'] >= $col))) {
			
			return $price_table[$key]['color'];
		}
	}

	return $color;

	
}

#############################################################
# Deprecated function 
function get_block_color($banner_id, $block_id) {
	// Returns as default currency.

	$sql = "select color from prices where block_id_from <= '$block_id' AND block_id_to >= '$block_id' AND banner_id='$banner_id' ";
	$result = mysql_query($sql) or die (mysql_error());

	if (mysql_num_rows($result)==0) {
		// get default price 

		$row[color] = "white";
	} else {
		$row = mysql_fetch_array($result);
	}

	//echo "curr".$row['currency']." price".$row['price']."<br>";
	return $row['color'];

}

###############################################
function get_block_price($banner_id, $block_id) {
	// Returns as default currency.

	// get co-ords of the block

	$sql = "select x, y from blocks where block_id='$block_id' and banner_id='$banner_id' ";
	$result = mysql_query($sql) or die (mysql_error());
	$block_row = mysql_fetch_array($result);

	$row = $block_row['x'];
	$col = $block_row['y'];


	$price = get_zone_price($banner_id, $row, $col);


	return $price;


}
##################################################################

function get_zone_price($banner_id, $row, $col) {

	$row = $row+1;
	$col = $col+1;
	global $price_table;
	if(isset($price_table['loaded'])) {
		if($price_table['loaded'] != 1) {
	
		load_price_zones($banner_id);
		//print_r ($price_table);
		}
	} else {
		load_price_zones($banner_id);
	}

	
	foreach ($price_table as $key=>$val) {
		if ((($price_table[$key]['row_from'] <= $row) && ($price_table[$key]['row_to'] >= $row)) &&
			(($price_table[$key]['col_from'] <= $col) && ($price_table[$key]['col_to'] >= $col))) {
			
			$price = convert_to_default_currency($price_table[$key]['currency'], $price_table[$key]['price']);
			//echo "Zone: row $row col $col ($price)\n";
			return $price;
		}
	}

	// if not found..

	$sql = "select price_per_block as price, currency from banners where  banner_id='$banner_id' ";
	$result2 = mysql_query($sql) or die (mysql_error());
	$block_row = mysql_fetch_array($result2);
		
	//echo "curr".$row['currency']." price".$row['price']."<br>";
	$price = convert_to_default_currency($block_row['currency'],$block_row['price']);
	//echo "row $row col $col ($price)\n";
	return $price;

}



##########################

function show_price_area($banner_id) {

	$sql = "select grid_width, grid_height from banners where  banner_id='$banner_id' ";
	$result = mysql_query($sql) or die (mysql_error());
	$row = mysql_fetch_array($result);
	$grid_width = $row['grid_width'];
	$grid_height = $row['grid_height'];

	$sql = "SELECT * FROM prices where banner_id='$banner_id'";
	$result = mysql_query($sql) or die (mysql_error());

	?>

	<map name="prices" id="prices">

<?php

while ($row=mysql_fetch_array($result)) {
	$row['row_from'] = $row['row_from']-1;
	$row['row_to'] = $row['row_to']-1;

	$row['col_from'] = $row['col_from']-1;
	$row['col_to'] = $row['col_to']-1;

// the x and y coordinates of the upper left and lower right corner

	
	
?>

<area  shape="RECT" coords="<?php echo $row['col_from']*10;?>,<?php echo $row['row_from']*10;?>,<?php echo ($row['col_to']*10)+10;?>,<?php echo ($row['row_to']*10)+10;?>" href="" title="<?php echo htmlspecialchars($row['price']);?>" alt="<?php echo htmlspecialchars($row[price]);?>" onclick="return false; " target="_blank" />

<?php
	

}

?>

</map>
<?php

}

##############################################################

function display_price_table ($banner_id) {

	global $label, $f2;
	
	$banner_id = $banner_id;

	if (banner_get_packages($banner_id)) {

		return false; // cannot have custom price zones, this banner has packages.

	}

	// get the default price
	$sql = "select price_per_block as price, currency from banners where  banner_id='$banner_id' ";
	$result2 = mysql_query($sql) or die (mysql_error());
	$row = mysql_fetch_array($result2);
	$price=$row['price'];

	$sql = "SELECT * FROM prices where banner_id='$banner_id' order by row_from";
	$result = mysql_query($sql) or die (mysql_error());

	if (mysql_num_rows($result)> 0) {
		?>
		<p>
		<div class='fancy_heading' width="85%"><?php echo $label['advertiser_pf_table'];?></div>
		<p>
		<?php echo $label['advertiser_pf_intro']; ?>&nbsp;
		</p>
		<table border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9" width="50%">
		<tr >
			<td><b><font face="Arial" size="2"><?php echo $label['advertiser_pf_price'];?></font></b></td>
			<td><b><font face="Arial" size="2"><?php echo $label['advertiser_pf_color'];?></font></b></td>
			<td><b><font face="Arial" size="2"><?php echo $label['advertiser_pf_fromrow'];?></font></b></td>
			<td><b><font face="Arial" size="2"><?php echo $label['advertiser_pf_torow'];?></font></b></td>
			<td><b><font face="Arial" size="2"><?php echo $label['advertiser_pf_fromcol'];?></font></b></td>
			<td><b><font face="Arial" size="2"><?php echo $label['advertiser_pf_tocol'];?></font></b></td>
			
	
			
		</tr>

		<?php
		while ($row = mysql_fetch_array($result)) {
?>
<tr bgcolor="#ffffff">
			<td ><font face="Arial" size="2"><?php if ($row['price']==0) { echo $label['free'];} else { echo convert_to_default_currency_formatted($row['currency'], $row['price'], true) ; } ?></font></td>
			<td bgcolor="<?php if ($row[color]=='yellow') { echo '#FFFF00';} elseif ($row['color']=='cyan') { echo '#00FFFF';} elseif ($row[color]=='magenta') { echo '#FF00FF';} ?>"><font face="Arial" size="2" ><?php 
					
				echo $row['color'];
				 
				 ?>
				
				</font></td>
				<td><font face="Arial" size="2"><?php echo $row[row_from];?></font></td>
				<td><font face="Arial" size="2"><?php echo $row[row_to];?></font></td>
				<td><font face="Arial" size="2"><?php echo $row[col_from];?></font></td>
				<td><font face="Arial" size="2"><?php echo $row[col_to];?></font></td>
				
	
				</tr>
<?php
		}

		?>

		</table>
		</p>

		<?php


	}


}
#######################################################
# return's the order's price in default currency
function calculate_price($banner_id, $blocks_str) {

	if ($blocks_str=='') {
		return;
	}
	$blocks = explode (",", $blocks_str);
	foreach ($blocks as $block_id) {
		
		$sql = "SELECT price, currency FROM blocks where block_id='".$block_id."'";
		$result = mysql_query($sql) or die(mysql_error());
		$row = mysql_fetch_array($result);
		
		//echo "call to get_block_price";

		$price += get_block_price($banner_id, $block_id);

		//echo ' finished get_block_price<br>';

	
		
	}

	//echo "price is".$price."<br>";
	
	return $price;
}
?>