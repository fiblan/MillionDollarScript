<?php
/**
 * @version		$Id: expr.php 62 2010-09-12 01:17:36Z ryan $
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

// email expiration warnings?

	if (EMAIL_USER_EXPIRE_WARNING=='YES') {

		$now = (gmdate("Y-m-d H:i:s"));

		//echo $now;

		//$now = "2006-01-14 04:35:58";

		//$sql = "SELECT * from orders, banners where status='completed' and orders.banner_id=banners.banner_id AND banners.days_expire <> 0 AND DATE_SUB('$now',INTERVAL banners.days_expire DAY) >= DATE_SUB(orders.date_published, INTERVAL 3 DAY) AND orders.date_published IS NOT NULL AND expiry_notice_sent='N' ";

		$sql = "SELECT * from orders, banners where status='completed' and orders.banner_id=banners.banner_id AND banners.days_expire <> 0 AND DATE_SUB('$now',INTERVAL banners.days_expire+5 DAY) >= orders.date_published AND orders.date_published IS NOT NULL AND expiry_notice_sent <> 'Y' ";

		//$sql = "SELECT * FROM orders where status='completed' ";

		echo $sql;

		$result = mysql_query($sql) or die (mysql_error());

		echo "Advertisers to email: ".mysql_num_rows($result);

		?>
<table width="100%" cellSpacing="1" cellPadding="3" align="center" bgColor="#d9d9d9" border="0">
		<tr>
<td><b><font face="Arial" size="2"><input type="checkbox" onClick="checkBoxes(this, 'orders[]');"></td>
    <td><b><font face="Arial" size="2">Order Date</font></b></td>
    <td><b><font face="Arial" size="2">Customer Name</font></b></td>
    <td><b><font face="Arial" size="2">Username & ID</font></b></td>
	<td><b><font face="Arial" size="2">OrderID</font></b></td>
	<td><b><font face="Arial" size="2">Grid</font></b></td>
	<td><b><font face="Arial" size="2">Quantity</font></b></td>
	<td><b><font face="Arial" size="2">Amount</font></b></td>
	<td><b><font face="Arial" size="2">Status</font></b></td>
</tr>

		<?php

		while ($row = mysql_fetch_array($result)) {

			?>

			<tr onmouseover="old_bg=this.getAttribute('bgcolor');this.setAttribute('bgcolor', '#FBFDDB', 0);" onmouseout="this.setAttribute('bgcolor', old_bg, 0);" bgColor="#ffffff">
	<td><input type="checkbox" name="orders[]" value="<?php echo $row[order_id]; ?>"></td>
	<td><font face="Arial" size="2"><?php echo $row[order_date];?></font></td>
	<td><font face="Arial" size="2"><?php echo $row[FirstName]." ".$row[LastName];?></font></td>
    <td><font face="Arial" size="2"><?php echo $row[Username];?> (#<?php echo $row[ID];?>)</font></td>
	<td><font face="Arial" size="2">#<?php echo $row[order_id];?></font></td>
	<td><font face="Arial" size="2"><?php 

		$sql = "select * from banners where banner_id=".$row['banner_id'];
$b_result = mysql_query ($sql) or die (mysql_error().$sql);
$b_row = mysql_fetch_array($b_result);
		
		echo $b_row['name'];
		
	?></font></td>
	<td><font face="Arial" size="2"><?php echo $row['quantity'];?></font></td>
	<td><font face="Arial" size="2"><?php echo convert_to_default_currency_formatted($row['currency'], $row[price])?></font></td>
	<td><font face="Arial" size="2"><?php echo $label[$row['status']];?></font></td>
	</tr>

	<?php

		}

	} else {

		echo "Expiration warnings not enabled. You can enable them form Main Config.";


}

?>