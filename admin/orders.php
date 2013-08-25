<?php
/**
 * @version		$Id: orders.php 62 2010-09-12 01:17:36Z ryan $
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

@set_time_limit ( 180 );
require("../config.php");
require ('admin_common.php');

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
$oid ='';
if ($_REQUEST['mass_complete']!='') {

	foreach ($_REQUEST[orders] as $oid) {

		$sql = "SELECT * from orders where order_id=".$oid;
		$result = mysql_query ($sql) or die (mysql_error());
		$order_row = mysql_fetch_array ($result);
		
		if ($order_row['status']!='completed') {
			complete_order ($order_row['user_id'], $oid);
			debit_transaction($order_row['user_id'],  $order_row[price], $order_row[currency], $order_row[order_id], $reason_code, 'Admin');

		}

	}

}


if ($_REQUEST['action']=='complete') {

	$sql = "SELECT * from orders where order_id=".$_REQUEST[order_id];
	$result = mysql_query ($sql) or die (mysql_error());
	$order_row = mysql_fetch_array ($result);

	complete_order ($_REQUEST['user_id'], $_REQUEST[order_id]);
	debit_transaction($_REQUEST[order_id],  $order_row[price], $order_row[currency], $order_row[order_id], $reason_code, 'Admin');
	echo "Order completed.";

}

if ($_REQUEST['action']=='cancel') {

	/*

	$sql = "UPDATE orders set status='cancelled' WHERE order_id=".$_REQUEST[order_id];
	mysql_query ($sql) or die (mysql_error());

	*/

	cancel_order($_REQUEST[order_id]);
	echo "Order cancelled.";

}


if ($_REQUEST['mass_cancel']!='') {

	echo "cancelling...";

	foreach ($_REQUEST[orders] as $oid) {

		//echo "$order_id ";
		cancel_order($oid);
	}


}
if ($_REQUEST['action']=='delete') {

	

	delete_order($_REQUEST[order_id]);

	echo "Order deleted.";

}

if ($_REQUEST['mass_delete']!='') {

	foreach ($_REQUEST[orders] as $oid) {
		delete_order($oid);
	}

}

 $q_aday = $_REQUEST['q_aday'];
$q_amon = $_REQUEST['q_amon'];
$q_ayear = $_REQUEST['q_ayear'];
$q_name = $_REQUEST['q_name'];
$q_username = $_REQUEST['q_username'];
$q_resumes = $_REQUEST['q_resumes'];
$q_news = $_REQUEST['q_news'];
$q_email = $_REQUEST['q_email'];
$q_company = $_REQUEST['q_company'];
$search = $_REQUEST['search'];
$q_string = "&q_name=$q_name&q_username=$q_username&q_email=$q_email&q_aday=$q_aday&q_amon=$q_amon&q_ayear=$q_ayear&search=$search";
?>

<p>

<form style="margin: 0" action="<?php echo $_SERVER['PHP_SELF'];?>?search=search" method="post">
<input type="hidden" value="<?php echo $_REQUEST['show'];?>" name="show">
         
           <center>
         <table border="0" cellpadding="2" cellspacing="0" style="border-collapse: collapse"  id="AutoNumber2"  width="100%">
  
    <tr>
      <td width="63" bgcolor="#EDF8FC" valign="top">
      <p align="right"><font size="2" face="Arial"><b>Name</b></font></td>
      <td width="286" bgcolor="#EDF8FC" valign="top">
      <font face="Arial">
      <input type="text" name="q_name" size="39" value="<?php echo $q_name;?>" /></font></td>
      <td width="71" bgcolor="#EDF8FC" valign="top">
      <p align="right"><b><font face="Arial" size="2">Username</font></b></td>
      <td width="299" bgcolor="#EDF8FC" valign="top">
      
      <input type="text" name="q_username" size="28" value="<?php echo $q_username; ?>"/></td>
    </tr>
    <tr>
      <td width="63" bgcolor="#EDF8FC" valign="top">
      <p align="right"><b><font face="Arial" size="2">Date From:</font></b></td>
      <td width="286" bgcolor="#EDF8FC" valign="top">
     <b>
       <font face="Arial" size="2"></font></b><font size="2" face="Arial"><b> 
       </b></font>
