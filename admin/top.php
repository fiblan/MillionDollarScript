<?php
/**
 * @version		$Id: top.php 137 2011-04-18 19:48:11Z ryan $
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

require("../config.php");
require ('admin_common.php');

$BID = $f2->bid($_REQUEST['BID']);

$bid_sql = " AND banner_id=$BID ";

if (($BID=='all') || ($BID=='')) { 
	$BID=''; 
	$bid_sql = "  ";
	
} 

$sql = "Select * from banners ";
$res = mysql_query($sql);
?>
<form name="bidselect" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
<input type="hidden" name="old_order_id" value="<?php echo $order_id;?>">
Select grid: <select name="BID" onchange="document.bidselect.submit()">
	<option value='all' <?php if ($f2->bid($_REQUEST['BID'])=='all') { echo 'selected'; } ?>>Show All</option>
	<?php
	while ($row=mysql_fetch_array($res)) {
		
		if (($row['banner_id']==$BID) && ($f2->bid($_REQUEST['BID'])!='all')) {
			$sel = 'selected';
		} else {
			$sel ='';

		}
		echo '<option '.$sel.' value='.$row['banner_id'].'>'.$row[name].'</option>';
	}
	?>
</select>
</form>
<hr>
<p>
Here is the list of the top clicks. You may copy and paste this list onto your website.
</p>

<table width="100%" border="0" cellSpacing="1" cellPadding="3" align="center" bgColor="#d9d9d9">

<tr >
<td>
<font face="arial" size="2"><b>Advertiser's Link</b></font>
</td>
<td>
<font face="arial" size="2"><b>Blocks</b></font>
</td>
<td>
<font face="arial" size="2"><b>Clicks</b></font>
</td>
</tr>

<?php

//$sql = "SELECT *, DATE_FORMAT(MAX(order_date), '%Y-%c-%d') as max_date, sum(quantity) AS pixels FROM orders where status='completed' $bid_sql GROUP BY user_id, banner_id order by pixels desc ";

//$sql = "SELECT *, DATE_FORMAT(MAX(order_date), '%Y-%c-%d') as max_date, sum(quantity) AS pixels FROM orders where status='completed' $bid_sql GROUP BY user_id, banner_id order by pixels desc ";

$sql = "SELECT *, sum(click_count) as clicksum, count(order_id) as b from blocks WHERE status='sold' AND image_data <> '' $bid_sql group by url order by clicksum desc ";

//echo $sql;

$result = mysql_query($sql) or die(mysql_error());


while ($row=mysql_fetch_array($result)) {

?>
	<tr bgcolor="#ffffff" >
	<td>
	<font face="arial" size="2"><?php

	echo "<a href='".$row[url]."' target='_blank' >".$row[alt_text]."</a>";

	/*
	
$sql = "SELECT alt_text, url, count(alt_text) AS COUNT FROM blocks WHERE user_id=".$row[user_id]." and banner_id=".$row[banner_id]." group by url ";

		$m_result = mysql_query ($sql);
		while ($m_row=mysql_fetch_array($m_result)) {
			if ($m_row[url] !='') {
				echo "<a href='".$m_row[url]."' target='_blank' >".$m_row[alt_text]."</a> <br>";
			} else {
				

			}
		}
		if (mysql_num_rows($m_result)==0) {
			echo "[not yet]";

		}

		*/

	?></font>
	</td>
	<td>
	<font face="arial" size="2"><?php echo $row[b]; ?></font>
	</td>
	
	<td>
	<font face="arial" size="2"><?php echo $row[clicksum];?></font>
	</td>
	</tr>
<?php

}

?>

</table>

</font>