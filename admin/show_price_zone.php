<?php
/**
 * @version		$Id: show_price_zone.php 137 2011-04-18 19:48:11Z ryan $
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

define ('NO_HOUSE_KEEP', 'YES');

require("../config.php");
require ('admin_common.php');

session_start();

$BID = $f2->bid($_REQUEST['BID']);

if ($BID=='') { $BID='1'; }

//$sql = "select * from banners where banner_id=$BID";
//$result = mysql_query ($sql) or die (mysql_error().$sql);
//$b_row = mysql_fetch_array($result);
load_banner_constants($BID);

//echo USR_GRID_BLOCK;

$currency = get_default_currency();

#
# Preload all block
$sql = "select block_id, status, user_id, image_data FROM blocks where status='sold' AND banner_id=$BID ";
$result = mysql_query ($sql) or die (mysql_error().$sql);
while ($row=mysql_fetch_array($result)) {
	$blocks[$row[block_id]] = $row['status'];
	if (($row[user_id] == $_REQUEST[user_id]) && ($row['status']!='ordered') && ($row['status']!='sold')) {
		$blocks[$row[block_id]] = 'onorder';
		$order_exists = true;
	} elseif (($row['status']!='sold') && ($row[user_id] != $_REQUEST[user_id]) ) {
		$blocks[$row[block_id]] = 'reserved';

	}
	
	if ($row[image_data]!='') {
		$images[$row[block_id]]=imagecreatefromstring(base64_decode($row[image_data]));
	}
		//echo $row[block_id]." ";
}
$cell =0;
if (function_exists("imagecreatetruecolor")) {
	$map = imagecreatetruecolor ( G_WIDTH*BLK_WIDTH, G_HEIGHT*BLK_HEIGHT );
} else {
	$map = imagecreate ( G_WIDTH*BLK_WIDTH, G_HEIGHT*BLK_HEIGHT );
}

	$block = imagecreatefromstring (USR_GRID_BLOCK );
	//$selected_block = imagecreatefrompng ( "selected_block.png" );
	$sold_block = imagecreatefromstring ( USR_SOL_BLOCK );
	//$reserved_block = imagecreatefrompng ( "reserved_block.png" );
	//$ordered_block = imagecreatefrompng ( "ordered_block.png" );

	$cyan_block = imagecreate(BLK_WIDTH, BLK_HEIGHT);
	//$color= ImageColorAllocate( $cyan_block, 255, 255, 255); 
	$color= ImageColorAllocate( $cyan_block, 0, 255, 255); 
	imagerectangle ( $cyan_block , 0, 0, BLK_WIDTH, BLK_HEIGHT, $color );

	$yellow_block = imagecreate(BLK_WIDTH, BLK_HEIGHT);
	//$color= ImageColorAllocate( $yellow_block,  255, 255, 255);
	$color= ImageColorAllocate( $yellow_block,  255, 255, 0); 
	imagerectangle ( $yellow_block , 0, 0, BLK_WIDTH, BLK_HEIGHT, $color );

	$magenta_block = imagecreate(BLK_WIDTH, BLK_HEIGHT);
	//$color= ImageColorAllocate( $magenta_block,  255, 255, 255);
	$color= ImageColorAllocate( $magenta_block, 255, 0, 255); 
	imagerectangle ( $magenta_block , 0, 0, BLK_WIDTH, BLK_HEIGHT, $color );


	// initialise the map, tile it with blocks
	$i=0; $j=0; $x_pos=0; $y_pos=0;
	$row_c=1;$col_c=1;
	$textcolor = imagecolorallocate($map, 0, 0, 0);
	$textcolor_w = imagecolorallocate($map, 255, 255, 255);
	//$color = get_block_color($BID, $cell);
	$color = get_zone_color($BID, $j, $i);
	for ($i=0; $i < G_HEIGHT; $i++) { // row
		for ($j=0; $j < G_WIDTH; $j++) { // col

			

			if ($images[$cell]!='') {
				imagecopy ( $map, $images[$cell], $x_pos, $y_pos, 0, 0, BLK_WIDTH, BLK_HEIGHT );
				imagedestroy($images[$cell]);

			} elseif($blocks[$cell]!='') {
				imagecopy ( $map, $sold_block, $x_pos, $y_pos, 0, 0, BLK_WIDTH, BLK_HEIGHT );
				
			} else {
				

				//echo "[".$color."]<br>";

				$color = get_zone_color($BID, $i, $j);

				switch ($color) {

					case "cyan":
						imagecopy ( $map, $cyan_block, $x_pos, $y_pos, 0, 0, BLK_WIDTH, BLK_HEIGHT );
						break;
					case "yellow":
						imagecopy ( $map, $yellow_block, $x_pos, $y_pos, 0, 0, BLK_WIDTH, BLK_HEIGHT );
						break;
					case "magenta":
						imagecopy ( $map, $magenta_block, $x_pos, $y_pos, 0, 0, BLK_WIDTH, BLK_HEIGHT );
						break;
					case "white":
						imagecopy ( $map, $block, $x_pos, $y_pos, 0, 0, BLK_WIDTH, BLK_HEIGHT );
						break;

					default:
						imagecopy ( $map, $block, $x_pos, $y_pos, 0, 0, BLK_WIDTH, BLK_HEIGHT );
					break;

				}

			//	imagecopy ( $map, $block, $x_pos, $y_pos, 0, 0, 10, 10 );

			}
			if ($i==1) {
				imagestringup($map, 2, $x_pos, 18, "#$col_c ", $textcolor_w);
				imagestringup($map, 1, $x_pos+1, 18+1, "$col_c ", $textcolor);
				$col_c++;
			}
			$cell++;
			$x_pos += BLK_WIDTH; 

		}
		
		$x_pos = 0;

		imagestring($map, 2, $x_pos, $y_pos, "#$row_c ", $textcolor_w);
		imagestring($map, 1, $x_pos+1, $y_pos+1, "#$row_c ", $textcolor);
		$row_c++;

		$y_pos += BLK_HEIGHT;
		
	}

	// crate a map form the images in the db

	/*
	
	$sql = "select * from blocks where approved='Y' and status='sold' AND image_data <> ''";
	$result = mysql_query($sql) or die(mysql_error());
	
	$i=0;
	while ($row = mysql_fetch_array($result)) {

		$data = $row[image_data];
		
		if (strlen($data)!=0) {
			$block = base64_decode($data);
			$block = imagecreatefromstring($block);
		} else {
			$block = imagecreatefrompng ( $file_path."temp/block.png" );

		}
		imagecopy ( $map, $block, $row['x'], $row['y'], 0, 0, 10, 10 );
		

	}
	*/

	imagedestroy($block);
	imagedestroy($cyan_block);
	imagedestroy($magenta_block);
	imagedestroy($yellow_block);
	imagedestroy($sold_block);
	
	header ("Content-type: image/x-png");
	imagepng($map);
	imagedestroy($map);
?>