<?php

if ($q_aday == '') {
 
   
       // $q_aday = date("d");
	  //   $q_amon = date("m");
	  //   $q_ayear = date("Y");
 
}

?>
       <select name="q_aday">
                            <option></option>
                            <option <?php if ($q_aday=='01') { echo ' selected ';} ?> >1</option>
                            <option <?php if ($q_aday=='02') { echo ' selected ';} ?> >2</option>
                            <option <?php if ($q_aday=='03') { echo ' selected ';} ?> >3</option>
                            <option <?php if ($q_aday=='04') { echo ' selected ';} ?> >4</option>
                            <option <?php if ($q_aday=='05') { echo ' selected ';} ?> >5</option>
                            <option <?php if ($q_aday=='06') { echo ' selected ';} ?> >6</option>
                            <option <?php if ($q_aday=='07') { echo ' selected ';} ?>>7</option>
                            <option <?php if ($q_aday=='08') { echo ' selected ';} ?>>8</option>
                            <option <?php if ($q_aday=='09') { echo ' selected ';} ?> >9</option>
                            <option <?php if ($q_aday=='25') { echo ' selected ';} ?> >25</option>
                            <option <?php if ($q_aday=='26') { echo ' selected ';} ?> >26</option>
                            <option <?php if ($q_aday=='10') { echo ' selected ';} ?> >10</option>
                            <option <?php if ($q_aday=='11') { echo ' selected ';} ?> > 11</option>
                            <option <?php if ($q_aday=='12') { echo ' selected ';} ?> >12</option>
                            <option <?php if ($q_aday=='13') { echo ' selected ';} ?> >13</option>
                            <option <?php if ($q_aday=='14') { echo ' selected ';} ?> >14</option>
                            <option <?php if ($q_aday=='15') { echo ' selected ';} ?> >15</option>
                            <option <?php if ($q_aday=='16') { echo ' selected ';} ?> >16</option>
                            <option <?php if ($q_aday=='17') { echo ' selected ';} ?> >17</option>
                            <option <?php if ($q_aday=='18') { echo ' selected ';} ?> >18</option>
                            <option <?php if ($q_aday=='19') { echo ' selected ';} ?> >19</option>
                            <option <?php if ($q_aday=='20') { echo ' selected ';} ?> >20</option>
                            <option <?php if ($q_aday=='21') { echo ' selected ';} ?> >21</option>
                            <option <?php if ($q_aday=='22') { echo ' selected ';} ?> >22</option>
                            <option <?php if ($q_aday=='23') { echo ' selected ';} ?> >23</option>
                            <option <?php if ($q_aday=='24') { echo ' selected ';} ?> >24</option>
                            <option <?php if ($q_aday=='27') { echo ' selected ';} ?> >27</option>
                            <option <?php if ($q_aday=='28') { echo ' selected ';} ?> >28</option>
                            <option <?php if ($q_aday=='29') { echo ' selected ';} ?> >29</option>
                            <option <?php if ($q_aday=='30') { echo ' selected ';} ?> >30</option>
                            <option <?php if ($q_aday=='31') { echo ' selected ';} ?> >31</option>
                          </select>
                          <select name="q_amon" >
                           <option ></option>
                            <option <?php if ($q_amon=='01') { echo ' selected ';} ?> value="1">Jan</option>
                            <option <?php if ($q_amon=='02') { echo ' selected ';} ?> value="2">Feb</option>
                            <option <?php if ($q_amon=='03') { echo ' selected ';} ?> value="3">Mar</option>
                            <option <?php if ($q_amon=='04') { echo ' selected ';} ?> value="4">Apr</option>
                            <option <?php if ($q_amon=='05') { echo ' selected ';} ?> value="5">May</option>
                            <option <?php if ($q_amon=='06') { echo ' selected ';} ?> value="6">Jun</option>
                            <option <?php if ($q_amon=='07') { echo ' selected ';} ?> value="7">Jul</option>
                            <option <?php if ($q_amon=='08') { echo ' selected ';} ?> value="8">Aug</option>
                            <option <?php if ($q_amon=='09') { echo ' selected ';} ?> value="9">Sep</option>
                            <option <?php if ($q_amon=='10') { echo ' selected ';} ?> value="10">Oct</option>
                            <option <?php if ($q_amon=='11') { echo ' selected ';} ?> value="11">Nov</option>
                            <option <?php if ($q_amon=='12') { echo ' selected ';} ?> value="12">Dec</option>
                          </select>
                          <input type="text"  name="q_ayear" size="4"  value="<?php echo $q_ayear; ?>" />
	 
	 </td>
      <td width="71" bgcolor="#EDF8FC" valign="top">
      </td>
      <td width="299" bgcolor="#EDF8FC" valign="top">
      
     </td>
    </tr>
  
    <tr>
      <td width="731" bgcolor="#EDF8FC" colspan="4">
      <font face="Arial"><b>
      <input type="submit" value="Find" name="B1" style="float: left"><?php if ($_REQUEST['search']=='search') { ?>&nbsp; </b></font><b>[<font face="Arial"><a href="<?php echo $_SERVER['PHP_SELF']?>?show=<?php echo $_REQUEST['show'];?>">Start a New Search</a></font>]</b><?php } ?></td>
    </tr>
    </table>

           </center>
         

