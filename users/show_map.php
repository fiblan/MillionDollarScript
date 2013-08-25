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

session_start();
define ('NO_HOUSE_KEEP', 'YES');

require ("../config.php");

$BID = $f2->bid($_REQUEST['BID']);

$sql = "select * from banners where banner_id='".$BID."'";
$result = mysql_query ($sql) or die (mysql_error().$sql);
$b_row = mysql_fetch_array($result);

load_banner_constants($BID);

# Preload all block
$sql = "select block_id, status, user_id, image_data FROM blocks where status='sold' AND user_id='".$_SESSION['MDS_ID']."' and banner_id='$BID'";
//echo $sql;
$result = mysql_query ($sql) or die (mysql_error().$sql);
while ($row=mysql_fetch_array($result)) {
	$blocks[$row[block_id]] = $row['status'];
	if (($row[user_id] == $_SESSION['MDS_ID']) && ($row['status']!='ordered') && ($row['status']!='sold')) {
		$blocks[$row[block_id]] = 'onorder';
		$order_exists = true;
	} elseif (($row['status']!='sold') && ($row[user_id] != $_SESSION['MDS_ID']) ) {
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
	$block = imagecreatefromstring ( USR_GRID_BLOCK );
	//$selected_block = imagecreatefrompng ( "selected_block.png" );
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
				imagecopy ( $map, $sold_block, $x_pos, $y_pos, 0, 0, BLK_WIDTH, BLK_HEIGHT );
				
			} else {
				imagecopy ( $map, $block, $x_pos, $y_pos, 0, 0, BLK_WIDTH, BLK_HEIGHT );

			}

			
			$cell++;

			$x_pos += BLK_WIDTH; 
		

		}
		$x_pos = 0;
		$y_pos += BLK_HEIGHT;
		
	}

	# copy the NFS blocks.

	$nfs_block = imagecreatefrompng ( "not_for_sale_block.png" );
	$sql = "select * from blocks where status='nfs' and banner_id='$BID' ";
	$result = mysql_query($sql) or die(mysql_error());

	while ($row = mysql_fetch_array($result)) {
		imagecopy ( $map, $nfs_block, $row['x'], $row['y'], 0, 0, BLK_WIDTH, BLK_HEIGHT );
	}

	imagedestroy($nfs_block);


	if (file_exists(SERVER_PATH_TO_ADMIN.'temp/background.png') && (function_exists("imagealphablending"))) {
		$background = imagecreatefrompng (SERVER_PATH_TO_ADMIN."temp/background.png");
		imagealphablending($map, true);
		$MaxW = imagesx($background); //Edit by -J-
		$MaxH = imagesy($background); //Edit by -J-

		imagecopy($map, $background, 0, 0, 0, 0, $MaxW, $MaxH);
		imagedestroy($background);
	} 

	imagedestroy($block);
	imagedestroy($sold_block);
	
	if ((OUTPUT_JPEG == 'Y') && (function_exists("imagejpeg"))) {
		//imageinterlace($map, 1);
		header ("Content-type: image/jpg");
		imagejpeg($map, '', JPEG_QUALITY);

	} elseif (OUTPUT_JPEG=='N') {
		header ("Content-type: image/x-png");
		imagepng($map);

	} elseif (OUTPUT_JPEG=='GIF') {
		header ("Content-type: image/gif");
		imagepng($map);
	}
	imagedestroy($map);
?>