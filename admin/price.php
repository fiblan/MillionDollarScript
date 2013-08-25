<?php
/**
 * @version		$Id: price.php 137 2011-04-18 19:48:11Z ryan $
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
<?php echo $f2->get_doc(); ?>
<?php

$BID = $f2->bid($_REQUEST['BID']);

?>

<title>Edit Prices</title>

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
<b>Price Zones:</b> Here you can add different price price zones to the grid. This feature allows you to make some regions of the grid more expensive than others. <i>Careful: Packages disregard Price Zones, i.e. if a grid has packages, the Price Zones will be ignored for that grid.</i> </p>

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
	<b>Grid Name:</b> <?php echo G_NAME;?><br>
	<b>Default Price per block:</b> <?php echo G_PRICE;?><br>
	
	<input type="button" style="background-color:#66FF33" value="New Price Zone..." onclick="window.location='price.php?new=1&BID=<?php echo $BID; ?>'"><br>
	
	Listing rows that are marked as custom price.<br>

	<?php

	function validate_input() {

		global $b_row, $BID;

		if (trim($_REQUEST['row_from'])=='') {
			$error .= "<b>- 'Start from Row' code is blank</b><br>";
		}
		if (trim($_REQUEST['row_to'])=='') {
			$error .= "<b>- 'End at Row' is blank</b><br>";
		}

		if (trim($_REQUEST['col_from'])=='') {
			$error .= "<b>- 'Start from Col' code is blank</b><br>";
		}
		if (trim($_REQUEST['col_to'])=='') {
			$error .= "<b>- 'End at Col' is blank</b><br>";
		}

		if (trim($_REQUEST['color'])=='') {
			$error .= "<b>- 'Color' not selected</b><br>";

		}

		if ($error=='') {

			if (!is_numeric($_REQUEST['row_from'])) {
			$error .= "<b>- 'Start from Row' must be a number</b><br>";

			}

			if (!is_numeric($_REQUEST['row_to'])) {
			$error .= "<b>- 'End at Row' must be a number</b><br>";

			}

			if ($error=='') {

				if ($_REQUEST['row_from'] > $_REQUEST['row_to']) {
					$error .= "<b>- 'Start from Row' is larger than 'End at Row'</b><br>";

				} elseif (($_REQUEST['row_from'] < 1) || ($_REQUEST['row_to'] > G_HEIGHT)) {

					$error .= "<b>- The rows specified are out of range! (The current grid has ".G_HEIGHT." rows)</b><br>";
					
				} else {
					// check database..

					if ($_REQUEST['submit']!='') {
						if ($_REQUEST['price_id'] !='') {
							$and_price = "and price_id <>".$_REQUEST['price_id'];

						}
						$sql = "SELECT * FROM prices where row_from <= ".$_REQUEST['row_to']." AND row_to >=".$_REQUEST['row_from']." AND col_from <= ".$_REQUEST['col_to']." AND col_to >=".$_REQUEST['col_from']." $and_price AND banner_id=$BID";
						//echo "$sql<br>";
						$result = mysql_query ($sql) or die (mysql_error());
						if (mysql_num_rows($result)>0) {
							$error .= "<b> - Cannot create: Price zones cannot overlap other price zones!</b><br>";

							$o_o_r=true;

					}

					}
				}

				if ($_REQUEST['col_from'] > $_REQUEST['col_to']) {
					$error .= "<b>- 'Start from Column' is larger than 'End at Column'</b><br>";

				} elseif (($_REQUEST['col_from'] < 1) || ($_REQUEST['col_to'] > G_WIDTH)) {

					$error .= "<b>- The columns specified are out of range! (The current grid has ".G_WIDTH." columns)</b><br>";
					
				} 

			}

		}

		if (trim($_REQUEST['price'])=='') {
			$error .= "<b>- Price is blank</b><br>";

		}

		if (trim($_REQUEST['currency'])=='') {
			$error .= "<b>- Currency is blank</b><br>";

		}


		return $error;


	}

	if ($_REQUEST['action'] == 'delete') {
		
		$sql = "DELETE FROM prices WHERE price_id='".$_REQUEST['price_id']."' ";
		mysql_query($sql) or die(mysql_error().$sql);
		
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
			$_REQUEST['block_id_to'] = ((($_REQUEST['row_to']) * G_WIDTH)-1);


			$sql = "REPLACE INTO prices(price_id, banner_id, row_from, row_to, col_from, col_to, block_id_from, block_id_to, price, currency, color) VALUES ('".$_REQUEST['price_id']."', '".$BID."', '".$_REQUEST['row_from']."', '".$_REQUEST['row_to']."', '".$_REQUEST['col_from']."', '".$_REQUEST['col_to']."', '".$_REQUEST['block_id_from']."', '".$_REQUEST['block_id_to']."', '".$_REQUEST['price']."', '".$_REQUEST['currency']."', '".$_REQUEST['color']."') ";

			//echo $sql;

			mysql_query ($sql) or die (mysql_error());

			$_REQUEST['new'] ='';
			$_REQUEST['action'] = '';
			//print_r ($_REQUEST);


		}

	}

	?>


	<?php

	$result = mysql_query("select * FROM prices  where banner_id=$BID") or die (mysql_error());

	if (mysql_num_rows($result)>0) {
	?>

	<table width="800" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9" border="0">
	<tr>
		<td><b><font face="Arial" size="2">Grid ID</font></b></td>
		<td><b><font face="Arial" size="2">Color</font></b></td>
		<td><b><font face="Arial" size="2">Row<br>- From</font></b></td>
		<td><b><font face="Arial" size="2">Row<br>- To</font></b></td>
		<td><b><font face="Arial" size="2">Column<br>- From</font></b></td>
		<td><b><font face="Arial" size="2">Column<br>- To</font></b></td>
		<td><b><font face="Arial" size="2">Price<br>per block</font></b></td>
		<td><b><font face="Arial" size="2">Currency</font></b></td>
		<td><b><font face="Arial" size="2">Action</font></b></td>
	</tr>

	<?php
				
				while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

					?>

					<tr bgcolor="#ffffff">

					 <td><font face="Arial" size="2"><?php echo $row[banner_id];?></font></td>
					<td bgcolor="<?php if ($row[color]=='yellow') { echo '#FFFF00';} elseif ($row[color]=='cyan') { echo '#00FFFF';} elseif ($row[color]=='magenta') { echo '#FF00FF';} ?>"><font face="Arial" size="2" ><?php 
						
					echo $row['color'];
					 
					 ?>
						 
					</font></td>
					<td><font face="Arial" size="2"><?php echo $row[row_from];?></font></td>
					<td><font face="Arial" size="2"><?php echo $row[row_to];?></font></td>
					<td><font face="Arial" size="2"><?php echo $row[col_from];?></font></td>
					<td><font face="Arial" size="2"><?php echo $row[col_to];?></font></td>
					<td><font face="Arial" size="2"><?php echo $row[price];?></font></td>
					<td><font face="Arial" size="2"><?php echo $row[currency];?></font></td>
					<td nowrap><font face="Arial" size="2"><a href="<?php echo $_SERVER['PHP_SELF'];?>?price_id=<?php echo $row['price_id'];?>&BID=<?php echo $BID; ?>&action=edit">Edit</a> | <a href="<?php echo $_SERVER['PHP_SELF'];?>?price_id=<?php echo $row[price_id];?>&BID=<?php echo $BID; ?>&action=delete" onclick="return confirmLink(this, 'Delete, are you sure?');">Delete</a></font></td>	
					
					
					</tr>


					<?php

				}
		?>
		</table>

	<?php
	} else {
		echo "There are no custom price zones for this grid.<br>";
	}

	?>
	
	<?php

	if ($_REQUEST['new']=='1') {
		echo "<h4>Add Price Zone:</h4>";
		//echo "<p>Note: Make sure that you create a file for your new language in the /lang directory.</p>";
	}
	if ($_REQUEST['action']=='edit') {
		echo "<h4>Edit Price Zone:</h4>";

		$sql = "SELECT * FROM prices WHERE `price_id`='".$_REQUEST['price_id']."' ";
		$result = mysql_query ($sql) or die (mysql_error());
		$row = mysql_fetch_array($result);

		if ($error=='') {
		$_REQUEST['color'] = $row['color'];
		$_REQUEST['price_id'] = $row['price_id'];
		$_REQUEST['row_from'] = $row['row_from'];
		$_REQUEST['row_to'] = $row['row_to'];
		$_REQUEST['col_from'] = $row['col_from'];
		$_REQUEST['col_to'] = $row['col_to'];
		$_REQUEST['price'] = $row['price'];
		$_REQUEST['currency'] = $row['currency'];

		}

		
	}

	if (($_REQUEST['new']!='') || ($_REQUEST['action']=='edit')) {

		if ($_REQUEST['col_from']=='') {
			$_REQUEST['col_from']=1;
		}
		if ($_REQUEST['col_to']=='') {
			$_REQUEST['col_to']=G_HEIGHT;
		}



		?>
	<form action='price.php' method="post">
	<input type="hidden" value="<?php echo $row['price_id']?>" name="price_id" >
	<input type="hidden" value="<?php echo $_REQUEST['new']?>" name="new" >
	<input type="hidden" value="<?php echo $_REQUEST['action']?>" name="action" >
	<input type="hidden" value="<?php echo $BID; ?>" name="BID" >
	<table border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9">
	<tr bgcolor="#ffffff" ><td><font size="2">Color :</font></td><td>
	<select  name="color">
	<option value="" >[Select]</option>
	<option value="yellow" <?php if ($_REQUEST[color]=='yellow') {echo ' selected ';} ?> style="background-color: #FFFF00">Yellow</option>
	<option value="cyan" <?php if ($_REQUEST[color]=='cyan'){ echo ' selected ';} ?> style="background-color: #00FFFF">Cyan</option>
	<option value="magenta" <?php if ($_REQUEST[color]=='magenta') { echo ' selected ';} ?> style="background-color: #FF00FF">Magenta</option>
	<option value="white" <?php if ($_REQUEST[color]=='white') { echo ' selected ';} ?> style="background-color: #FFffFF">White</option>
	</select>

	</td></tr>
	<tr bgcolor="#ffffff" ><td><font size="2">Start from Row :</font></td><td><input size="2" type="text" name="row_from" value="<?php echo $_REQUEST['row_from']; ?>"> eg. 1</td></tr>
	<tr bgcolor="#ffffff" ><td><font size="2">End at Row:</font></td><td><input <?php echo $disabled; ?> size="2" type="text" name="row_to" value="<?php echo $_REQUEST['row_to']; ?>"> eg. 25</td></tr>
	<tr bgcolor="#ffffff" ><td><font size="2">Start from Column :</font></td><td><input size="2" type="text" name="col_from" value="<?php echo $_REQUEST['col_from']; ?>"> eg. 1</td></tr>
	<tr bgcolor="#ffffff" ><td><font size="2">End at Column:</font></td><td><input <?php echo $disabled; ?> size="2" type="text" name="col_to" value="<?php echo $_REQUEST['col_to']; ?>"> eg. 25</td></tr>
	<tr bgcolor="#ffffff" ><td><font size="2">Price Per Block:</font></td><td><input  size="5" type="text" name="price" value="<?php echo $_REQUEST['price']; ?>">Price per block (<?php echo BLK_WIDTH*BLK_HEIGHT; ?> pixels). Enter a decimal</td></tr>
	<tr bgcolor="#ffffff" ><td><font size="2">Currency:</font></td><td><select <?php echo $disabled; ?> size="1" name="currency"><?php currency_option_list( $_REQUEST['currency']);?>The price's currency</td></tr>

	</table>
	<input type="submit" name="submit" value="Submit">
	</form>

		<?php

	}

	?>
	<p>
	&nbsp;
	</p>
	<img usemap="#prices" src="show_price_zone.php?BID=<?php echo $BID;?>&time=<?php echo (time()); ?>" width="<?php echo (G_WIDTH*BLK_WIDTH); ?>" height="<?php echo (G_HEIGHT*BLK_HEIGHT); ?>" border="0" usemap="#main" />

	<?php

	show_price_area($BID);
}

?>

</body>

</html>