</form>

</p>

<?php

if ($_REQUEST[show]=='WA') {

	$where_sql = " AND (status ='confirmed' OR status='pending') ";
	$date_link=$date_link."&show=WA";

} elseif ($_REQUEST['show']=='CA') {
	$where_sql = " AND (status ='cancelled') ";
	$date_link=$date_link."&show=CA";
	
} elseif ($_REQUEST['show']=='EX') {
	$where_sql = " AND (status ='expired') ";
	$date_link=$date_link."&show=EX";
	
} elseif ($_REQUEST['show']=='DE') {
	$where_sql = " AND (status ='deleted') ";
	$date_link=$date_link."&show=DE";
	
} elseif ($_REQUEST['show']=='CO') {
	$where_sql = " AND status ='completed' ";

}

switch ($_REQUEST[show]) {
	case 'WA':
		echo '<p>Showing new orders waiting</p>';
		break;
	case 'CO':
		echo '<p>Showing completed orders</p>';
		break;
	case 'EX':
		echo '<p>Showing expired orders.</p>';
		break;
	case 'CA':
		echo '<p>Showing cancelled orders. Note: Blocks are kept reserved for cancelled orders. Delete the order to free the blocks.</p>';
		break;
	case 'DE':
		echo '<p>Showing deleted orders.</p>';
		break;
	
}

$q_aday = $_REQUEST['q_aday'];
$q_amon = $_REQUEST['q_amon'];
$q_ayear = $_REQUEST['q_ayear'];
$q_name = $_REQUEST['q_name'];
$q_username = $_REQUEST['q_username'];

$q_email = $_REQUEST['q_email'];

if ($q_name != '') {
	$list = preg_split ("/[\s,]+/", $q_name);
    for ($i=1; $i < sizeof($list); $i++) {
		$or1 .=" OR (`FirstName` like '%".$list[$i]."%')";
		$or2 .=" OR (`LastName` like '%".$list[$i]."%')";
    }
    $where_sql .= " AND (((`FirstName` like '%$list[0]%') $or1) OR ((`LastName` like '%$list[0]%') $or2))";
}

if ($q_username != '') {
	$q_username = trim($q_username);
	$list = preg_split ("/[\s,]+/", $q_username);
    for ($i=1; $i < sizeof($list); $i++) {
		$or .=" OR (`Username` like '%".$list[$i]."%')";
    }
    $where_sql .= " AND ((`Username` like '%$list[0]%') $or)";
}

if ($_REQUEST['user_id'] != '') {

	$where_sql .= " AND t1.user_id=".$_REQUEST['user_id'];

}

if ($_REQUEST[order_id]!='') {

echo '<h3>*** Highlighting order #'.$_REQUEST['order_id'].'.</h3> ';

}

