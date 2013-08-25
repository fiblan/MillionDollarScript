<?php
/**
 * @version		$Id: preview_blend.php 137 2011-04-18 19:48:11Z ryan $
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

$BID = $f2->bid($_REQUEST['BID']);
if ($BID =='') {
	$BID=1;

}
$sql = "select * from banners where banner_id=$BID";
$result = mysql_query ($sql) or die (mysql_error().$sql);
$b_row = mysql_fetch_array($result);


if (function_exists("imagecreatetruecolor")) {
	$map = imagecreatetruecolor ( $b_row[grid_width]*10, $b_row[grid_height]*10 );
} else {

	echo "Your GD library does not support alpha blending, please upgrade to GD2 ";
	die();

}
if (file_exists(SERVER_PATH_TO_ADMIN."temp/background$BID.png")) {
	$background = imagecreatefrompng ("temp/background$BID.png");
	imagealphablending($map, true);
} else {
	$background = imagecreatetruecolor ( $b_row[grid_width]*10, $b_row[grid_height]*10 );
	imagealphablending($map, false);
}


$block = imagecreatefrompng ( $file_path."temp/block.png" );



$i=0; $j=0; $x_pos=0; $y_pos=0;

	for ($i=0; $i < $b_row[grid_height]; $i++) {
		for ($j=0; $j < $b_row[grid_width]; $j++) {
			imagecopy ( $map, $block, $x_pos, $y_pos, 0, 0, 10, 10 );
			//echo "$map, $block, $x_pos, $y_pos, 0, 0, 10, 10 ($i $j)<br>";
			// bool imagecopy ( resource dst_im, resource src_im, int dst_x, int dst_y, int src_x, int src_y, int src_w, int src_h )

			$x_pos += 10; 
		

		}
		$x_pos = 0;
		$y_pos += 10;
		
}
# copy the NFS blocks.

	$nfs_block = imagecreatefrompng ( $file_path."temp/not_for_sale_block.png" );
	$sql = "select * from blocks where status='nfs' and banner_id=$BID ";
	$result = mysql_query($sql) or die(mysql_error());

	while ($row = mysql_fetch_array($result)) {
		imagecopy ( $map, $nfs_block, $row['x'], $row['y'], 0, 0, 10, 10 );
	}

	imagedestroy($nfs_block);
$MaxW = imagesx($background); //Edit by -J-
$MaxH = imagesy($background); //Edit by -J-

imagecopy($map, $background, 0, 0, 0, 0, $MaxW, $MaxH);


if ((OUTPUT_JPEG == 'Y') && (function_exists("imagejpeg"))) {
		//imageinterlace($map, 1);
		header ("Content-type: image/jpg");
		imagejpeg($map, '', JPEG_QUALITY);

	} else {
		header ("Content-Type: image/png");
		imagepng($map);
	}
imagedestroy($map);
imagedestroy($background);
imagedestroy($block);



?>