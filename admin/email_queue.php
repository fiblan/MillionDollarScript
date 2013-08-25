<?php
/**
 * @version		$Id: email_queue.php 62 2010-09-12 01:17:36Z ryan $
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
require('admin_common.php');


ini_set('max_execution_time', 500);



if ($_REQUEST['action']=='delall') {

	$sql = "SELECT * FROM mail_queue ";
	$result = mysql_query($sql) or die(mysql_error());
	while ($row = mysql_fetch_array($result)) {

		if ($row[att1_name]!='') {
			unlink($row[att1_name]);
		}

		if ($row[att2_name]!='') {
			unlink($row[att2_name]);
		}

		if ($row[att3_name]!='') {
			unlink($row[att3_name]);
		}

		$sql = "DELETE FROM mail_queue where mail_id='".$row[mail_id]."' ";
		mysql_query($sql) or die(mysql_error());

	}
	
}
if ($_REQUEST['action']=='delsent') {
	$sql = "SELECT * from mail_queue where `status`='sent' ";
	$result = mysql_query($sql) or die(mysql_error());
	while ($row = mysql_fetch_array($result)) {

		if ($row[att1_name]!='') {
			unlink($row[att1_name]);
		}

		if ($row[att2_name]!='') {
			unlink($row[att2_name]);
		}

		if ($row[att3_name]!='') {
			unlink($row[att3_name]);
		}

		$sql = "DELETE FROM mail_queue where mail_id='".$row[mail_id]."' ";
		mysql_query($sql) or die(mysql_error());

	}
	
}
if ($_REQUEST['action']=='delerror') {
	$sql = "SELECT * from mail_queue where `status`='error' ";
	$result = mysql_query($sql) or die(mysql_error());
	while ($row = mysql_fetch_array($result)) {

		if ($row[att1_name]!='') {
			unlink($row[att1_name]);
		}

		if ($row[att2_name]!='') {
			unlink($row[att2_name]);
		}

		if ($row[att3_name]!='') {
			unlink($row[att3_name]);
		}

		$sql = "DELETE FROM mail_queue where mail_id='".$row[mail_id]."' ";
		mysql_query($sql) or die(mysql_error());

	}
	
}
if ($_REQUEST['action']=='resend') {

	$sql = "UPDATE mail_queue SET status='queued' WHERE mail_id=".$_REQUEST['mail_id'];
	mysql_query($sql) or die(mysql_error());

	process_mail_queue(1, $_REQUEST['mail_id']);

}

$EMAILS_PER_BATCH = EMAILS_PER_BATCH;
if ($EMAILS_PER_BATCH=='') {
	$EMAILS_PER_BATCH = 10;
}

if ($_REQUEST['action']=='send') {
	//$sql = "DELETE FROM mail_queue where `status`='sent' ";
	//mysql_query($sql) or die(mysql_error());

	
	process_mail_queue($EMAILS_PER_BATCH);
}

$q_to_add = $_REQUEST['q_to_add'];
$q_to_name = $_REQUEST['q_to_name'];
$q_subj = $_REQUEST['q_subj'];
$q_msg = $_REQUEST['q_msg'];
$q_status = $_REQUEST['q_status'];
$q_type = $_REQUEST['q_type'];
$search = $_REQUEST['search'];
$q_string = "&q_to_add=$q_to_add&q_subj=$q_subj&q_to_name=$q_to_name&q_msg=$q_msg&q_status=$q_status&q_type=$q_type&search=$search";

$sql = "select count(*) as c from mail_queue  ";
$result = mysql_query($sql);
$row = mysql_fetch_array($result);
$total = $row['c'];

$sql = "select count(*) as c from mail_queue where status='queued'  ";
$result = mysql_query($sql);
$row = mysql_fetch_array($result);
$queued = $row['c'];

$sql = "select count(*) as c from mail_queue where status='sent'  ";
$result = mysql_query($sql);
$row = mysql_fetch_array($result);
$sent = $row['c'];

$sql = "select count(*) as c from mail_queue where status='error'  ";
$result = mysql_query($sql);
$row = mysql_fetch_array($result);
$error = $row['c'];

?>
<b><?php echo $total; ?></b> Total Email(s) | 
<b><?php echo $queued; ?></b> Email(s) on Queue | 
<b><?php echo $sent; ?></b> Email(s) Sent | 
<b><?php echo $error; ?></b> Email(s) Failed<br>
<input type='button' value="Refresh" onclick="window.location='<?php echo $_SERVER['PHP_SELF'];?>?'" >
<input type='button' value="Process Queue - (Send <?php echo $EMAILS_PER_BATCH;?>)" onclick="window.location='<?php echo $_SERVER['PHP_SELF'];?>?action=send<?php echo $q_string; ?>'" > | <input type='button' value="Delete Sent" onclick="window.location='<?php echo $_SERVER['PHP_SELF'];?>?action=delsent<?php echo $q_string; ?>'" > |  <input type='button' value="Delete Error" onclick="window.location='<?php echo $_SERVER['PHP_SELF'];?>?action=delerror<?php echo $q_string; ?>'" > | <input type='button' value="Delete All" onclick="window.location='<?php echo $_SERVER['PHP_SELF'];?>?action=delall<?php echo $q_string; ?>'" >

<br>

<?php
//$q_string.="&action=$action";

?>
<!--
<hr>
 Note: Please see <a href="emailq.php">this file</a> for details how run the email queue process automatically.<br>
-->
<?php

if (USE_SMTP!='YES') {

	//echo "<font color='black'><b>Note: You do not have SMTP enabled, so emails will not be sent via the queue. They will be processed by PHP's mail() function. Therefore there will be no outgoing emails listed below. (This is not an error)</b></font>";

}

?>
<hr>
<form style="margin: 0" action="<?php echo $_SERVER['PHP_SELF'];?>?search=y" method="post">
         
           <center>
         <table border="0" cellpadding="2" cellspacing="0" style="border-collapse: collapse"  id="AutoNumber2"  width="100%">
  
    <tr>
      <td width="63" bgcolor="#EDF8FC" valign="top">
      <p align="right"><font size="2" face="Arial"><b>To Addr</b></font></td>
      <td width="286" bgcolor="#EDF8FC" valign="top">
      <font face="Arial">
      <input type="text" name="q_to_add" size="39" value="<?php echo $q_to_add;?>" /></font></td>
      <td width="71" bgcolor="#EDF8FC" valign="top">
      <p align="right"><b><font face="Arial" size="2">To Name</font></b></td>
      <td width="299" bgcolor="#EDF8FC" valign="top">
      
      <input type="text" name="q_to_name" size="28" value="<?php echo $q_to_name; ?>"/></td>
    </tr>
	 <tr>
      <td width="63" bgcolor="#EDF8FC" valign="top">
      <p align="right"><font size="2" face="Arial"><b>Subject</b></font></td>
      <td width="286" bgcolor="#EDF8FC" valign="top">
      <font face="Arial">
      <input type="text" name="q_subj" size="39" value="<?php echo $q_subj;?>" /></font></td>
      <td width="71" bgcolor="#EDF8FC" valign="top">
      <p align="right"><b><font face="Arial" size="2">Message</font></b></td>
      <td width="299" bgcolor="#EDF8FC" valign="top">
      
      <input type="text" name="q_msg" size="28" value="<?php echo $q_msg; ?>"/></td>
    </tr>
	 <tr>
      <td width="63" bgcolor="#EDF8FC" valign="top">
      <p align="right"><font size="2" face="Arial"><b>Status</b></font></td>
      <td width="286" bgcolor="#EDF8FC" valign="top">
      <font face="Arial">
	  <select name="q_status">
		<option value='' <?php if ($_REQUEST['q_status']=='') { echo ' selected '; } ?>></option>
		<option value='queued' <?php if ($_REQUEST['q_status']=='queued') { echo ' selected '; } ?>>queued</option>
		<option value='error' <?php if ($_REQUEST['q_status']=='error') { echo ' selected '; } ?>>error</option>
		<option value='sent' <?php if ($_REQUEST['q_status']=='sent') { echo ' selected '; } ?>>sent</option>
	  </select>
     </font></td>
      <td width="71" bgcolor="#EDF8FC" valign="top">
      <p align="right"><b><font face="Arial" size="2">Type</font></b></td>
      <td width="299" bgcolor="#EDF8FC" valign="top">
        <select name="q_type">
		<option value='' <?php if ($_REQUEST['q_type']=='') { echo ' selected '; } ?>></option>
		<option value='1' <?php if ($_REQUEST['q_type']=='1') { echo ' selected '; } ?>>1 - Complete Order</option>
		<option value='2' <?php if ($_REQUEST['q_type']=='2') { echo ' selected '; } ?>>2 - Confirm Order</option>
		<option value='3' <?php if ($_REQUEST['q_type']=='3') { echo ' selected '; } ?>>3 - Pend Order</option>
		<option value='4' <?php if ($_REQUEST['q_type']=='4') { echo ' selected '; } ?>>4 - Expire Order</option>
		<option value='5' <?php if ($_REQUEST['q_type']=='5') { echo ' selected '; } ?>>5 - Account Confirmation</option>
		<option value='6' <?php if ($_REQUEST['q_type']=='6') { echo ' selected '; } ?>>6 - Forgot Pass</option>
		<option value='7' <?php if ($_REQUEST['q_type']=='7') { echo ' selected '; } ?>>7 - Pixels Published</option>
		<option value='8' <?php if ($_REQUEST['q_type']=='8') { echo ' selected '; } ?>>8 - Order Renewed</option>
	  </select>
     
	  </td>
    </tr>
	   <tr>
      <td width="731" bgcolor="#EDF8FC" colspan="4">
      <font face="Arial"><b>
      <input type="submit" value="Find Emails" name="B1" style="float: left"><?php if ($search=='y') { ?>&nbsp; </b></font><b>[<font face="Arial"><a href="<?php echo $_SERVER['PHP_SELF']?>">Start a New Search</a></font>]</b><?php } ?></td>
    </tr>
	</table>
<?php

if ($q_to_add != '') {
	$where_sql .= " AND `to_address` like '%$q_to_add%' "; 

}

if ($q_to_name != '') {
	$where_sql .= " AND `to_name` like '%$q_to_name%' "; 

}

if ($q_msg != '') {
	$where_sql .= " AND `message` like '%$q_msg%' "; 

}

if ($q_subj != '') {
	$where_sql .= " AND `subject` like '%$q_subj%' "; 

}

if ($q_type != '') {
	$where_sql .= " AND `template_id` like '$q_type' "; 

}

if ($q_status !='') {
	$where_sql .= " AND `status`='$q_status' ";

}

$sql = "SELECT * FROM mail_queue where 1=1 $where_sql order by mail_date DESC";

$result = mysql_query ($sql) or die (mysql_error());
$count = mysql_num_rows($result);
$records_per_page = 40;
if ($count > $records_per_page) {

	mysql_data_seek($result, $_REQUEST['offset']);

}
if ($count > $records_per_page)  {
	$pages = ceil($count / $records_per_page);
	$cur_page = $offset / $records_per_page;
	$cur_page++;

	echo "<center>";
	?>
	<center><b><?php echo $count; ?> Emails on Queue returned (<?php echo $pages;?> pages) </b></center>
	<?php
	echo "Page $cur_page of $pages - ";
	$nav = nav_pages_struct($result, $q_string, $count, $records_per_page);
	$LINKS = 10;
	render_nav_pages($nav, $LINKS, $q_string, $show_emp, $cat);
	echo "</center>";

}

?>

<table border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9" >
			<tr bgColor="#eaeaea">
				<td><b><font size="2">Date</b></font></td>
				<td><b><font size="2">Type</b></font></td>
				<td><b><font size="2">To Addr</b></font></td>
				<td><b><font size="2">To Name</b></font></td>
				<td><b><font size="2">Fr Addr</b></font></td>
				<td><b><font size="2">Fr Name</b></font></td>
				<td><b><font size="2">Subj</b></font></td>
				<td><b><font size="2">Msg</b></font></td>
				<td><b><font size="2">Html Msg</b></font></td>
				<td><b><font size="2">Att</b></font></td>
				<td><b><font size="2">Status</b></font></td>
				<td><b><font size="2">Err</b></font></td>
				<td><b><font size="2">Retry</b></font></td>
				<td><b><font size="2">Action</b></font></td>
			</tr>

<?php


$i=0;
while (($row=mysql_fetch_array($result)) && ($i<$records_per_page)) {

	$i++;

	$new_window = "onclick=\"window.open('show_email.php?mail_id=".$row[mail_id]."', '', 'toolbar=no,scrollbars=yes,location=no,statusbar=no,menubar=yes,resizable=1,width=600,height=600,left = 50,top = 50');return false;\"";	

?>

	<tr bgColor="#ffffff">
		<td><font size="1"><?php echo get_local_time($row['mail_date']); ?></font></td>
		<td><font size="1"><?php echo $row['template_id']; ?></font></td>
		<td><font size="1"><?php echo $row['to_address']; ?></font></td>
		<td><font size="1"><?php echo $row['to_name']; ?></font></td>
		<td><font size="1"><?php echo $row['from_address']; ?></font></td>
		<td><font size="1"><?php echo $row['from_name']; ?></font></td>
		<td><font size="1"><?php echo substr($row['subject'],0, 7); ?><a href="" <?php echo $new_window; ?>>...</a></font></td>
		<td><font size="1"><?php echo substr($row['message'],0, 7); ?><a href="" <?php echo $new_window; ?>>...</a></font></td>
		<td><font size="1"><?php echo substr($row['html_message'],0,7); ?><a href="" <?php echo $new_window; ?>>...</a></font></td>
		<td><font size="1"><?php echo $row['attachments']; ?></font></td>
		<td><font size="2" color="<?php if ($row['status']=='sent') { echo 'green'; } ?>"><?php echo $row['status']; ?></font></td>
		<td><font size="1"><?php echo $row['error_msg']; ?></font></td>
		<td><font size="1"><?php echo $row['retry_count']; ?></font></td>
		<td><b><font size="1"><a href='email_queue.php?action=resend&mail_id=<?php echo $row[mail_id].$q_string;?>'>Resend</a></b></font></td>
	</tr>

<?php


}

?>

</table>
<?php
if ($count > $records_per_page)  {
	$pages = ceil($count / $records_per_page);
	$cur_page = $offset / $records_per_page;
	$cur_page++;

	echo "<center>";
	?>
	
	<?php
	echo "Page $cur_page of $pages - ";
	$nav = nav_pages_struct($result, $q_string, $count, $records_per_page);
	$LINKS = 10;
	render_nav_pages($nav, $LINKS, $q_string, $show_emp, $cat);
	echo "</center>";

}

?>