$sql = "SELECT * FROM orders as t1, users as t2 where t1.user_id=t2.ID $where_sql ORDER BY t1.order_date DESC  ";

//echo $sql;

$result = mysql_query ($sql) or die (mysql_error());
$count = mysql_num_rows($result);

$records_per_page = 40;

if ($count > $records_per_page) {

	mysql_data_seek($result, $_REQUEST['offset']);

}

?>
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
		
	function checkBoxes(checkbox, name) {
	
		var form, state, boxes, count,i;
		form = checkbox.form;
		state = checkbox.checked;
		boxes = eval("document.form1.elements['" + name + "']");
		count = boxes.length; 
		for (i=0;i<count;i++)
	   
		boxes[i].checked = state;
	}
	</script>
<?php
$pages = ceil($count / $records_per_page);
	$cur_page = $offset / $records_per_page;
	$cur_page++;

?>

<form style="margin: 0px;" method="post" action="<?php echo $_SERVER['PHP_SELF']; echo "?offset=".$_REQUEST['offset'].$q_string; ?>" name="form1" >
<input type="hidden" name="show" value="<?php echo $_REQUEST['show'];?>">
<input type="hidden" name="offset" value="<?php echo $_REQUEST['offset'];?>">
<center><b><?php echo mysql_num_rows($result); ?> Orders Returned (<?php echo $pages;?> pages) </b></center>
<?php
	if ($count > $records_per_page)  {
		// calculate number of pages & current page

		$q_string .= "&show=".$_REQUEST['show'];
	
	
		echo "<center>";
		$label["navigation_page"] =  str_replace ("%CUR_PAGE%", $cur_page, $label["navigation_page"]);
		$label["navigation_page"] =  str_replace ("%PAGES%", $pages, $label["navigation_page"]);
	//	echo "<span > ".$label["navigation_page"]."</span> ";
		$nav = nav_pages_struct($result, $q_string, $count, $records_per_page);
		$LINKS = 40;
		render_nav_pages($nav, $LINKS, $q_string, $show_emp, $cat);
		echo "</center>";
	}
	//print_r($_REQUEST);
?>
<table width="100%" cellSpacing="1" cellPadding="3" align="center" bgColor="#d9d9d9" border="0">
<tr >
<td colspan="12"> <?php if ($_REQUEST['show']!='DE') { ?> With selected: <input type="submit" value='Complete' onclick="if (!confirmLink(this, 'Complete for all selected, are you sure?')) return false" name='mass_complete' > <?php if ($_REQUEST['show']!='CA') { ?>| <input type="submit" value='Cancel' name='mass_cancel'  onclick="if (!confirmLink(this, 'Cancel for all selected, are you sure?')) return false" > <?php } ?><?php if ($_REQUEST['show']=='CA') { ?>| <input type="submit" value='Delete' name='mass_delete'  onclick="if (!confirmLink(this, 'Delete for all selected, are you sure?')) return false"> <?php } } ?></td>
</tr>
<tr bgcolor="#eaeaea">
<td><b><font face="Arial" size="2"><input type="checkbox" onClick="checkBoxes(this, 'orders[]');"></td>
    <td><b><font face="Arial" size="2">Order Date</font></b></td>
    <td><b><font face="Arial" size="2">Customer Name</font></b></td>
    <td><b><font face="Arial" size="2">Username & ID</font></b></td>
	<td><b><font face="Arial" size="2">OrderID</font></b></td>
	<td><b><font face="Arial" size="2">AdID</font></b></td>
	<td><b><font face="Arial" size="2">Grid</font></b></td>
	<td><b><font face="Arial" size="2">Quantity</font></b></td>
	<td><b><font face="Arial" size="2">Amount</font></b></td>
	<td><b><font face="Arial" size="2">Status</font></b></td>
