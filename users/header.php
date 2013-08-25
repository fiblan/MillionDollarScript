<html>

<head>
<link rel='StyleSheet' type="text/css" href="style.css" >

<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<style type="text/css">


</style>

<title><?php echo SITE_NAME; ?></title>

</head>

<body>
<?php

if (USE_AJAX=='SIMPLE') {
	$order_page = 'order_pixels.php';
} else {
	$order_page = 'select.php';
}

?>
<img src="<?php echo SITE_LOGO_URL; ?>">
<div style='background-color: #ffffff; border-color:#C0C0C0; border-style:solid;padding:10px'>
<div class="menu_bar">
<a href="index.php" class="menu_bar"><?php echo $label['advertiser_header_nav1']; ?></a> | <a href="<?php echo $order_page; ?>" class="menu_bar"><?php echo $label['advertiser_header_nav2'];?></a>  | <a href="publish.php"  class="menu_bar"><?php echo $label['advertiser_header_nav3'];?></a> | <a href="orders.php"  class="menu_bar"><?php echo $label['advertiser_header_nav4'];?></a>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;  <?php
if ($_SESSION['MDS_ID']!='') { ?>
<a href='logout.php'  class="menu_bar" ><?php echo $label['advertiser_header_nav5']; ?></a>
<?php } ?>
</div>
