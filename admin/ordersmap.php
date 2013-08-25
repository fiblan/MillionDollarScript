<?php
/**
 * @version		$Id: ordersmap.php 137 2011-04-18 19:48:11Z ryan $
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

ini_set('max_execution_time', 10000);
require("../config.php");
require ('admin_common.php');

if ($f2->bid($_REQUEST['BID'])!='') {
	$BID = $f2->bid($_REQUEST['BID']);
} else {
	$BID = 1;

}
load_banner_constants($BID);

//$sql = "select * from banners where banner_id=$BID";
//$result = mysql_query ($sql) or die (mysql_error().$sql);
//$b_row = mysql_fetch_array($result);

?>
The following screen shows a map of all the orders made on a grid. Move your mouse over the blocks to find who owns the order. Click on the block to manage the order.<br>
Red blocks are on order (Status can be: 'reserved', 'ordered', 'sold'), Green blocks are currently selected (Status can be: 'new')

</span>
<?php

$sql = "Select * from banners ";
$res = mysql_query($sql);
?>

<form name="bidselect" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">

Select grid: <select name="BID" onchange="document.bidselect.submit()">
		<option> </option>
		<?php
	while ($row=mysql_fetch_array($res)) {
		
		if (($row['banner_id']==$BID) && ($f2->bid($_REQUEST['BID'])!='all')) {
			$sel = 'selected';
		} else {
			$sel ='';

		}
		echo '<option '.$sel.' value='.$row['banner_id'].'>'.$row['name'].'</option>';
	}
	?>
</select>
</form>
<hr>

<?php

echo "<iframe width=\"".(G_WIDTH*BLK_WIDTH)."\" height=\"".((G_HEIGHT*BLK_HEIGHT)+50)."\" frameborder=0 marginwidth=0 marginheight=0 VSPACE=0 HSPACE=0 SCROLLING=no  src=\""."map_iframe.php?BID=$BID\"></iframe>";

?>