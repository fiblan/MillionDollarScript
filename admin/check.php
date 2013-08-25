<?php
require("../config.php");
require ('admin_common.php');

/*
COPYRIGHT 2008 - see www.milliondollarscript.com for a list of authors

This file is part of the Million Dollar Script.

Million Dollar Script is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Million Dollar Script is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with the Million Dollar Script.  If not, see <http://www.gnu.org/licenses/>.

*/
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