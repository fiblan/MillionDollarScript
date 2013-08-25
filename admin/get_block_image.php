<?php
// accepts $_REQUEST['BID'] and $_REQUEST['image_name']
define ('NO_HOUSE_KEEP', 'YES');

require ('../config.php');

$row = load_banner_constants($_REQUEST['BID']);

$image = $row[$_REQUEST['image_name']];

if ($image=='') {
	$image = get_default_image($_REQUEST['image_name']);
}


header("Cache-Control: max-age=60, must-revalidate"); // HTTP/1.1

header("Expires: ".gmdate('r',time()+60)); // Date in the past

header ("Content-type: image/x-png");
echo base64_decode($image);

?>