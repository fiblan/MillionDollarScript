<?php
require("../config.php");
require ('admin_common.php');

ini_set('max_execution_time', 10000);
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

if ($_REQUEST['BID']!='') {
	$BID = $_REQUEST['BID'];
	//$sql = "select * from banners where banner_id=$BID";
	//$result = mysql_query ($sql) or die (mysql_error().$sql);
	//$b_row = mysql_fetch_array($result);
} else {
	//$BID = 1; # Banner ID. 
}

load_banner_constants($BID);

if ($_REQUEST['action']!='') {
	$sql = "delete from blocks where status='nfs' AND banner_id=$BID ";
	mysql_query ($sql) or die (mysql_error().$sql);

	$cell = "0";
	$x="0"; $y="0";
	for ($i=0; $i < G_HEIGHT; $i++) {
		$x="0";
		for ($j=0; $j < G_WIDTH; $j++) {
			

			if ($_REQUEST['cell'.$cell]!='') {
			
				$sql = "REPLACE INTO blocks (block_id, status, x, y, banner_id) VALUES ($cell, 'nfs', $x, $y, $BID)";
				mysql_query ($sql) or die (mysql_error().$sql);

			}
			$x=$x+BLK_WIDTH;
			$cell++;

		}
		$y=$y+BLK_HEIGHT;

	}

}
?>
<p>
Here you can mark blocks to be not for sale. Click 'Save' at the bottom of this page when done. (Blocks that are not for sale appear in green)
</p>
(Note: If you have a background image, the image is blended in using the browser's built-in filter - your alpha channel is ignored on this page)
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
<?php

if ($BID !='') {

	?>
	<div style="position:absolute; background: url(temp/background<?php echo $BID; ?>.png); top:200; left:10;  z-index:0; width:<?php echo G_WIDTH*BLK_WIDTH; ?>; height:<?php echo G_HEIGHT*BLK_HEIGHT; ?>;">

	</div>
	<?php

	$sql = "show columns from blocks ";
	$result = mysql_query($sql);
	while ($row=mysql_fetch_array($result)) {

		if ($row['Field']=='status') {

			if (strpos($row['Type'], 'nfs')==0) {
			
				$sql = "ALTER TABLE `blocks` CHANGE `status` `status` SET( 'reserved', 'sold', 'free', 'ordered', 'nfs' ) NOT NULL ";
				 mysql_query($sql) or die ("<p><b>CANNOT UPGRADE YOUR DATABASE!<br>Please run the follwoing query manually from PhpMyAdmin:</b><br>$sql<br>");

			}

		}

	}

	$sql = "select block_id, status, user_id FROM blocks WHERE banner_id=$BID";
	$result = mysql_query ($sql) or die (mysql_error().$sql);
	while ($row=mysql_fetch_array($result)) {
		$blocks[$row[block_id]] = $row['status'];
		
	}


	?>

	<?php

	?>
	<script language="javascript">
	function roundNumber(number) {
		
		newnumber = Math.round (<?php echo ($BLK_WIDTH * BLK_HEIGHT); ?> * number) / <?php echo ($BLK_WIDTH * BLK_HEIGHT); ?>;
		return newnumber;
	}


	function sb(t, cell) {
		e=document.getElementById("select_status");
		//alert(t.value);
		if (t.value != '1') {
			t.value = '1';
			cell.setAttribute('src', 'not_for_sale_block.png');
			

		} else {
			t.value = '';
			cell.setAttribute('src', 'block.png', 0);
			

		}

	}

	</script>


	<form method="post" action="nfs.php" name="form1">
	<input type="hidden" name="BID" value="<?php echo $BID; ?>">
	<div style="position:absolute; top:200; left:10; z-index:1; filter:alpha(opacity=50);-moz-opacity:.50;opacity:.50;">
	<input type="hidden" name="action" value="select">


	<?php
	$cell="0";
	for ($i=0; $i < G_HEIGHT; $i++) {
		//echo "<tr><td  nowrap>";
		echo "<span style='white-space: nowrap; '>";
		for ($j=0; $j < G_WIDTH; $j++) {
			
			
			switch ($blocks[$cell]) {

				case 'sold':
					//echo "<td id='cell".$cell."' bgcolor='red' >";
					echo '<IMG SRC="../users/sold_block.png" WIDTH="'.BLK_WIDTH.'" HEIGHT="'.BLK_HEIGHT.'" BORDER="0" ALT="">';
					break;
				case 'reserved':
					//echo "<td id='cell".$cell."' bgcolor='yellow'>";
					echo '<IMG SRC="../users/reserved_block.png" WIDTH="'.BLK_WIDTH.'" HEIGHT="'.BLK_HEIGHT.'" BORDER="0" ALT="">';
					break;
				case 'nfs':
					//echo "<td id='cell".$cell."' bgcolor='yellow'>";
					echo '<IMG id="cell'.$cell.'" SRC="not_for_sale_block.png" style="cursor: pointer;cursor: hand;" WIDTH="'.BLK_WIDTH.'" HEIGHT="'.BLK_HEIGHT.'" BORDER="0" ALT="" onclick="sb(document.form1.cell'.$cell.', getElementById(\'cell'.$cell.'\'))">';
					echo '<input type="hidden" name="cell'.$cell.'" value="1" >';
					break;
				case 'ordered':
					//echo "<td id='cell".$cell."' bgcolor='orange'>";
					echo '<IMG SRC="../users/ordered_block.png" WIDTH="'.BLK_WIDTH.'" HEIGHT="'.BLK_HEIGHT.'" BORDER="0" ALT="">';
					break;

				case 'onorder':
					//echo "<td id='cell".$cell."' bgcolor='green'>";
					echo '<IMG id="cell'.$cell.'" SRC="not_for_sale_block.png" style="cursor: pointer;cursor: hand;" WIDTH="'.BLK_WIDTH.'" HEIGHT="'.BLK_HEIGHT.'" BORDER="0" ALT="" onclick="sb(document.form1.cell'.$cell.', getElementById(\'cell'.$cell.'\'))">';

					
			
					break;
				case 'free':
				case '':
					
					//echo "<input name='cell".$cell."' value='1' onclick='select_block(this, getElementById(\"cell".$cell."\"))' class='free' type='checkbox'>";

					echo '<IMG id="cell'.$cell.'" SRC="block.png" style="cursor: pointer;cursor: hand;" WIDTH="'.BLK_WIDTH.'" HEIGHT="'.BLK_HEIGHT.'" BORDER="0" ALT="" onclick="sb(document.form1.cell'.$cell.', getElementById(\'cell'.$cell.'\'))">';

					echo '<input type="hidden" name="cell'.$cell.'" value="" >';


			}
			
			
			$cell++;
		}
		//echo "</td></tr>";
		echo "</span></br>";

	}


	?>


	<!-- </table>-->
	<hr>
	<input type="submit" value='Save Not for Sale'>
	<hr>
	</div>
	</form>
<?php

}

?>

