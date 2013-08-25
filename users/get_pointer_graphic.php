<?php
/**
 * @version		$Id: get_pointer_graphic.php 137 2011-04-18 19:48:11Z ryan $
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

require ('../config.php');

$BID = $f2->bid($_REQUEST['BID']);

load_banner_constants($BID);

$user_id = $_SESSION['MDS_ID'];
$filename = get_tmp_img_name();
if (file_exists($filename)) {
	$handle = fopen ($filename, 'r');
	$contents = fread($handle, filesize($filename));
	$size = getimagesize($filename);
	fclose($handle);
	$image = imagecreatefromstring($contents);

} else {
	$image = imagecreatefrompng('pointer.png');
	$size = getimagesize('pointer.png');

}

$new_size = get_required_size($size[0], $size[1]);

$out = imagecreatetruecolor($new_size[0], $new_size[1]);

$imagebg = imageCreateFromPNG ('block.png'); // tile filler
imageSetTile ($out, $imagebg);
imageFilledRectangle ($out, 0, 0, $new_size[0], $new_size[1], IMG_COLOR_TILED);

imagecopy ($out, $image, 0, 0, 0, 0, $size[0], $size[1] );

if (MDS_RESIZE=='YES') { // make it smaller
	$newsize_img = imagecreate($new_size[0], $new_size[1]);
	imagecopyresampled ( $newsize_img, $image, 0, 0, 0, 0, $new_size[0], $new_size[1], $size[0], $size[1] );
	imagecopy ($out, $newsize_img, 0, 0, 0, 0, $new_size[0], $new_size[1] );
}

//);

//imagestring ($out, 2, 10, 10, 'hello'.$new_size[0]." - ".$new_size[1], 0 );

//imagedestroy($image);
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
header ("Content-type: image/x-png");
imagepng( $out);
imagedestroy($out);



?>