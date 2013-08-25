<?php
define ('NO_HOUSE_KEEP', 'YES');

require("../config.php");
require ('admin_common.php');

$BID = $_REQUEST['BID'];
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