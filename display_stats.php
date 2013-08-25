<?php
define ('NO_HOUSE_KEEP', 'YES');

require ('config.php');
/*
COPYRIGHT 2008 - see www.milliondollarscript.com for a list of authors

This file is part of the Million Dollar Script.

Million Dollar Script is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Million Dollar Script is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with the Million Dollar Script.  If not, see <http://www.gnu.org/licenses/>.

*/

if ($_REQUEST['BID']!='') {
	$BID = $_REQUEST['BID'];
} else {
	$BID = 1;
}

load_banner_constants($BID);

$sql = "select * from banners where banner_id='$BID'";
$result = mysql_query ($sql) or die (mysql_error().$sql);
$b_row = mysql_fetch_array($result);

$sql = "select count(*) AS COUNT FROM blocks where status='sold' and banner_id='$BID' ";
$result = mysql_query ($sql);
$row = mysql_fetch_array($result);
$sold = $row['COUNT']*(BLK_WIDTH*BLK_HEIGHT);

$sql = "select count(*) AS COUNT FROM blocks where status='nfs' and banner_id='$BID' ";
$result = mysql_query ($sql);
$row = mysql_fetch_array($result);
$nfs = $row['COUNT']*(BLK_WIDTH*BLK_HEIGHT);

$available = (($b_row[grid_width] * $b_row[grid_height] * (BLK_WIDTH*BLK_HEIGHT) )-$nfs ) - $sold;

if ($label['sold_stats']=='') {
	$label['sold_stats']="Sold";
}

if ($label['available_stats']=='') {
	$label['available_stats']="Available";
}

?>
<html>
<link rel=StyleSheet type="text/css" href="main.css" >
<body  class="status_body">

<div class="status">
<b><?php echo $label['sold_stats']; ?>:</b> <?php echo number_format($sold); ?><br><b><?php echo $label['available_stats']; ?>:</b> <?php echo number_format($available); ?><br>
</div>
</body>
</html>