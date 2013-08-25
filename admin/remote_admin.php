<?php

session_start();
require("../config.php");



if ($_REQUEST['key']!='') {

	$mykey = substr(md5(ADMIN_PASSWORD),1,15);

	if ($mykey == $_REQUEST['key']) {
		$_SESSION['ADMIN']='1'; // automatically log in
		$admin = true;
	}

}

if (!$admin) {
	require ('admin_common.php');
}



if ($_REQUEST['BID']!='') {
	$BID = $_REQUEST['BID'];
} else {
	$BID = 1;

}

load_banner_constants($BID);

$sql = "select * from banners where banner_id=$BID";
$result = mysql_query ($sql) or die (mysql_error().$sql);
$b_row = mysql_fetch_array($result);
$sql = "select * from users where ID=".$_REQUEST['user_id'];
$result = mysql_query ($sql) or die (mysql_error().$sql);
$u_row = mysql_fetch_array($result);

if ($_REQUEST['approve_links']!='') {

	//echo "Saving links...";
	if (sizeof($_REQUEST['urls'])>0) {
		//echo " * * *";
		$i=0;

		foreach ($_REQUEST['urls'] as $url) {
			$sql = "UPDATE blocks SET url='".$_REQUEST['new_urls'][$i]."', alt_text='".$_REQUEST['new_alts'][$i]."' WHERE user_id='".$_REQUEST['user_id']."' and url='$url' and banner_id='".$_REQUEST['BID']."'  ";
			//echo $sql."<br>";
			mysql_query ($sql) or die (mysql_error().$sql);
			$i++;
		}
		


	}
	// approve pixels
	$sql = "UPDATE blocks set approved='Y' WHERE user_id=".$_REQUEST['user_id']." AND banner_id=".$BID;
	mysql_query ($sql) or die (mysql_error().$sql);

	$sql = "UPDATE orders set approved='Y' WHERE user_id=".$_REQUEST['user_id']." AND banner_id=".$BID;
	mysql_query ($sql) or die (mysql_error().$sql);

	// process the image

	echo process_image($BID);
	publish_image($BID);
	process_map($BID);

	echo "<p><b>Links Approved, grid updated!</b></p>";

}

if ($_REQUEST['disapprove_links']!='') {

	$sql = "UPDATE blocks set approved='N' WHERE user_id=".$_REQUEST[user_id]." and banner_id=$BID";
	mysql_query ($sql) or die (mysql_error().$sql);

	$sql = "UPDATE orders set approved='N' WHERE user_id=".$_REQUEST[user_id]." and banner_id=$BID";
	mysql_query ($sql) or die (mysql_error().$sql);

	echo process_image($BID);
	publish_image($BID);
	process_map($BID);

	echo "<p><b>Links Disapproved, grid updated!</b></p>";


}

?>
<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
<b>Listing Links for:</b> <?php echo $u_row['LastName']." ".$u_row['FirstName'];?> (<?php echo $u_row['Username'];?>)
<input type="hidden" name="offset" value="<?php echo $_REQUEST['offset']; ?>">
<input type="hidden" name="BID" value="<?php echo $_REQUEST['BID']; ?>">
<input type="hidden" name="user_id" value="<?php echo $_REQUEST['user_id']; ?>">
<table>
<tr>
<td><b>URL</b></td>
<td><b>Alt Text</b></td>
</tr>

<?php

$sql = "SELECT alt_text, url, count(alt_text) AS COUNT, banner_id FROM blocks WHERE user_id=".$_REQUEST['user_id']."  $bid_sql group by url ";

$m_result = mysql_query ($sql);
$i=0;
while ($m_row=mysql_fetch_array($m_result)) {
	$i++;
	if ($m_row[url] !='') {
		echo "<tr><td>
		<input type='hidden' name='urls[]' value='".htmlspecialchars($m_row[url])."'>
		<input type='text' name='new_urls[]' size='40' value=\"".escape_html($m_row[url])."\"></td>
				<td><input name='new_alts[]' type='text' size='80' value=\"".escape_html($m_row[alt_text])."\"></td></tr>";
	}
}

?>

</table>

<input type="submit" value="Approve (OK)" name="approve_links"> | <input type="submit" value="Disapprove (No)" name="disapprove_links">
&nbsp; &nbsp;<a href="index.php">Go to Admin</a> | <a href='../users/login.php?Username=<?php echo $u_row['Username'];?>&Password=<?php echo ADMIN_PASSWORD; ?>' target='_blank'>Login to this Advertiser's Account</a>
</form>

<?php

echo "<iframe width=\"".(G_WIDTH*BLK_WIDTH)."\" height=\"".(G_HEIGHT*BLK_HEIGHT)."\" frameborder=0 marginwidth=0 marginheight=0 VSPACE=0 HSPACE=0 SCROLLING=no  src=\""."show_map.php?BID=$BID&user_id=".$_REQUEST['user_id']."\"></iframe>";
?>