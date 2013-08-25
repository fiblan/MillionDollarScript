<?php

require ('config.php');
require ('include/mouseover_js.inc.php');
ob_start();

if ($_REQUEST['BID']!='') {
		$BID = $_REQUEST['BID'];
	} else {
		$BID = 1;

	}

if (!is_numeric($BID)) {
	die();
}

?><table  border="0" cellSpacing="1" cellPadding="3"  bgColor="#d9d9d9">
<tr>
<td>
<font face="arial" size="2"><b>Date of Purchase</b></font>
</td>
<td>
<font face="arial" size="2"><b>Ads(s)</b></font>
</td>
<td>
<font face="arial" size="2"><b>Pixels</b></font>
</td>
</tr>

<?php
require_once ("include/ads.inc.php");
//require ('include/mouseover_js.inc.php');
$sql = "SELECT *, MAX(order_date) as max_date, sum(quantity) AS pixels FROM orders where status='completed' AND banner_id='$BID' GROUP BY user_id, banner_id order by pixels desc ";
$result = mysql_query($sql) or die(mysql_error());
while ($row=mysql_fetch_array($result)) {
?>
	<tr bgcolor="#ffffff" >
	<td>
	<font face="arial" size="2"><?php echo get_formatted_date(get_local_time($row[max_date])); ?></font>
	</td>
	<td>
	<font face="arial" size="2"><?php

		
		$sql = "Select * FROM  `ads` as t1, `orders` AS t2 WHERE t1.ad_id=t2.ad_id AND t1.banner_id='$BID' and t1.order_id > 0 AND t1.user_id='".$row['user_id']."' ORDER BY `ad_date`";
		$m_result = mysql_query ($sql) or die(mysql_error());
		while ($prams=mysql_fetch_array($m_result, MYSQL_ASSOC)) {
			
			$ALT_TEXT = get_template_value('ALT_TEXT', 1);
			$ALT_TEXT = str_replace("'", "", $ALT_TEXT);
			$ALT_TEXT = (str_replace("\"", '', $ALT_TEXT));
			$js_str = " onmousemove=\"sB(event, '".$ALT_TEXT."', this, ".$prams['ad_id'].")\" onmouseout=\"hI()\" ";
			echo $br.'<a target="_blank" '.$js_str.' href="'.get_template_value('URL', 1).'">'.get_template_value('ALT_TEXT', 1).'</a>';
			$br = '<br>';
		}

	?></font>
	</td>
	<td>
	<font face="arial" size="2"><?php 
		echo $row['pixels'];?></font>
	</td>
	</tr>
<?php

}

?>

</table>
<?php

$c = ob_get_contents();
ob_end_clean();
$c = str_replace("'", "\\'", $c);
$c = str_replace("\n", "", $c);
$c = str_replace("\r", "", $c);
echo "document.write('".($c)."');";

?>

