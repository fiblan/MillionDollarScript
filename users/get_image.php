<?php
/**
 * @version		$Id: get_image.php 137 2011-04-18 19:48:11Z ryan $
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

$BID = $f2->bid($_REQUEST['BID']);

if ($BID=='') {
	$BID=1;
}


require ('../config.php');


$sql = "SELECT * FROM blocks where block_id='".$_REQUEST['block_id']."' banner_id='$BID' ";
$result  = mysql_query ($sql) or die(mysql_error());
$row = mysql_fetch_array($result, MYSQL_ASSOC);



if ($row[image_data]=='') {

	if ($row['status']=="sold") {
		$file_name = 'ordered_block.png';

		# hard coded above file to save a file read...

		$data = "iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAABGdBTUEAALGPC/xhBQAAABZJREFUKFNj/N/gwIAHAKXxIIYRKg0AB3qe55E8bNQAAAAASUVORK5CYII=";

	} else {
		$file_name = "block.png";

		# hard coded above file to save a file read...

		$data = "iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAABGdBTUEAALGPC/xhBQAAABdJREFUKFNjvHLlCgMeAJT+jxswjFBpAOAoCvbvqFc9AAAAAElFTkSuQmCC";

	}
	header ("Content-type: image/x-png");
	echo base64_decode($data);
	//$file = fopen ($file_name, 'r');
	//$data = fread ($file, filesize($file_name));
	//echo base64_encode($data);
	//fclose ($file);

} else {
	header ("Content-type: ".$row[mime_type]);
	
	echo base64_decode( $row[image_data]);

}

?>
