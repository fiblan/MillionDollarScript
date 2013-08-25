<?php
/**
 * @version		$Id: show_map.php 137 2011-04-18 19:48:11Z ryan $
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

if ($f2->bid($_REQUEST['BID'])!='') {
	$BID = $f2->bid($_REQUEST['BID']);
} else {
	$BID = 1;

}
//$sql = "select * from banners where banner_id=$BID";
//$result = mysql_query ($sql) or die (mysql_error().$sql);
//$b_row = mysql_fetch_array($result);

load_banner_constants($BID);

#
# Preload all block

if ($_REQUEST[user_id]!='') {
	$sql = "select block_id, status, user_id, image_data FROM blocks where status='sold' AND user_id=".$_REQUEST[user_id]." AND banner_id=$BID ";
} else {
	$sql = "select block_id, status, user_id, image_data FROM blocks where banner_id=$BID ";

}

$result = mysql_query ($sql) or die (mysql_error().$sql);
while ($row=mysql_fetch_array($result)) {
	$blocks[$row[block_id]] = $row['status'];
	/*
	if (($row[user_id] == $_REQUEST[user_id]) && ($row['status']!='ordered') && ($row['status']!='sold')) {
		$blocks[$row[block_id]] = 'onorder';
		$order_exists = true;
	} elseif (($row['status']!='sold') && ($row[user_id] != $_REQUEST[user_id]) ) {
		$blocks[$row[block_id]] = 'reserved';

	}
	*/
	
	if ($row['image_data']!='') {
		$images[$row['block_id']]=imagecreatefromstring(base64_decode($row['image_data']));
	}
		//echo $row[block_id]." ";
}
$cell =0;
if (function_exists("imagecreatetruecolor")) {
	$map = imagecreatetruecolor ( G_WIDTH*BLK_WIDTH, G_HEIGHT*BLK_HEIGHT );
} else {
	$map = imagecreate ( G_WIDTH*BLK_WIDTH, G_HEIGHT*BLK_HEIGHT );

}

	$block = imagecreatefromstring ( GRID_BLOCK );

	$selected_block = imagecreatefromstring ( USR_SEL_BLOCK );
	$sold_block = imagecreatefromstring ( USR_SOL_BLOCK );
	//$reserved_block = imagecreatefrompng ( "reserved_block.png" );
	//$ordered_block = imagecreatefrompng ( "ordered_block.png" );

	// initialise the map, tile it with blocks
	$i=0; $j=0; $x_pos=0; $y_pos=0;

	for ($i=0; $i < G_HEIGHT; $i++) {
		for ($j=0; $j < G_WIDTH; $j++) {

			if ($images[$cell]!='') {
				imagecopy ( $map, $images[$cell], $x_pos, $y_pos, 0, 0, BLK_WIDTH, BLK_HEIGHT );
				imagedestroy($images[$cell]);

			} elseif($blocks[$cell]!='') {
				switch ($blocks[$cell]) {

					case 'reserved':

						imagecopy ( $map, $selected_block, $x_pos, $y_pos, 0, 0, BLK_WIDTH, BLK_HEIGHT );

						break;

					case 'sold':
						default:
						imagecopy ( $map, $sold_block, $x_pos, $y_pos, 0, 0, BLK_WIDTH, BLK_HEIGHT );
						break;
					}
					//imagecopy ( $map, $sold_block, $x_pos, $y_pos, 0, 0, 10, 10 );
				
			} else {
				imagecopy ( $map, $block, $x_pos, $y_pos, 0, 0, BLK_WIDTH, BLK_HEIGHT );
			}
	
			$cell++;
			$x_pos += BLK_WIDTH; 		
		}
		$x_pos = 0;
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
	imagedestroy($sold_block);
	
	header ("Content-type: image/x-png");
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	imagepng($map);
	imagedestroy($map);

	foreach ($images as $img) {

		@imagedestroy($img);

	}
?>