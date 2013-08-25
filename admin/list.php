<?php
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
require("../config.php");
require ('admin_common.php');




if ($_REQUEST['BID']!='') {
		$BID = $_REQUEST['BID'];
	} else {
		$BID = 1;

	}

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
	
	<?php
	while ($row=mysql_fetch_array($res)) {
		
		if (($row['banner_id']==$BID) && ($_REQUEST['BID']!='all')) {
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
Here is the list of your top advertisers for the selected grid. <b>To have this list on your own page, copy and paste the following HTML code.</b>

</p>
<?php


ob_start();
$dir = dirname(__FILE__);
$dir = preg_split ('%[/\\\]%', $dir);
$blank = array_pop($dir);
$dir = implode('/', $dir);
include ($dir.'/mouseover_box.htm'); // edit this file to change the style of the mouseover box!
$box = ob_get_contents();
ob_end_flush();
?>

<?php

?>
<TEXTAREA style='font-size: 10px;' rows='10' onfocus="this.select()" cols="90%"><?php echo htmlentities($box.'<script src="'.BASE_HTTP_PATH.'top_ads_js.php?BID='.$BID.'"></script>'); ?></TEXTAREA>

<hr>

<script src="<?php echo BASE_HTTP_PATH.'top_ads_js.php?BID='.$BID; ?>"  ></script>

<?php


?>

