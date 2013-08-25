<?php
session_start();
define ('NO_HOUSE_KEEP', 'YES');

require ('../config.php');

if ($_REQUEST['BID']=='') {
	$BID = 1;
} else {
	$BID = $_REQUEST['BID'];
}

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