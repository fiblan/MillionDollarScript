<?php
/**
 * @version		$Id: orders.php 72 2010-09-12 01:31:46Z ryan $
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

session_start();
include ("../config.php");
include ("login_functions.php");

process_login();


require ("header.php");


?>

<script language="JavaScript" type="text/javascript">

function confirmLink(theLink, theConfirmMsg)
   {
      
       if (theConfirmMsg == '' || typeof(window.opera) != 'undefined') {
           return true;
       }

       var is_confirmed = confirm(theConfirmMsg + '\n');
       if (is_confirmed) {
           theLink.href += '&is_js_confirmed=1';
       }

       return is_confirmed;
   } // end of the 'confirmLink()' function

</script>

<h3><?php echo $label['advertiser_ord_history']; ?></h3>

<p>
<?php echo $label['advertiser_ord_explain']; ?>
</p>

<h4><?php echo $label['advertiser_ord_hist_list']; ?></h4>

<?php
$sql = "SELECT * FROM orders as t1, users as t2 where t1.user_id=t2.ID AND t1.user_id='".$_SESSION['MDS_ID']."' ORDER BY t1.order_date DESC ";
$result = mysql_query ($sql) or die (mysql_error());


?>

<table width="100%" cellSpacing="1" cellPadding="3" align="center" bgColor="#d9d9d9" border="0">
<tr>
    <td><b><font face="Arial" size="2"><?php echo $label['advertiser_ord_prderdate']; ?></font></b></td>
    <td><b><font face="Arial" size="2"><?php echo $label['advertiser_ord_custname']; ?></font></b></td>
    <td><b><font face="Arial" size="2"><?php echo $label['advertiser_ord_usernid'];?></font></b></td>
	<td><b><font face="Arial" size="2"><?php echo $label['advertiser_ord_orderid']; ?></font></b></td>
	<td><b><font face="Arial" size="2"><?php echo $label['advertiser_ord_quantity']; ?></font></b></td>
	<td><b><font face="Arial" size="2"><?php echo $label['advertiser_ord_image']; ?></font></b></td>
	<td><b><font face="Arial" size="2"><?php echo $label['advertiser_ord_amount']; ?></font></b></td>
	<td><b><font face="Arial" size="2"><?php echo $label['advertiser_status']; ?></font></b></td>
	</tr>
<?php

if (mysql_num_rows($result)==0) {
	echo '<td colspan="7">'.$label['advertiser_ord_noordfound'].' </td>';
} else {

	while ($row=mysql_fetch_array($result)) {
	?>
<tr onmouseover="old_bg=this.getAttribute('bgcolor');this.setAttribute('bgcolor', '#FBFDDB', 0);" onmouseout="this.setAttribute('bgcolor', old_bg, 0);" bgColor="#ffffff">
    <td><font face="Arial" size="2"><?php echo get_local_time($row[order_date]);?></font></td>
	<td><font face="Arial" size="2"><?php echo $row[FirstName]." ".$row[LastName];?></font></td>
    <td><font face="Arial" size="2"><?php echo $row[Username];?> (#<?php echo $row[ID];?>)</font></td>
	<td><font face="Arial" size="2">#<?php echo $row[order_id];?></font></td>
	<td><font face="Arial" size="2"><?php echo $row[quantity];?></font></td>
	<td><font face="Arial" size="2"><?php 

			$sql = "select * from banners where banner_id=".$row['banner_id'];
			$b_result = mysql_query ($sql) or die (mysql_error().$sql);
			$b_row = mysql_fetch_array($b_result);
		
			echo $b_row['name'];
			
		?></font></td>
	<td><font face="Arial" size="2"><?php echo convert_to_default_currency_formatted($row['currency'], $row['price']); ?></font></td>
	<td><font face="Arial" size="2"><?php echo $label[$row['status']];?><br><?php
	if (USE_AJAX=='SIMPLE') {
		$order_page = 'order_pixels.php';
		$temp_var = '&order_id=temp';
	} else {
		$order_page = 'select.php';
	}
	switch ($row['status']) {
		case "new":
			echo $label['adv_ord_inprogress'].'<br>';
			echo "<a href='".$order_page."?BID=".$row['banner_id']."$temp_var'>(".$label['advertiser_ord_confnow'].")</a>";
			echo "<br><input type='button' value='".$label['advertiser_ord_cancel_button']."' onclick='if (!confirmLink(this, \"".$label['advertiser_ord_cancel']."\")) return false; window.location=\"orders.php?cancel=yes&order_id=".$row['order_id']."\"' >";
			break;
		case "confirmed":
			echo "<a href='payment.php?order_id=".$row['order_id']."&BID=".$row['banner_id']."'>(".$label['advertiser_ord_awaiting'].")</a>";
			//echo "<br><input type='button' value='".$label['advertiser_ord_cancel_button']."' onclick='if (!confirmLink(this, \"".$label['advertiser_ord_cancel']."\")) return false; window.location=\"orders.php?cancel=yes&order_id=".$row['order_id']."\"' >";
			break;
		case "completed":
			echo "<a href='publish.php?order_id=".$row['order_id']."&BID=".$row['banner_id']."'>(".$label['advertiser_ord_manage_pix'].")</a>";

			if ($row['days_expire'] > 0) {

				if ($row['published']!='Y') {
						$time_start = strtotime(gmdate('r'));
				} else {
					$time_start = strtotime($row['date_published']." GMT");
				}

				$elapsed_time = strtotime(gmdate('r')) - $time_start;
				$elapsed_days = floor ($elapsed_time / 60 / 60 / 24);
				
				$exp_time =  ($row['days_expire']  * 24 * 60 * 60);

				$exp_time_to_go = $exp_time - $elapsed_time;
				$exp_days_to_go =  floor ($exp_time_to_go / 60 / 60 / 24);

				$to_go = elapsedtime($exp_time_to_go);

				$elapsed = elapsedtime($elapsed_time);

				if ($row['date_published']!='') {
					echo "<br>Expires in: ".$to_go;
				}

			}

			break;
		case "expired":

			$time_expired = strtotime($row['date_stamp']);

			$time_when_cancel = $time_expired + (DAYS_RENEW * 24 * 60 * 60);

			$days =floor (($time_when_cancel - time()) / 60 / 60 / 24);

			// check to see if there is a renew_wait or renew_paid order

			$sql = "select order_id from orders where (status = 'renew_paid' OR status = 'renew_wait') AND original_order_id='".$row['original_order_id']."' ";
			$res_c = mysql_query($sql);
			if (mysql_num_rows($res_c)==0) {
 
				$label['advertiser_ord_renew'] = str_replace("%DAYS_TO_RENEW%", $days, $label['advertiser_ord_renew']);
				echo "<a href='payment.php?order_id=".$row['order_id']."&BID=".$row['banner_id']."'><font color='red' size='1'>(".$label['advertiser_ord_renew'].")</font></a>";
			}
			break;
		case "cancelled":
			break;
		case "pending":
			break;

	}

/*
	if (($row['price']==0) && ($row['status']='deleted') && && ($row['status']!='cancelled')) {

		echo "<br><input type='button' value='".$label['advertiser_ord_cancel_button']."' onclick='if (!confirmLink(this, \"".$label['advertiser_ord_cancel']."\")) return false; window.location=\"orders.php?cancel=yes&order_id=".$row['order_id']."\"' >";


	}

*/
	
	?></td>
	</tr>

	<?php
	}


}
?>

</table>

<?php

require ("footer.php");

?>