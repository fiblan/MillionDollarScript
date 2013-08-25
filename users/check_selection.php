<?php
/**
 * @version		$Id: check_selection.php 137 2011-04-18 19:48:11Z ryan $
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
// check the image selection.
require ("../config.php");

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

$BID = $f2->bid($_REQUEST['BID']);
load_banner_constants($BID);

// normalize...

$_REQUEST['map_x'] = floor ($_REQUEST['map_x'] / BLK_WIDTH )* BLK_WIDTH;
$_REQUEST['map_y'] = floor ($_REQUEST['map_y'] / BLK_HEIGHT)* BLK_HEIGHT;
$_REQUEST['block_id'] = floor ($_REQUEST['block_id']);
# place on temp order -> then 

//print_r($_REQUEST);

function place_temp_order($in_str, $price) {


	global $f2;

	if (session_id()=='') { return false; } // cannot place order if there is no session!
	$blocks = explode(',', $in_str);

	$quantity = sizeof($blocks)*(BLK_WIDTH*BLK_HEIGHT);

	$now = (gmdate("Y-m-d H:i:s")); 

	// preserve ad_id & block info...
	$sql = "SELECT ad_id, block_info  FROM temp_orders WHERE session_id='".addslashes(session_id())."' ";
	$result = mysql_query($sql) or die(mysql_error());
	$row = mysql_fetch_array($result);
	$ad_id = $row['ad_id'];
	$block_info = addslashes($row['block_info']);
	
	$BID = $f2->bid($_REQUEST['BID']);

	// DAYS_EXPIRE comes form load_banner_constants()
	$sql = "REPLACE INTO `temp_orders` ( `session_id` , `blocks` , `order_date` , `price` , `quantity` ,  `days_expire`, `banner_id` , `currency` ,  `date_stamp` , `ad_id`, `block_info` )  VALUES ('".addslashes(session_id())."', '".$in_str."', '".$now."', '0', '".$quantity."', '".DAYS_EXPIRE."', '".$BID."', '".get_default_currency()."',  '$now', '$ad_id', '$block_info' );";
	$f2->debug('Placed Temp order. '.$sql);
	mysql_query($sql) or die (mysql_error());

}



# reserves the pixels for the temp order..

$price_table ='';

function reserve_temp_order_pixels($block_info, $in_str) {

	global $f2, $label;

	if (session_id()=='') { return false; } // cannot reserve pixels if there is no session

	// check if it is free
	$BID = $f2->bid($_REQUEST['BID']);

	$sql = "select block_id from blocks where banner_id='".$BID."' and block_id IN($in_str) ";
	
	$result = mysql_query($sql) or die ($sql.mysql_error()); 
	if (mysql_num_rows($result)>0) {
		echo js_out_prep($label['check_sel_notavailable']." (E432)");
		//do_log_entry ($sql);
		return; 
	}

	

	$blocks = explode (',', $in_str);

	foreach ($block_info as $key=>$block) {

		//$price = get_zone_price($f2->bid($_REQUEST['BID']),  $block['map_y']/10, $block['map_x']/10);

		$price = get_zone_price($BID,  $block['map_y']/BLK_HEIGHT, $block['map_x']/BLK_WIDTH);

		$currency = get_default_currency();

		// enhance block info...

		$block_info[$key]['currency'] = $currency;
		$block_info[$key]['price'] = $price;
		$block_info[$key]['banner_id'] = $f2->bid($_REQUEST['BID']);

		$total += $price;

		//mysql_query ($sql) or die (mysql_error().$sql);
		//echo $key.", ";

	}

	//echo 'total:'.$total;
	//print_r($block_info);
	//$block_info = serialize($block_info);
	$sql = "UPDATE temp_orders set price='$total' where session_id='".session_id()."'  ";
	mysql_query($sql);
	//echo $sql;
	// save to file
	$fh = fopen (SERVER_PATH_TO_ADMIN.'temp/'."info_".md5(session_id()).".txt", 'wb');
	fwrite($fh, serialize($block_info));
	fclose($fh);
	

	mysql_query ($sql) or die (mysql_error().$sql);

}


#######################################################################
## MAIN 
#######################################################################
// return true, or false if the image can fit


check_selection_main();

function check_selection_main() {

	global $f2;

	# check the status of the block.


	###################################################
	if (USE_LOCK_TABLES == 'Y') {
		$sql = "LOCK TABLES blocks WRITE, temp_orders WRITE, currencies READ, prices READ, banners READ";
		$result = mysql_query ($sql) or die (" <b>Dear Webmaster: The current MySQL user does not have permission to lock tables. Please give this user permission to lock tables, or turn off locking in the Admin. To turn off locking in the Admin, please go to Main Config and look under the MySQL Settings.<b>");
	} else {
		// poor man's lock
		$sql = "UPDATE `config` SET `val`='YES' WHERE `key`='SELECT_RUNNING' AND `val`='NO' ";
		$result = mysql_query($sql) or die(mysql_error());
		if (mysql_affected_rows()==0) {
			// make sure it cannot be locked for more than 30 secs 
			// This is in case the proccess fails inside the lock
			// and does not release it.

			$unix_time = time();

			// get the time of last run
			$sql = "SELECT * FROM `config` where `key` = 'LAST_SELECT_RUN' ";
			$result = @mysql_query($sql);
			$t_row = @mysql_fetch_array($result);

			if ($unix_time > $t_row['val']+30) {
				// release the lock
				
				$sql = "UPDATE `config` SET `val`='NO' WHERE `key`='SELECT_RUNNING' ";
				$result = @mysql_query($sql) or die(mysql_error());

				// update timestamp
				$sql = "REPLACE INTO config (`key`, `val`) VALUES ('LAST_SELECT_RUN', '$unix_time')  ";
				$result = @mysql_query($sql) or die (mysql_error());
			}
			
			usleep(5000000); // this function is executing in another process. sleep for half a second
			check_selection_main (); 
			return;
		}


	}
	####################################################

	$upload_image_file = get_tmp_img_name ();


	$size = getimagesize($upload_image_file);
	$new_size = get_required_size($size[0], $size[1]);


	$block_id=$_REQUEST['block_id'];
	//print_r($_REQUEST);
	// get width and height of uploaded image
	//echo "[".$size[0]." ".$size[1]."] ";
	//echo $block_id;

	if (function_exists("imagecreatetruecolor")) {
		$dest = imagecreatetruecolor(BLK_WIDTH, BLK_HEIGHT);
		$whole_image = imagecreatetruecolor ($new_size[0], $new_size[1]);
	} else {
		$dest = imagecreate(BLK_WIDTH, BLK_HEIGHT);
		$whole_image = imagecreate ($new_size[0], $new_size[1]);
	}
	
	$parts = explode ('.', $upload_image_file);
	$ext = strtolower(array_pop($parts));
	//echo $ext."($upload_image_file)\n";
	switch ($ext) {
		case 'jpeg':
		case 'jpg':
			$upload_image = imagecreatefromjpeg ($upload_image_file);
			break;
		case 'gif':
			$upload_image = imagecreatefromgif ($upload_image_file);
			break;
		case 'png':
			$upload_image = imagecreatefrompng ($upload_image_file);
			break;
	}

	
	// create the requ

	//$imagebg = imageCreateFromPNG (SERVER_PATH_TO_ADMIN.'temp/block.png'); // transparent PNG
	//echo GRID_BLOCK;
	$imagebg = imageCreateFromstring (GRID_BLOCK);

	imageSetTile ($whole_image, $imagebg);
	imageFilledRectangle ($whole_image, 0, 0, $new_size[0], $new_size[1], IMG_COLOR_TILED);
	imagecopy ($whole_image, $upload_image, 0, 0, 0, 0, $size[0], $size[1] );
//imagepng($whole_image);

	for ($i=0; $i<($size[1]); $i+=BLK_HEIGHT) {
		
		for ($j=0; $j<($size[0]); $j+=BLK_WIDTH) {
			 
			$map_x = $j+$_REQUEST['map_x'];
			$map_y = $i+$_REQUEST['map_y'];

			$r_x = $map_x;
			$r_y = $map_y;

			//echo "map_x: $map_x map_y: $map_y \n";

			$GRD_WIDTH = BLK_WIDTH * G_WIDTH;
			$cb = (($map_x) / BLK_WIDTH) + (($map_y/BLK_HEIGHT) * ($GRD_WIDTH / BLK_WIDTH)) ;


			$in_str = $in_str."$comma$cb";
			$comma = ',';

			$block_info[$cb]['map_x'] = $map_x;
			$block_info[$cb]['map_y'] = $map_y;

			// bool imagecopy ( resource dst_im, resource src_im, int dst_x, int dst_y, int src_x, int src_y, int src_w, int src_h )
			imagecopy ( $dest, $whole_image, 0, 0, $j, $i, BLK_WIDTH,  BLK_HEIGHT);
			//echo "imagecopy ( $dest, $whole_image, 0, 0, $j, $i, ".BLK_HEIGHT.", '".BLK_WIDTH."' );";
			
			ob_start();
			imagepng($dest);
			$data = ob_get_contents();
			ob_end_clean();

			$data = base64_encode($data);

			$block_info[$cb]['image_data'] = $data;

		}

	}

	//

	imagedestroy($dest);
	imagedestroy($upload_image);
	//print_r ($block_info);

	// create a temporary order and place the blocks on a temp order

	place_temp_order($in_str, $price);
	//echo "in_str is:".$in_str;
	reserve_temp_order_pixels($block_info, $in_str);

	###################################################
			
	if (USE_LOCK_TABLES == 'Y') {
		$sql = "UNLOCK TABLES";
		$result = mysql_query ($sql) or die (mysql_error()." <b>Dear Webmaster: The current MySQL user set in config.php does not have permission to lock tables. Please give this user permission to lock tables, or set USE_LOCK_TABLES to N in the config.php file that comes with this script.<b>");
	} else {

		// release the poor man's lock
		$sql = "UPDATE `config` SET `val`='NO' WHERE `key`='SELECT_RUNNING' ";
		mysql_query($sql);

		$unix_time = time();

		// update timestamp
		$sql = "REPLACE INTO config (`key`, `val`) VALUES ('LAST_SELECT_RUN', '$unix_time')  ";
		$result = @mysql_query($sql) or die (mysql_error());


	}
	####################################################


}




?>