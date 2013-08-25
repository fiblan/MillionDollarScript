<?php
/**
 * @version		$Id: image_functions.php 153 2012-09-10 22:08:44Z ryan $
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

##################################################

function publish_image ($BID) {

	if (!is_numeric($BID)) { return false; }

	$BANNER_DIR = get_banner_dir();


	$file_path = SERVER_PATH_TO_ADMIN; // eg e:/apache/htdocs/ojo/admin/

	$p = preg_split ('%[/\\\]%', $file_path);
	 array_pop($p);
	 array_pop($p);
	
	$dest = implode('/', $p);
	$dest = $dest."/".$BANNER_DIR;

	
	if (OUTPUT_JPEG=='Y') {
		copy ($file_path."temp/temp$BID.jpg", $dest."main$BID.jpg");
		//echo "copy ".$file_path."temp/temp$BID.jpg, ".$dest."main$BID.jpg";
		//unlink ($file_path."temp/temp.png");

	} elseif (OUTPUT_JPEG=='N') {
	
		copy ($file_path."temp/temp$BID.png", $dest."main$BID.png");
		//unlink ($file_path."temp/temp.png");
	} elseif ((OUTPUT_JPEG=='GIF')) {
		copy ($file_path."temp/temp$BID.gif", $dest."main$BID.gif");
	}

	// output the tile image

	$b_row = load_banner_row($BID);

	if ($b_row['tile']=='') {
		$b_row['tile'] = get_default_image('tile');
	}
	$tile = imagecreatefromstring(base64_decode($b_row['tile']));
	imagegif($tile, $dest."bg-main$BID.gif");
	//imagepng($tile, $dest."bg-main$BID.gif");

	// update the records

	$sql = "SELECT * FROM blocks WHERE approved='Y' and status='sold' AND image_data <> '' AND banner_id='$BID' ";
	$r = mysql_query ($sql) or die (mysql_error().$sql);
	
	while ($row = mysql_fetch_array($r)) {

		

		// set the 'date_published' only if it was not set before, date_published can only be set once.
		$now = (gmdate("Y-m-d H:i:s"));
		$sql = "UPDATE orders set `date_published`='$now' where order_id='".$row['order_id']."' AND date_published IS NULL ";
		$result = mysql_query($sql) or die(mysql_error());

		// update the published status, always updated to Y

		$sql = "UPDATE orders set `published`='Y' where order_id='".$row['order_id']."'  ";
		$result = mysql_query($sql) or die(mysql_error());

		$sql = "UPDATE blocks set `published`='Y' where block_id='".$row['block_id']."' AND banner_id='$BID'";
		$result = mysql_query($sql) or die(mysql_error());


	}

	//Make sure to un-publish any blocks that are not approved...

	$sql = "SELECT block_id, order_id FROM blocks WHERE approved='N' AND status='sold' AND banner_id='$BID' ";
	//echo $sql;
	$result = mysql_query($sql) or die(mysql_error());
	while ($row = mysql_fetch_array($result)) {
		$sql = "UPDATE blocks set `published`='N' where block_id='".$row['block_id']."'  AND banner_id='$BID'  ";
		mysql_query($sql) or die(mysql_error());

		$sql = "UPDATE orders set `published`='N' where order_id='".$row['order_id']."'  AND banner_id='$BID'  ";
		mysql_query($sql) or die(mysql_error());

	}

	// update the time-stamp on the banner

	$sql = "UPDATE banners SET time_stamp='".time()."' WHERE banner_id='".$BID."' ";
	mysql_query($sql) or die(mysql_error());
	//echo $sql;

}

###################################################

function process_image($BID) {

	if (!is_numeric($BID)) { return false; }

	$BANNER_DIR = get_banner_dir();

	$sql = "select * from banners where banner_id='".$BID."'";
	$result = mysql_query ($sql) or die (mysql_error().$sql);
	$b_row = mysql_fetch_array($result);

	// initialize banner values:
	if (!$b_row['block_width']) { $b_row['block_width'] = 10;}
	if (!$b_row['block_height']) { $b_row['block_height'] = 10;}

	$BLK_WIDTH = $b_row['block_width'];
	$BLK_HEIGHT = $b_row['block_height'];
	$G_WIDTH = $b_row['grid_width'];
	$G_HEIGHT = $b_row['grid_height'];
	if (!$b_row['grid_block']) $b_row['grid_block'] = get_default_image('grid_block');
	if (!$b_row['nfs_block']) $b_row['nfs_block'] = get_default_image('nfs_block');

	$file_path = SERVER_PATH_TO_ADMIN;

	$progress .= 'Please wait.. Processing the Grid image with GD';

	if (function_exists("imagecreatetruecolor")) {
		$map = imagecreatetruecolor ( $G_WIDTH*$BLK_WIDTH, $G_HEIGHT*$BLK_HEIGHT );
	} else {
		$map = imagecreate ( $G_WIDTH*$BLK_WIDTH, $G_HEIGHT*$BLK_HEIGHT );
	}
	//$block = imagecreatefrompng ( $file_path."temp/block.png" );

	$block = imagecreatefromstring ( base64_decode($b_row['grid_block']) );

	

	// initialise the map, tile it with blocks
	$i=0; $j=0; $x_pos=0; $y_pos=0;

	for ($i=0; $i < $G_HEIGHT; $i++) {
		for ($j=0; $j < $G_WIDTH; $j++) {
			imagecopy ( $map, $block, $x_pos, $y_pos, 0, 0, $BLK_WIDTH, $BLK_HEIGHT );
			$x_pos += $BLK_WIDTH; 
		}
		$x_pos = 0;
		$y_pos += $BLK_HEIGHT;
		
	}

	# copy the NFS blocks.

	//$nfs_block = imagecreatefrompng ( $file_path."temp/not_for_sale_block.png" );
	$nfs_block = imagecreatefromstring ( base64_decode($b_row['nfs_block']) );
	$sql = "select * from blocks where status='nfs' AND banner_id='$BID' ";
	$result = mysql_query($sql) or die(mysql_error());

	while ($row = mysql_fetch_array($result)) {
		imagecopy ( $map, $nfs_block, $row['x'], $row['y'], 0, 0, $BLK_WIDTH, $BLK_HEIGHT );
	}

	imagedestroy($nfs_block);

	# blend in the background

	if (file_exists(SERVER_PATH_TO_ADMIN."temp/background$BID.png") && function_exists("imagealphablending")) {
		$background = imagecreatefrompng (SERVER_PATH_TO_ADMIN."temp/background$BID.png");
		imagealphablending($map, true);
		$MaxW = imagesx($background); //Edit by -J-
		$MaxH = imagesy($background); //Edit by -J-

		imagecopy($map, $background, 0, 0, 0, 0, $MaxW, $MaxH);
		imagedestroy ($background);
	}

	// crate a map form the images in the db
	
	$sql = "select * from blocks where approved='Y' and status='sold' AND image_data <> '' AND banner_id='$BID' ";
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
		
		imagecopy ( $map, $block, $row['x'], $row['y'], 0, 0, $BLK_WIDTH, $BLK_HEIGHT );
		imagedestroy ($block);
		

	}

	// save
//imagejpeg($map);
	if ((OUTPUT_JPEG == 'Y') && (function_exists("imagejpeg"))) {
		if (INTERLACE_SWITCH=='YES') {
			imageinterlace($map, 1);
		}
		if(!touch($file_path."temp/temp$BID.jpg")) {
			$progress .= "<b>Warning:</b> The script does not have permission write to " . $file_path . "temp/temp" . $BID . ".jpg or the directory does not exist<br>";
			
		}
		imagejpeg($map, $file_path."temp/temp$BID.jpg", JPEG_QUALITY);
		$progress .= "<br>Saved as ".$file_path."temp/temp$BID.jpg<br>";

	} elseif (OUTPUT_JPEG =='N') {

		if (INTERLACE_SWITCH=='YES') {
			imageinterlace($map, 1);
		}
		if(!touch($file_path."temp/temp$BID.png")) {
			$progress .= "<b>Warning:</b> The script does not have permission write to " . $file_path . "temp/temp" . $BID . ".png or the directory does not exist<br>";
			
		}
		imagepng($map, $file_path."temp/temp$BID.png");
		$progress .= "<br>Saved as ".$file_path."temp/temp$BID.png<br>";

	} elseif (OUTPUT_JPEG =='GIF') {

		if (INTERLACE_SWITCH=='YES') {
			imageinterlace($map, 1);
		}
		//$fh = fopen ($file_path."temp/temp$BID.gif", 'wb');
		//	echo 'touching '.$file_path."temp/temp$BID.gif<br>";
		if(!touch($file_path."temp/temp$BID.gif")) {
			$progress .= "<b>Warning:</b> The script does not have permission write to " . $file_path . "temp/temp" . $BID . ".gif or the directory does not exist<br>";
			
		}
		imagegif($map, $file_path."temp/temp$BID.gif");
		$progress .= "<br>Saved as ".$file_path."temp/temp$BID.gif<br>";
		//fclose($fh);

	}

	//imagepng($map, $file_path."temp/temp.png");

	imagedestroy($map);

	
	return $progress;



}

###################################################

function get_html_code($BID) {

	$sql = "select * from banners where banner_id='".$BID."'";
	$result = mysql_query ($sql) or die (mysql_error().$sql);
	$b_row = mysql_fetch_array($result);

	if (!$b_row['block_width']) $b_row['block_width'] = 10;
	if (!$b_row['block_height']) $b_row['block_height'] = 10;
	return "<iframe width=\"".($b_row['grid_width']*$b_row['block_width'])."\" height=\"".($b_row['grid_height']*$b_row['block_height'])."\" frameborder=0 marginwidth=0 marginheight=0 VSPACE=0 HSPACE=0 SCROLLING=no  src=\"".BASE_HTTP_PATH."display_map.php?BID=$BID\"></iframe>";


}

####################################################
function get_stats_html_code($BID) {

	//$sql = "select * from banners where banner_id=".$BID;
	//$result = mysql_query ($sql) or die (mysql_error().$sql);
	//$b_row = mysql_fetch_array($result);

	return "<iframe width=\"150\" height=\"50\" frameborder=0 marginwidth=0 marginheight=0 VSPACE=0 HSPACE=0 SCROLLING=no  src=\"".BASE_HTTP_PATH."display_stats.php?BID=$BID\" allowtransparency=\"true\" ></iframe>";


}

#########################################################


?>