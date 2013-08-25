<?php
/**
 * @version		$Id: preview.php 62 2010-09-12 01:17:36Z ryan $
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
	<head>
<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">

	</head>
	
Please input admin password:<br>
<form method='post'>
<input type="password" name='pass'>
<input type="submit" value="OK">
</form>
	<?php

	die();

}
$BID = 1; # Banner ID. Change this later & allow users to select multiple banners
$sql = "select * from banners where banner_id=$BID";
$result = mysql_query ($sql) or die (mysql_error().$sql);
$b_row = mysql_fetch_array($result);
if ($_REQUEST[order_id]) {
	$_SESSION[MDS_order_id] = $_REQUEST[order_id];
}

$sql = "select block_id, status, user_id, url, alt_text FROM blocks where  status='sold' AND banner_id=$BID";
$result = mysql_query ($sql) or die (mysql_error());
while ($row=mysql_fetch_array($result)) {
	$blocks[$row[block_id]] = $row['status'];
	$owners[$row[block_id]] = $row['user_id'];
	
	
}
?>
<body style="margin:0px;">
The image:
<table border="0" cellpadding=0 cellspacing=0>
<tr><td nowrap>
<?php



for ($i=0; $i < $b_row['grid_height']; $i++) {
	//echo "<tr>";
	for ($j=0; $j < $b_row['grid_width']; $j++) {
		
		
		switch ($blocks[$cell]) {

			case 'sold':
	
					echo '<img style="cursor: pointer;cursor: hand;"  src="get_image.php?block_id='.$cell.'" width="10" height="10">';
				
				break;
			
			case '':
				echo "<img src='block.png'>";
		}
		
		$cell ++;
	}
	echo "<br>";
	
}


?>
</td>
</tr>
</table>
</body>

