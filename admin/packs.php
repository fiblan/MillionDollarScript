<?php
/**
 * @version		$Id: packs.php 137 2011-04-18 19:48:11Z ryan $
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

?>
<html>
<?php

$BID = $f2->bid($_REQUEST['BID']);

?>
<?php echo $f2->get_doc(); ?>

<title>Edit Packages</title>

<script language="JavaScript" type="text/javascript">

	function confirmLink(theLink, theConfirmMsg) {
    
       if (theConfirmMsg == '' || typeof(window.opera) != 'undefined') {
           return true;
       }

       var is_confirmed = confirm(theConfirmMsg + '\n');
       if (is_confirmed) {
           theLink.href += '&is_js_confirmed=1';
       }

       return is_confirmed;
	}
	</script>

</head>

<body style=" font-family: 'Arial', sans-serif; font-size:10pt; ">
<p>
Packages: Here you can add different price / expiry / max orders combinations to your grids called 'Packages'. Packages added to a grid will overwrite the grid's default price, expiry & max orders settings. After selecting pixels from a grid, the user will choose which package they want. Once the package is selected, the script will calculate the final price for the order. <i>Careful: Packages disregard Price Zones, i.e. if a grid has  packages, then the Price Zones will be ignored for that grid.</i> </p>
<hr>
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
		echo '<option '.$sel.' value='.$row['banner_id'].'>'.$row[name].'</option>';
	}
	?>
</select>
</form>
<?php

if ($BID!='') {

	?>
	<hr>
	
	<b>Grid ID:</b> <?php echo $BID; ?><br>
	<b>Grid Name</b>: <?php echo G_NAME;?><br>
	<b>Default Price per 100:</b> <?php echo G_PRICE;?><br>

	<input type="button" style="background-color:#66FF33" value="New Package..." onclick="window.location='packs.php?new=1&BID=<?php echo $BID; ?>'"><br>
	
	Listing rows that are marked as custom price.<br>

	<?php

	function validate_input() {

		global $b_row, $BID;

		
		if (trim($_REQUEST['price'])=='') {
			$error .= "<b>- Price is blank</b><br>";

		}elseif (!is_numeric($_REQUEST['price'])) {
			$error .= "<b>- Price must be a number.</b><br>";

		}

		if (trim($_REQUEST['description'])=='') {
			$error .= "<b>- Description is blank</b><br>";

		}

		if (trim($_REQUEST['currency'])=='') {
			$error .= "<b>- Currency is blank</b><br>";

		} 

		if (trim($_REQUEST['max_orders'])=='') {
			$error .= "<b>- Max orders is blank</b><br>";

		}elseif (!is_numeric($_REQUEST['max_orders'])) {
			$error .= "<b>- Max orders must be a number</b><br>";

		}

		if (trim($_REQUEST['days_expire'])=='') {
			$error .= "<b>- Days to expire is blank</b><br>";

		} elseif (!is_numeric($_REQUEST['days_expire'])) {
			$error .= "<b>- Days to expire must be a number.</b><br>";

		}


		return $error;


	}

	if ($_REQUEST['action'] == 'delete') {

		$sql = "SELECT * FROM orders where package_id='".$_REQUEST['package_id']."'";
		$result = mysql_query ($sql);
		if ((mysql_num_rows($result)>0) && ($_REQUEST['really']=='')) {
			echo "<font color='red'>Cannot delete package: This package is a part of another order</font> (<a href='packs.php?BID=$BID&package_id=".$_REQUEST['package_id']."&action=delete&really=yes'>Click here to delete anyway</a>)";

		} else {
		
			$sql = "DELETE FROM packages WHERE package_id='".$_REQUEST['package_id']."' ";
			mysql_query($sql) or die(mysql_error().$sql);
		}
		
	}

	function set_to_default($package_id) {

		global $BID;

		$sql = "SELECT * FROM packages where is_default='Y' and banner_id=$BID ";
		$result = mysql_query($sql) or die(mysql_error().$sql);
		$row = mysql_fetch_array($result);
		$old_default = $row['package_id'];

		$sql = "UPDATE packages SET is_default='N' WHERE banner_id=$BID ";
		
		mysql_query($sql) or die(mysql_error().$sql);
		$sql = "UPDATE packages SET is_default='Y' WHERE package_id='".$package_id."' AND banner_id=$BID";
		mysql_query($sql) or die(mysql_error().$sql);

		if ($old_default == '') {

			// update previous orders which are blank, to the default.
			// in the 1.7.0 database, all orders must have packages

			$sql = "UPDATE orders SET package_id='".$package_id."' WHERE package_id='' AND banner_id='".$BID."' ";
			mysql_query($sql) or die(mysql_error().$sql);


		}


	}

	if ($_REQUEST['action'] == 'default') {
		set_to_default($_REQUEST['package_id']);
		
	}

	if ($_REQUEST['submit']!='') {

		$error = validate_input();

		if ($error != '') {
		
			echo "<p>";
			echo "<font color='red'>Error: cannot save due to the following errors:</font><br>";
			echo $error;
			echo "</p>";


		} else {

			// calculate block id..

			$_REQUEST['block_id_from'] = ($_REQUEST['row_from']-1) * G_WIDTH;
			$_REQUEST['block_id_to'] = ((($_REQUEST['row_to']) * G_HEIGHT)-1);


			$sql = "REPLACE INTO packages(package_id, banner_id, price, currency, days_expire,  max_orders, description, is_default) VALUES ('".$_REQUEST['package_id']."', '".$BID."', '".$_REQUEST['price']."', '".$_REQUEST['currency']."', '".$_REQUEST['days_expire']."',  '".$_REQUEST['max_orders']."', '".$_REQUEST['description']."', '".$_REQUEST['is_default']."')";

			//echo $sql;

			mysql_query ($sql) or die (mysql_error());

			$_REQUEST['new'] ='';
			$_REQUEST['action'] = '';
			//print_r ($_REQUEST);

			// if no default package exists, set the last inserted banner to default

			if (!get_default_package($BID)) {
				set_to_default(mysql_insert_id());
			}



		}

	}

	?>


	<?php

	$result = mysql_query("select * FROM packages  where banner_id=$BID") or die (mysql_error());

	if (mysql_num_rows($result)>0) {
	?>

	<table width="800" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9" border="0">
	<tr>
		<td><b><font face="Arial" size="2">Package ID</font></b></td>
		<td><b><font face="Arial" size="2">Description</font></b></td>
		<td><b><font face="Arial" size="2">Days Expire</font></b></td>
		<td><b><font face="Arial" size="2">Price</font></b></td>
		<td><b><font face="Arial" size="2">Currency</font></b></td>
		<td><b><font face="Arial" size="2">Max Orders</font></b></td>
		<td><b><font face="Arial" size="2">Default</font></b></td>
		<td><b><font face="Arial" size="2">Action</font></b></td>
	</tr>
	<?php		
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
				?>

				<tr bgcolor="#ffffff">

				<td><font face="Arial" size="2"><?php echo $row['package_id'];?></font></td>
				<td><font face="Arial" size="2" ><?php echo $row['description']; ?></font></td>
				<td><font face="Arial" size="2"><?php if ($row['days_expire']==0) { echo 'unlimited';} else {echo $row['days_expire'];}?></font></td>
				<td><font face="Arial" size="2"><?php echo $row['price'];?></font></td>
				<td><font face="Arial" size="2"><?php echo $row['currency'];?></font></td>
				<td><font face="Arial" size="2"><?php if ($row['max_orders']==0) { echo 'unlimited';} else {echo $row['max_orders'];}?></font></td>
				<td><font face="Arial" size="2"><?php echo $row['is_default'];?></font></td>
				
				<td nowrap><font face="Arial" size="2"><a href="<?php echo $_SERVER['PHP_SELF'];?>?package_id=<?php echo $row['package_id'];?>&BID=<?php echo $BID; ?>&action=edit">Edit</a> <?php if ($row['is_default']!='Y') {?>| <a href="<?php echo $_SERVER['PHP_SELF'];?>?package_id=<?php echo $row['package_id'];?>&BID=<?php echo $BID; ?>&action=default" >Set Default</a><?php } ?> | <a href="<?php echo $_SERVER['PHP_SELF'];?>?package_id=<?php echo $row['package_id'];?>&BID=<?php echo $BID; ?>&action=delete" onclick="return confirmLink(this, 'Delete, are you sure?');">Delete</a></font></td>	
				
				
				</tr>


				<?php

			}
		?>
		</table>

	<?php
	} else {
		echo "There are no packages for this grid.<br>";
	}

	?>
	
	<?php

	if ($_REQUEST['new']=='1') {
		echo "<h4>New Package:</h4>";
		
	}
	if ($_REQUEST['action']=='edit') {
		echo "<h4>Edit Package:</h4>";

		$sql = "SELECT * FROM packages WHERE `package_id`='".$_REQUEST['package_id']."' ";
		$result = mysql_query ($sql) or die (mysql_error());
		$row = mysql_fetch_array($result);

		if ($error=='') {
			$_REQUEST['banner_id'] = $row['banner_id'];
			$_REQUEST['package_id'] = $row['package_id'];
			$_REQUEST['days_expire'] = $row['days_expire'];
			$_REQUEST['price'] = $row['price'];
			$_REQUEST['currency'] = $row['currency'];
			$_REQUEST['price_id'] = $row['price_id'];
			$_REQUEST['description'] = $row['description'];
			$_REQUEST['max_orders'] = $row['max_orders'];
			$_REQUEST['is_default'] = $row['is_default'];
		
		}

		
	}

	if (($_REQUEST['new']!='') || ($_REQUEST['action']=='edit')) {

	



		?>
	<form action='packs.php' method="post">
	<input type="hidden" value="<?php echo $row['package_id']?>" name="package_id" >
	<input type="hidden" value="<?php echo $_REQUEST['new']?>" name="new" >
	<input type="hidden" value="<?php echo $_REQUEST['action']?>" name="action" >
	<input type="hidden" value="<?php echo $_REQUEST['is_default']?>" name="is_default" >
	<input type="hidden" value="<?php echo $BID; ?>" name="BID" >
	<table border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9">
	
	<tr bgcolor="#ffffff" ><td><font size="2">Name:</font></td><td><input  size="15" type="text" name="description" value="<?php echo $_REQUEST['description']; ?>">Enter a descriptive name for the package. Eg, "$30 for 100 days."</td></tr>
	<tr bgcolor="#ffffff" ><td><font size="2">Price Per Block:</font></td><td><input  size="5" type="text" name="price" value="<?php echo $_REQUEST['price']; ?>">Price per block (<?php echo (BLK_WIDTH*BLK_HEIGHT); ?> pixels). Enter a decimal</td></tr>
	<tr bgcolor="#ffffff" ><td><font size="2">Currency:</font></td><td><select <?php echo $disabled; ?> size="1" name="currency"><?php currency_option_list( $_REQUEST['currency']);?>The price's currency</td></tr>
	<tr bgcolor="#ffffff" ><td><font size="2">Days to expire:</font></td><td><input  size="5" type="text" name="days_expire" value="<?php echo $_REQUEST['days_expire']; ?>">How many days? (Enter 0 to use the grid's default)</td></tr>
	<tr bgcolor="#ffffff" ><td><font size="2">Maximum orders:</font></td><td><input  size="5" type="text" name="max_orders" value="<?php echo $_REQUEST['max_orders']; ?>">How many times can this pacakge be ordered? (Enter 0 for unlimited)</td></tr>
	

	</table>
	<input type="submit" name="submit" value="Submit">
	</form>

		<?php

	}

	?>
	

	<?php

//	show_price_area($BID);
}

?>

</body>

</html>