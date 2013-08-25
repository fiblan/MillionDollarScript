<?php
ini_set('max_execution_time', 10000);
require("../config.php");
require ('admin_common.php');

if ($_REQUEST['BID']!='') {
	$BID = $_REQUEST['BID'];
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
		
		if (($row['banner_id']==$BID) && ($_REQUEST['BID']!='all')) {
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