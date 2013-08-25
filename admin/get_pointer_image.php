<?php
define ('NO_HOUSE_KEEP', 'YES');

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

require ('../config.php');

$sql = "SELECT * FROM blocks where block_id='".$_REQUEST['block_id']."' and banner_id='".$_REQUEST['BID']."' ";

$result = mysql_query($sql) or die(mysql_error());
$row = mysql_fetch_array($result);

if ($row['image_data']=='') {
	load_banner_constants($_REQUEST['BID']);
	$row['image_data'] = base64_encode(GRID_BLOCK);
	//$row['image_data'] =  "iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAABGdBTUEAALGPC/xhBQAAABZJREFUKFNj/N/gwIAHAKXxIIYRKg0AB3qe55E8bNQAAAAASUVORK5CYII=";
}

header ("Content-type: image/x-png");
echo base64_decode($row['image_data']);


?>