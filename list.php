<?php
/**
 * @version		$Id: index.php 158 2012-10-04 15:23:39Z ryan $
 * @package		mds
 * @copyright	(C) Copyright 2010 Ryan Rhode, All rights reserved.
 * @author		Ryan Rhode, ryan@milliondollarscript.com
 * @license		This program is free software; you can redistribute it and/or modify
 *		it under the terms of the GNU General Public License as published by
 *		the Free Software Foundation; either version 3 of the License, or
 *		(at your option) any later version.
 *
 *		This program is distributed in the hope that it will be useful,
 *		but WITHOUT ANY WARRANTY; without even the implied warranty of
 *		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *		GNU General Public License for more details.
 *
 *		You should have received a copy of the GNU General Public License along
 *		with this program;  If not, see http://www.gnu.org/licenses/gpl-3.0.html.
 *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *
 *		Million Dollar Script
 *		A pixel script for selling pixels on your website.
 *
 *		For instructions see README.txt
 *
 *		Visit our website for FAQs, documentation, a list team members,
 *		to post any bugs or feature requests, and a community forum:
 * 		http://www.milliondollarscript.com/
 *
 */


// set the root path
define("MDSROOT", dirname(__FILE__));

// include the config file
include_once (MDSROOT . "/config.php");

// include the header
include_once (MDSROOT . "/html/header.php");

?>

<script src="<?php echo BASE_HTTP_PATH; ?>top_ads_js.php?BID=1"></script>

<?php include ('mouseover_box.htm'); ?>

<table style="width:100%;" border="0" cellSpacing="1" cellPadding="3"  bgColor="#d9d9d9">
<tr>
<td>
<font face="arial" size="2"><b>Date of Purchase</b></font>
</td>
<td>
<font face="arial" size="2"><b>Name</b></font>
</td>
<td>
<font face="arial" size="2"><b>Ads(s)</b></font>
</td>
<td>
<font face="arial" size="2"><b>Pixels</b></font>
</td>
</tr>

<?php
require_once ("include/ads.inc.php");

$sql = "SELECT *, MAX(order_date) as max_date, sum(quantity) AS pixels FROM orders where status='completed' AND approved='Y' AND published='Y' AND banner_id='$BID' GROUP BY user_id, banner_id order by pixels desc ";
$result = mysql_query($sql) or die(mysql_error());
while ($row=mysql_fetch_array($result)) {
	$q = "SELECT FirstName, LastName FROM users WHERE ID=" . $row['user_id'];
	$q = mysql_query($q) or die(mysql_error());
	$user = mysql_fetch_row($q);
?>
	<tr bgcolor="#ffffff" >
	<td>
	<font face="arial" size="2"><?php echo get_formatted_date(get_local_time($row['max_date'])); ?></font>
	</td>
	<td>
	<font face="arial" size="2"><?php echo $user['0'] . " " . $user['1']; ?></font>
	</td>
	<td>
	<font face="arial" size="2"><?php
		
		$sql = "Select * FROM  `ads` as t1, `orders` AS t2 WHERE t1.ad_id=t2.ad_id AND t1.banner_id='$BID' and t1.order_id > 0 AND t1.user_id='".$row['user_id']."' AND status='completed' AND approved='Y' ORDER BY `ad_date`";
		$m_result = mysql_query ($sql) or die(mysql_error());
		while ($prams=mysql_fetch_array($m_result, MYSQL_ASSOC)) {
			
			$ALT_TEXT = get_template_value('ALT_TEXT', 1);
			$ALT_TEXT = str_replace("'", "", $ALT_TEXT);
			$ALT_TEXT = (str_replace("\"", '', $ALT_TEXT));
			$js_str = "onmouseover=\"sB(event, '".$ALT_TEXT."', this, ".$prams['ad_id'].")\" onmousemove=\"sB(event, '".$ALT_TEXT."', this, ".$prams['ad_id'].")\" onmouseout=\"hI()\" ";
			echo $br.'<a target="_blank" '.$js_str.' href="'.get_template_value('URL', 1).'">'.get_template_value('ALT_TEXT', 1).'</a>';
			$br = '<br>';
		}

	?></font>
	</td>
	<td>
	<font face="arial" size="2"><?php 
		echo $row['pixels'];?></font>
	</td>
	</tr>
<?php

}

?>

</table>

<?php
include_once (MDSROOT . "/html/footer.php");
?>