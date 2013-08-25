<?php
define ('NO_HOUSE_KEEP', 'YES');

require ('config.php');

$BID = $_REQUEST['BID'];

if ($BID=='') {
	$BID = 1;
}



if ($DB_ERROR) {
	echo "Database configuration error: ".$DB_ERROR;
} else {
	show_map($BID);
}

?>
