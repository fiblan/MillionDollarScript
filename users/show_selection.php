<?php
/**
 * @version		$Id: show_selection.php 137 2011-04-18 19:48:11Z ryan $
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

load_banner_constants($BID);

//$sql = "select * from banners where banner_id='".$BID."'";
//$result = mysql_query ($sql) or die (mysql_error().$sql);
//$b_row = mysql_fetch_array($result);
$has_packages = banner_get_packages($BID);

#
# Preloaad all block
$sql = "select block_id, status, user_id FROM blocks where banner_id='$BID'  ";
$result = mysql_query ($sql) or die (mysql_error());
while ($row=mysql_fetch_array($result)) {
	$blocks[$row[block_id]] = $row['status'];
	if (($row[user_id] == $_SESSION['MDS_ID']) && ($row['status']!='ordered') && ($row['status']!='sold') && ($row['status']!='nfs')) {
		$blocks[$row[block_id]] = 'onorder';
		$order_exists = true;
	} elseif (($row['status']!='sold') && ($row[user_id] != $_SESSION['MDS_ID']) && ($row['status']!='nfs')) {
		$blocks[$row[block_id]] = 'reserved';

	}
	//echo $row[block_id]." ";
}
$cell =0;

if (function_exists("imagecreatetruecolor")) {
	$map = imagecreatetruecolor ( G_WIDTH*BLK_WIDTH, G_HEIGHT*BLK_HEIGHT );
} else {
	$map = imagecreate ( G_WIDTH*BLK_WIDTH, G_HEIGHT*BLK_HEIGHT );
}

	$block = imagecreatefromstring( USR_GRID_BLOCK );
	$selected_block = imagecreatefromstring ( USR_SEL_BLOCK);
	$sold_block = imagecreatefromstring ( USR_SOL_BLOCK );
	$reserved_block = imagecreatefromstring ( USR_RES_BLOCK );
	$ordered_block = imagecreatefromstring ( USR_ORD_BLOCK );
	$nfs_block = imagecreatefromstring ( USR_NFS_BLOCK );


	$cyan_block = imagecreate(BLK_WIDTH, BLK_HEIGHT);
	$color= ImageColorAllocate( $cyan_block, 255, 255, 255); 
	$color= ImageColorAllocate( $cyan_block, 0, 255, 255); 
	imagerectangle ( $cyan_block , 0, 0, BLK_WIDTH, BLK_HEIGHT, $color );

	$yellow_block = imagecreate(BLK_WIDTH,BLK_HEIGHT);
	$color= ImageColorAllocate( $yellow_block,  255, 255, 255);
	$color= ImageColorAllocate( $yellow_block,  255, 255, 0); 
	imagerectangle ( $yellow_block , 0, 0, BLK_WIDTH, BLK_HEIGHT, $color );

	$magenta_block = imagecreate(BLK_WIDTH,BLK_HEIGHT);
	$color= ImageColorAllocate( $magenta_block,  255, 255, 255);
	$color= ImageColorAllocate( $magenta_block, 255, 0, 255); 
	imagerectangle ( $magenta_block , 0, 0, BLK_WIDTH, BLK_HEIGHT, $color );

	//$white_block = imagecreate(10,10);
	//$color= ImageColorAllocate( $white_block,  255, 255, 255);
	//$color= ImageColorAllocate( $white_block, 255, 255, 255); 
	//imagerectangle ( $white_block , 0, 0, 10, 10, $color );

	// initialise the map, tile it with blocks
	$i=0; $j=0; $x_pos=0; $y_pos=0;

	for ($i=0; $i < G_HEIGHT; $i++) {
		for ($j=0; $j < G_WIDTH; $j++) {
			if (!$has_packages) { // ignore price zones if grid has packages
				$color = get_zone_color($BID, $i, $j);
			}
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
			$x_pos += BLK_WIDTH; 
		}
		$x_pos = 0;
		$y_pos += BLK_HEIGHT;
		
	}

	if ((file_exists(SERVER_PATH_TO_ADMIN."temp/background$BID.png") && (function_exists("imagealphablending")))) {
		$background = imagecreatefrompng (SERVER_PATH_TO_ADMIN."temp/background$BID.png");
		imagealphablending($map, true);
		$MaxW = imagesx($background); //Edit by -J-
		$MaxH = imagesy($background); //Edit by -J-

		imagecopy($map, $background, 0, 0, 0, 0, $MaxW, $MaxH);
		imagedestroy($background);
	} 

	// initialise the map, tile it with blocks
	$i=0; $j=0; $x_pos=0; $y_pos=0;
	//$color = get_block_color($BID, $cell);
	for ($i=0; $i < G_HEIGHT; $i++) {
		for ($j=0; $j < G_WIDTH; $j++) {
			

			switch ($blocks[$cell]) {

			case 'sold':
				imagecopy ( $map, $sold_block, $x_pos, $y_pos, 0, 0, BLK_WIDTH, BLK_HEIGHT );
				
				break;
			case 'reserved':
				imagecopy ( $map, $reserved_block, $x_pos, $y_pos, 0, 0, BLK_WIDTH, BLK_HEIGHT );
				break;
			case 'nfs':
				imagecopy ( $map, $nfs_block, $x_pos, $y_pos, 0, 0, BLK_WIDTH, BLK_HEIGHT );
				break;
			case 'ordered':
				imagecopy ( $map, $ordered_block, $x_pos, $y_pos, 0, 0, BLK_WIDTH, BLK_HEIGHT );
				break;

			case 'onorder':
				imagecopy ( $map, $selected_block, $x_pos, $y_pos, 0, 0, BLK_WIDTH, BLK_HEIGHT );
				
				break;
			case 'free':
			case '':

				
		
				//imagecopy ( $map, $block, $x_pos, $y_pos, 0, 0, 10, 10 );
			
			}
			$cell++;

			$x_pos += BLK_WIDTH; 
		

		}
		//$color = get_block_color($BID, $cell);
		$x_pos = 0;
		$y_pos += BLK_HEIGHT;
		
	}

	

	imagedestroy($block);
	imagedestroy($selected_block);
	imagedestroy($sold_block);
	imagedestroy($reserved_block);
	imagedestroy($ordered_block);
	imagedestroy($nfs_block);
	imagedestroy($cyan_block);
	imagedestroy($magenta_block);
	imagedestroy($yellow_block);

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