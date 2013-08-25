<?php
/**
 * @version		$Id: check.php 62 2010-09-12 01:17:36Z ryan $
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

if ($_REQUEST['pass']!='') {

	if ($_REQUEST['pass']==ADMIN_PASSWORD) {
		$_SESSION[ADMIN] = '1';

	}

}
if ($_SESSION[ADMIN]=='') {

	?>
Please input admin password:<br>
<form method='post'>
<input type="password" name='pass'>
<input type="submit" value="OK">
</form>
	<?php

	die();

}

// select all the blocks...

$sql = "SELECT order_id, block_id, banner_id FROM blocks WHERE status <> 'nfs'"; // nfs blocks do not have an order.
$result = mysql_query($sql);

while ($row=mysql_fetch_array($result)) {

	$sql = "SELECT order_id FROM orders WHERE banner_id='".$row['banner_id']."' AND  order_id='".$row['order_id']."'";
	$result2 = mysql_query($sql) or die (mysql_error());
	if (mysql_num_rows($result2)==0) { // there is no order matching
		// delete the blocks.
		echo "Deleting block #".$row['block_id']."<br>";
		$sql = "DELETE from blocks WHERE block_id='".$row['block_id']."' AND banner_id='".$row['banner_id']."' ";
		mysql_query($sql);
		

	}

}

echo "Check Completed.";