</tr>
<?php
 $i=0;
  while (($row = mysql_fetch_array($result, MYSQL_ASSOC)) && ($i<$records_per_page)) {
	  $i++;

	?>
	<tr onmouseover="old_bg=this.getAttribute('bgcolor');this.setAttribute('bgcolor', '#FBFDDB', 0);" onmouseout="this.setAttribute('bgcolor', old_bg, 0);" bgColor="<?php  if ($_REQUEST[order_id]==$row[order_id]) { echo '#FFFF99';} else { echo '#ffffff';} ?>">
	<td><input type="checkbox" name="orders[]" value="<?php echo $row[order_id]; ?>"></td>
	<td><font face="Arial" size="2"><?php echo get_local_time($row[order_date]);?></font></td>
	<td><font face="Arial" size="2"><?php echo escape_html($row[FirstName]." ".$row[LastName]);?></font></td>
    <td><font face="Arial" size="2"><?php echo $row[Username];?> (<a href='edit.php?user_id=<?php echo $row['ID']; ?>'>#<?php echo $row[ID];?></a>)</font></td>
	<td><font face="Arial" size="2">#<?php echo $row[order_id];?></font></td>
	<td><font face="Arial" size="2"><a href='ads.php?ad_id=<?php echo $row['ad_id']; ?>&order_id=<?php echo $row['order_id'];?>&banner_id=<?php echo $row['banner_id'];?>'>#<?php echo $row['ad_id'];?></a></font></td>
	<td><font face="Arial" size="2"><?php 

		$sql = "select * from banners where banner_id=".$row['banner_id'];
$b_result = mysql_query ($sql) or die (mysql_error().$sql);
$b_row = mysql_fetch_array($b_result);
		
		echo $b_row['name'];
		
	?></font></td>
	<td><font face="Arial" size="2"><?php echo $row[quantity];?></font></td>
	<td><font face="Arial" size="2"><?php echo convert_to_default_currency_formatted($row[currency], $row[price])?></font></td>
	<td><font face="Arial" size="2"><?php echo $row[status];?><br>
	<?php
	if ($row[status]=='cancelled') {
		$sql = "select * from transactions where type='CREDIT' and order_id=".$row[order_id];
		$r1 = mysql_query($sql) or die(mysql_error());
		if (mysql_num_rows($r1)>0){
			$refunded = true;
			echo "(Refunded)";

		} else {

			$refunded = false;

		}

	}
	?>
<?php if (($row[status]!='completed')&& ($row[status]!='deleted')&&!$refunded) { ?>
	<input type="button" style="font-size: 9px;" value="Complete" onclick="if (!confirmLink(this, 'Payment from <?php echo str_replace("'", "\\'", escape_html($row['LastName'])).", ".str_replace("'","\\'", escape_html($row['FirstName']));?> to be completed. Order for <?php echo $row[price];   ?> will be credited to their account.\n ** Are you sure? **')) return false; window.location='<?php echo $_SERVER['PHP_SELF'];?>?action=complete&user_id=<?php echo $row['ID']?>&order_id=<?php echo $row['order_id'].$date_link.$q_string."&show=".$_REQUEST['show'];?>' "> / <?php } 
	
	if ($row['status']=='cancelled') { ?><input type="button" style="font-size: 9px;" value="Delete" onclick="if (!confirmLink(this, 'Delete the order from <?php echo str_replace("'", "\\'",escape_html($row['LastName'])).", ".str_replace("'","\\'",escape_html($row['FirstName']));?>, are you sure?')) return false; window.location='<?php echo $_SERVER['PHP_SELF'];?>?action=delete&order_id=<?php echo $row['order_id'].$date_link.$q_string."&show=".$_REQUEST['show'];?>' "><?php } 

		else if ($row['status']=='deleted') {

		}
	
		else { ?><input type="button" style="font-size: 9px;" value="Cancel" onclick="if (!confirmLink(this, 'Cancel the order from <?php echo str_replace("'","\\'",escape_html($row['LastName'])).", ".str_replace("'","\\'",escape_html($row['FirstName']));?>, are you sure?')) return false; window.location='<?php echo $_SERVER['PHP_SELF'];?>?action=cancel&user_id=<?php echo $row['ID']?>&order_id=<?php echo $row['order_id'].$date_link.$q_string."&show=".$_REQUEST['show'];?>' "><?php } ?>

	<?php
		

	?>
	</font></td>
	</tr>
	<?php

}
?>
</table>
</form>
