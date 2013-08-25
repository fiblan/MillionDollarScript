<?php
session_start();
define ('NO_HOUSE_KEEP', 'YES');

require ('../config.php');
//include ("login_functions.php");
require ('admin_common.php');

//process_login();

// get the order id
if ($_REQUEST['block_id']!='') {
	$sql = "SELECT * FROM blocks where block_id='".$_REQUEST['block_id']."' and banner_id='".$_REQUEST['BID']."' ";
	
} elseif ($_REQUEST['aid']!='') {
	$sql = "SELECT * FROM ads where ad_id='".$_REQUEST['aid']."' ";

}

$result = mysql_query($sql) or die(mysql_error());
$row = mysql_fetch_array($result);
// load all the blocks wot
$sql = "select * from blocks where order_id='".$row['order_id']."' ";
$result3 = mysql_query($sql) or die(mysql_error());
//echo $sql;

load_banner_constants($_REQUEST['BID']);
$blocks = array();

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

	$blocks[$i]['block_id'] = $block_row['block_id'];
	if ($block_row['image_data']=='') {
		$blocks[$i]['image_data'] = imagecreatefromstring(GRID_BLOCK);
	} else {
		$blocks[$i]['image_data'] = imagecreatefromstring ( base64_decode($block_row['image_data']));

	}
	imagetruecolortopalette($blocks[$i]['image_data'], false, 256);
	$blocks[$i]['x'] = $block_row['x'];
	$blocks[$i]['y'] = $block_row['y'];

	$i++;

}

$x_size = ($high_x + BLK_WIDTH) - $low_x;
$y_size = ($high_y + BLK_HEIGHT) - $low_y;



foreach ($blocks as $block) {
	$id = ($block['x']-$low_x).($block['y']-$low_y);
	$new_blocks[$id] = $block;
}


$std_image = imagecreatefromstring(GRID_BLOCK);

$image = imagecreate ( $x_size, $y_size );
imagetruecolortopalette($image, false, 256);
$trans = imagecolorallocate($image,0,0,0);
imagecolortransparent($image , $trans);

$block_count =0;

for ($i=0; $i<$y_size; $i+=BLK_HEIGHT) {
	for ($j=0; $j<$x_size; $j=$j+BLK_WIDTH) {
		if ($new_blocks["$j$i"]['image_data']!='') {
			imagecopy ($image, $new_blocks["$j$i"]['image_data'], $j, $i, 0, 0, BLK_WIDTH, BLK_HEIGHT );
			imagedestroy($new_blocks["$j$i"]['image_data']);	
		} else {
			imagefilledrectangle  ( $image, $j, $i, $j+BLK_WIDTH, $i+BLK_HEIGHT, $trans );
		}
	}

}

imagedestroy($std_image);

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past

header ("Content-type: image/gif");
imagegif( $image);

?>