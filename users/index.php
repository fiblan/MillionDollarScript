<?php
session_start();
include ("../config.php");
include ("login_functions.php");

process_login();

require ("header.php");

$sql = "select block_id from blocks where user_id='".$_SESSION['MDS_ID']."' and status='sold' ";
$result = mysql_query($sql) or die(mysql_error());
$pixels = mysql_num_rows($result) * 100;

$sql = "select block_id from blocks where user_id='".$_SESSION['MDS_ID']."' and status='ordered' ";
$result = mysql_query($sql) or die(mysql_error());
$ordered = mysql_num_rows($result) * 100;

$sql = "select * from users where ID='".$_SESSION['MDS_ID']."' ";
$result = mysql_query($sql) or die(mysql_error());
$user_row = mysql_fetch_array($result);

?>
<h3><?php echo $label['advertiser_home_welcome'];?></h3>
<p>
<?php echo $label['advertiser_home_line2']."<br>"; ?>
<p>
<p>
<?php
$label['advertiser_home_blkyouown'] = str_replace("%PIXEL_COUNT%", $pixels, $label['advertiser_home_blkyouown']);
echo $label['advertiser_home_blkyouown']."<br>";

$label['advertiser_home_blkonorder'] = str_replace("%PIXEL_ORD_COUNT%", $ordered, $label['advertiser_home_blkonorder']);


if (USE_AJAX=='SIMPLE') {
	$label['advertiser_home_blkonorder'] = str_replace('select.php', 'order_pixels.php', $label['advertiser_home_blkonorder']);
} 
echo $label['advertiser_home_blkonorder']."<br>";

$label['advertiser_home_click_count'] = str_replace("%CLICK_COUNT%", number_format($user_row['click_count']), $label['advertiser_home_click_count']);
echo $label['advertiser_home_click_count']."<br>";
?>
</p>

<h3><?php echo $label['advertiser_home_sub_head']; ?></h3>
<p>
<?php 

if (USE_AJAX=='SIMPLE') {
	$label['advertiser_home_selectlink'] = str_replace('select.php', 'order_pixels.php', $label['advertiser_home_selectlink']);
} 

echo $label['advertiser_home_selectlink']; ?><br>
<?php echo $label['advertiser_home_managelink']; ?><br>
<?php echo $label['advertiser_home_ordlink']; ?><br>
<?php echo $label['advertiser_home_editlink']; ?><br>
</p>
<p>
<?php echo $label['advertiser_home_quest']; ?> <?php echo SITE_CONTACT_EMAIL; ?>
</p>

<?php

require ("footer.php");
?>