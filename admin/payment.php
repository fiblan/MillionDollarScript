<?php
require("../config.php");
require ('admin_common.php');


?>
<BODY style="font-family: 'Arial', sans-serif; font-size:10pt;">
<?php



$dir = dirname(__FILE__);
$dir = preg_split ('%[/\\\]%', $dir);
$blank = array_pop($dir);
$dir = implode('/', $dir);

include $dir.'/payment/payment_manager.php';

list_avalable_payments ();

?>