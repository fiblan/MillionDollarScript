<?php
/**
 * @version		$Id: get_pointer_image2.php 137 2011-04-18 19:48:11Z ryan $
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

require ('../config.php');

// get the order id
$sql = "SELECT * FROM blocks where block_id='".$_REQUEST['block_id']."' and banner_id='".$f2->bid($_REQUEST['BID'])."' ";
$result = mysql_query($sql) or die(mysql_error());
$row = mysql_fetch_array($result);
// load all the blocks wot
$sql = "select * from blocks where order_id='".$row['order_id']."' ";
$result3 = mysql_query($sql) or die(mysql_error());


load_banner_constants($f2->bid($_REQUEST['BID']));

//echo $sql;


// find high x, y & low x, y
// low x,y is the top corner, high x,y is the bottom corner

while ($block_row = mysql_fetch_array($result3)) {

	if ($high_x=='') {
		$high_x = $block_row['x'];
		$high_y = $block_row['y'];
		$low_x = $block_row['x'];
		$low_y = $block_row['y'];

	}

	if ($block_row['x'] > $high_x) {
		$high_x = $block_row['x'];
	}

	if ($block_row['y'] > $high_y) {
		$high_y = $block_row['y'];
	}

	if ($block_row['y'] < $low_y) {
		$low_y = $block_row['y'];
	}

	if ($block_row['x'] < $low_x) {
		$low_x = $block_row['x'];
	}

	
//echo "i is $i ".$block_row['block_id']." (x:".$block_row['x'].",y:".$block_row['y'].")<br>";
	$blocks[$i]['block_id'] = $block_row['block_id'];
	$blocks[$i]['image_data'] = imagecreatefromstring ( base64_decode($block_row['image_data']));
	imagetruecolortopalette($blocks[$i]['image_data'], false, 256);
	$blocks[$i]['x'] = $block_row['x'];
	$blocks[$i]['y'] = $block_row['y'];

	$i++;

}

//echo "high: $high_x, $high_y low: $low_x, $low_y<br>".BLK_WIDTH;

//print_r($blocks);
$x_size = ($high_x + BLK_WIDTH) - $low_x;
$y_size = ($high_y + BLK_HEIGHT) - $low_y;
//$x_size = ($high_x - $low_x)+BLK_WIDTH;
//$y_size = ($high_y - $low_y)+BLK_HEIGHT; 

//echo "size:".sizeof($blocks)."<br>";

foreach ($blocks as $block) {

	$id = ($block['x']-$low_x).($block['y']-$low_y);
	$new_blocks[$id] = $block;
	//imagedestroy($block['image_data']);

}

//echo "xs: $x_size ys $y_size<br>";
//$std_image =  "iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAABGdBTUEAALGPC/xhBQAAABdJREFUKFNjvHLlCgMeAJT+jxswjFBpAOAoCvbvqFc9AAAAAElFTkSuQmCC";

$std_image = imagecreatefromstring(GRID_BLOCK);
//
//if (function_exists("imagecreatetruecolor")) {
//	$image = imagecreatetruecolor ( $x_size, $y_size  );
	
//} else {
$image = imagecreate ( $x_size, $y_size );
imagetruecolortopalette($image, false, 256);
$trans = imagecolorallocate($image,0,0,0);
imagecolortransparent($image , $trans);
//}
# imagecopy ( $image, $blocks[1], int dst_x, int dst_y, int src_x, int src_y, int src_w, int src_h )
//imagecopy ( $image, $blocks[1]['image_data'], 0, 0, 0, 0, BLK_WIDTH, BLK_HEIGHT );




$block_count =0;

for ($i=0; $i<$y_size; $i+=BLK_HEIGHT) {
	for ($j=0; $j<$x_size; $j=$j+BLK_WIDTH) {
		//echo $j.$i."<br>";
		if ($new_blocks["$j$i"]['image_data']!='') {
			imagecopy ($image, $new_blocks["$j$i"]['image_data'], $j, $i, 0, 0, BLK_WIDTH, BLK_HEIGHT );
			imagedestroy($new_blocks["$j$i"]['image_data']);
			//echo "copy block..<br>";
		} else {
			imagefilledrectangle  ( $image, $j, $i, $j+BLK_WIDTH, $i+BLK_HEIGHT, $trans );
			//imagecopy ( $image, $std_image, $j, $i, 0, 0, 10, 10 );
			//echo "copy std..";

		}
	}

}

imagedestroy($std_image);

//imageline ( $image, 0, 0, int x2, int y2, 0 );
$c=imagecolorallocate($image, 0, 0, 0);
//echo "0, 0, $size_x, $size_y, $c";
imagerectangle ( $image, 0, 0, $x_size-1, $y_size-1, $c);
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

header ("Content-type: image/gif");
imagegif( $image);

?>