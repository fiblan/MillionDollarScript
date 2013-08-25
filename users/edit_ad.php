<?php
session_start();
include ("../config.php");
require_once ("../include/ads.inc.php");

if ($_REQUEST['BID']!='') {
	$BID=$_REQUEST['BID'];
} else {
$BID = 1; # Banner ID. Change this later & allow users to select multiple banners

}

process_login();

require ("header.php");


?>

<?php

require ("footer.php");

?>