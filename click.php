<?php
/**
 * @version		$Id: click.php 162 2012-12-12 16:48:21Z ryan $
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


include ("config.php");
$block_id = $_REQUEST['block_id'];
if ($block_id=='') {
	die();
}
$BID=$f2->bid($_REQUEST['BID']);
if ($BID=='') {
	$BID=1;
}

$sql = "SELECT url, user_id from blocks where block_id='$block_id' AND banner_id='$BID' ";
$result = @mysql_query($sql);
$row = @mysql_fetch_array($result);

// basic click count.

$sql = "UPDATE users SET click_count = click_count + 1 where ID='".$row[user_id]."'  ";

$result = @mysql_query($sql);


//	echo "$BID - $date : $result :  $x :$sql";
if (ADVANCED_CLICK_COUNT=='YES') { 

	$date = gmdate(Y)."-".gmdate(m)."-".gmdate(d);
	$sql = "UPDATE clicks set clicks = clicks + 1 where banner_id='$BID' AND `date`='$date' AND `block_id`='".$block_id."'";
	$result = mysql_query($sql) ;
	$x = @mysql_affected_rows();
	
	if (!$x) {

		$sql = "INSERT into clicks (`banner_id`, `date`, `clicks`, `block_id`, `user_id`) VALUES('$BID', '$date', '1', '$block_id', '".$row[user_id]."') ";
		$result = @mysql_query($sql) ;
	}


}

// 

$sql = "UPDATE blocks SET click_count = click_count + 1 where block_id='".$block_id."' AND banner_id='$BID' ";
//echo $sql;
$result = mysql_query($sql);

header ("Location: http://".$row[url]);

?>