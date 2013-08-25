<?php
/**
 * @version		$Id: clicks.php 137 2011-04-18 19:48:11Z ryan $
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

if (ADVANCED_CLICK_COUNT!='YES') {

	die ("Advanced click tracking not enabled. You will need to enable advanced click tracking in the Main Config");


}
if ($f2->bid($_REQUEST['BID'])!='') {
	$BID = $f2->bid($_REQUEST['BID']);
} else {
	$BID = 1;

}

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
		echo '<option '.$sel.' value='.$row['banner_id'].'>'.$row[name].'</option>';
	}
	?>
</select>
</form>
<hr>
<?php


$local_date = (gmdate("Y-m-d H:i:s"));
$local_time = strtotime($local_date);

if ($_REQUEST['from_day']=='') {
	$_REQUEST['from_day']="1";

}
if ($_REQUEST['from_month']=='') {
	$_REQUEST['from_month'] = date("m", $local_time);

}
if ($_REQUEST['from_year']=='') {
	$_REQUEST['from_year'] = date('Y', $local_time);
}

if ($_REQUEST['to_day']=='') {
	$_REQUEST['to_day']=date('d', $local_time);
	
}
if ($_REQUEST['to_month']=='') {
	$_REQUEST['to_month'] = date('m', $local_time);
	
}
if ($_REQUEST['to_year']=='') {

	$_REQUEST['to_year']=date('Y', $local_time);
}
?>

<h3>Click Report</h3>
<form method="GET">
From y/m/d: 
<select name="from_year" >
<option value=''> </option>
<?php
for ($i=2005; $i <= date("Y"); $i++) {
	if ($_REQUEST['from_year'] == $i) {
		$sel = " selected ";
	} else {
		$sel = " ";
	}
	echo "<option value='$i' $sel>$i</option>";
}
?>
</select>


<select name="from_month" >
<option value=''> </option>
<?php
for ($i=1; $i <= 12; $i++) {
	if ($_REQUEST['from_month'] == $i) {
		$sel = " selected ";
	} else {
		$sel = " ";
	}
	echo "<option value='$i' $sel >$i</option>";
}
?>
</select> 
<select name="from_day" >
<option value=''> </option>
<?php
for ($i=1; $i <= 31; $i++) {
	if ($_REQUEST['from_day'] == $i) {
		$sel = " selected ";
	} else {
		$sel = " ";
	}
	echo "<option value='$i' $sel >$i</option>";
}
?>
</select>


 To y/m/d: 

 <select name="to_year" >
<option value=''> </option>
<?php
for ($i=2005; $i <= date("Y"); $i++) {
	if ($_REQUEST['to_year'] == $i) {
		$sel = " selected ";
	} else {
		$sel = " ";
	}
	echo "<option value='$i' $sel>$i</option>";
}

if ($_REQUEST['select_date']!='') {

	$date_link=

		"&from_day=".$_REQUEST['from_day'].
		"&from_month=".$_REQUEST['from_month'].
		"&from_year=".$_REQUEST['from_year'].
		"&to_day=".$_REQUEST['to_day'].
		"&to_month=".$_REQUEST['to_month'].
		"&to_year=".$_REQUEST['to_year'].
		"&status=".$_REQUEST['status'].
		"&select_date=1";
}
?>
</select>


<select name="to_month">
<option value=''> </option>
<?php
for ($i=1; $i <= 12; $i++) {
	if ($_REQUEST['to_month'] == $i) {
		$sel = " selected ";
	} else {
		$sel = " ";
	}
	echo "<option value='$i' $sel >$i</option>";
}
?>
</select>
 
<select name="to_day" >
<option value=''> </option>
<?php
for ($i=1; $i <= 31; $i++) {
	if ($_REQUEST['to_day'] == $i) {
		$sel = " selected ";
	} else {
		$sel = " ";
	}
	echo "<option value='$i' $sel >$i</option>";
}
?>
</select>

<input type="hidden" name="BID" value="<?php echo $BID?>">
<input type="submit" name="select_date" value="Go"> &nbsp; &nbsp; &nbsp;
 <input type="button" name="select_date" value="Reset" onclick='window.location="<?php echo $_SERVER['PHP_SELF']; ?>" '>
</form><p>

<?php



$from = $_REQUEST['from_year']."-".$_REQUEST['from_month']."-".$_REQUEST['from_day'];

$to = $_REQUEST['to_year']."-".$_REQUEST['to_month']."-".$_REQUEST['to_day'];

$sql = "SELECT *, SUM(clicks) as CLICKSUM FROM clicks WHERE banner_id='$BID' AND `date` >= '$from' AND `date` <= '$to' GROUP BY date ";



$result = mysql_query ($sql);



?>

<p>
Showing Report for grid:<?php echo $BID; ?>
</p>
<table border="1">

<tr>
<td><b>Date</b></td>
<td><b>Clicks</b></td>
</tr>

<?php

if (mysql_num_rows($result)>0) {

	while ($row=mysql_fetch_array($result)) {

?>
<tr>
	<td><?php echo get_local_time($row['date'])?></td>
	<td><?php echo $row['CLICKSUM']?></td>
	
</tr>


<?php
	$total = $total +  $row['CLICKSUM'];
	}

}

?>
</table>

Total Clicks: <?php echo $total; ?>