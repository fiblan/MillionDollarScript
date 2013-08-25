<?php
/**
 * @version		$Id: approve.php 137 2011-04-18 19:48:11Z ryan $
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

$dir = dirname(__FILE__);
$dir = preg_split ('%[/\\\]%', $dir);
$blank = array_pop($dir);
$dir = implode('/', $dir);

require ($dir.'/mouseover_box.htm'); // edit this file to change the style of the mouseover box!
		
echo '<script language="JAVASCRIPT">';
require ($dir.'/include/mouseover_js.inc.php');
echo '</script>';

$BID = $f2->bid($_REQUEST['BID']);

$bid_sql = " AND banner_id=$BID ";

if (($BID=='all') || ($BID=='')) { 
	$BID=''; 
	$bid_sql = "  ";
	
} 
$sql = "Select * from banners ";
$res = mysql_query($sql);
if ($_REQUEST['action']=='approve') {

	$sql = "UPDATE blocks set approved='Y', published='N' WHERE user_id='".$_REQUEST['user_id']."' $bid_sql";
	mysql_query ($sql) or die (mysql_error().$sql);
	$sql = "UPDATE orders set approved='Y', published='N' WHERE user_id='".$_REQUEST['user_id']."' $bid_sql";
	mysql_query ($sql) or die (mysql_error().$sql);
	echo "Advertiser Approved.<br>";
}

if ($_REQUEST['mass_approve']!='') {

	if (sizeof($_REQUEST['users'])>0) {

		foreach ($_REQUEST['users'] as $user_id) {

			$sql = "UPDATE blocks set approved='Y', published='N' WHERE user_id='".$user_id."' $bid_sql";
			mysql_query ($sql) or die (mysql_error().$sql);
			$sql = "UPDATE orders set approved='Y', published='N' WHERE user_id='".$user_id."' $bid_sql";
			mysql_query ($sql) or die (mysql_error().$sql);
		}
		echo "Advertiser(s) Approved.<br>";
	}


}

if ($_REQUEST['action']=='disapprove') {

	$sql = "UPDATE blocks set approved='N' WHERE user_id='".$_REQUEST['user_id']."' $bid_sql";
	mysql_query ($sql) or die (mysql_error().$sql);
	$sql = "UPDATE orders set approved='N' WHERE user_id='".$_REQUEST['user_id']."' $bid_sql";
	mysql_query ($sql) or die (mysql_error().$sql);
	echo "Advertiser Disapproved.<br>";
}

if ($_REQUEST['mass_disapprove']!='') {

	if (sizeof($_REQUEST['users'])>0) {

		foreach ($_REQUEST['users'] as $user_id) {

			$sql = "UPDATE blocks set approved='N' WHERE user_id=".$user_id." $bid_sql";
			mysql_query ($sql) or die (mysql_error().$sql);
			$sql = "UPDATE orders set approved='N' WHERE user_id=".$user_id." $bid_sql";
			mysql_query ($sql) or die (mysql_error().$sql);

		}
		echo "Advertiser(s) Disapproved.<br>";

	}

}

if ($_REQUEST['do_it_now']=='true') {



	// process all grids

	$sql = "select * from banners ";
	$result = mysql_query ($sql) or die (mysql_error().$sql);	
	while ($row = mysql_fetch_array($result)) {
		echo process_image($row['banner_id']);
		publish_image($row['banner_id']);
		process_map($row['banner_id']);
	}

}

/*
if ($_REQUEST['all_go']!='') {

	$sql = "UPDATE blocks set approved='Y' ";
	mysql_query ($sql) or die (mysql_error().$sql);
	$sql = "select * from banners ";
	$result = mysql_query ($sql) or die (mysql_error().$sql);	
	while ($row = mysql_fetch_array($result)) {
		process_image($row['banner_id']);
		publish_image($row['banner_id']);
		process_map($row['banner_id']);
	}

}

*/


?>
<?php echo $f2->get_doc(); ?>

<script language="JavaScript">

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

</head>
<h3>Remember to process your Grid Image(s) <a href="process.php">here</a></h3>
<!--Shortcut:<input type="submit" value=' Approve all &amp; Process all!' style="font-size: 9px; " onclick="if (!confirmLink(this, 'Approve for all pixels and process + publish all grid(s)? are you sure?')) { return false} else { document.form1.all_go.value='ok'; document.form1.submit()}" name='all_go' >
-->
<form name="bidselect" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
<input type="hidden" name="old_order_id" value="<?php echo $order_id;?>">
<input type="hidden" value="<?php echo $_REQUEST['app'];?>" name="app">
Select Grid: <select name="BID" onchange="document.bidselect.submit()">
	<option value='all' <?php if ($f2->bid($_REQUEST['BID'])=='all') { echo 'selected'; } ?>>Show All</option>
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

if ($_REQUEST['save_links']!='') {

	//echo "Saving links...";
	if (sizeof($_REQUEST['urls'])>0) {
		//echo " * * *";
		$i=0;

		foreach ($_REQUEST['urls'] as $url) {
			$sql = "UPDATE blocks SET url='".$_REQUEST['new_urls'][$i]."', alt_text='".$_REQUEST['new_alts'][$i]."' WHERE user_id='".$_REQUEST['user_id']."' and url='$url' and banner_id='".$f2->bid($_REQUEST['BID'])."'  ";
			//echo $sql."<br>";
			mysql_query ($sql) or die (mysql_error().$sql);
			$i++;
		}
		


	}

}

if ($_REQUEST['edit_links']!='') {

	?>
<h3>Edit Links:</h3>
	<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
	<input type="hidden" name="offset" value="<?php echo $_REQUEST['offset']; ?>">
	<input type="hidden" name="BID" value="<?php echo $f2->bid($_REQUEST['BID']); ?>">
	<input type="hidden" name="user_id" value="<?php echo $_REQUEST['user_id']; ?>">
	<input type="hidden" value="<?php echo $_REQUEST['app'];?>" name="app">
	<table>
	<tr>
	<td><b>URL</b></td>
	<td><b>Alt Text</b></td>
	</tr>

	<?php

		$sql = "SELECT alt_text, url, count(alt_text) AS COUNT, banner_id FROM blocks WHERE user_id=".$_REQUEST['user_id']."  $bid_sql group by url ";
		
		$m_result = mysql_query ($sql);
		$i=0;
		while ($m_row=mysql_fetch_array($m_result)) {
			$i++;
			if ($m_row[url] !='') {
				echo "<tr><td>
				<input type='hidden' name='urls[]' value='".htmlspecialchars($m_row[url])."'>
				<input type='text' name='new_urls[]' size='40' value=\"".escape_html($m_row[url])."\"></td>
						<td><input name='new_alts[]' type='text' size='80' value=\"".escape_html($m_row[alt_text])."\"></td></tr>";
			}
		}

		?>

		</table>
		<input type="submit" value="Save Changes" name="save_links">

		</form>

		<?php

}



$sql = "SELECT FirstName, LastName, Username, Email,ID, alt_text, url, t1.status as STAT, approved, banner_id FROM blocks as t1, users as t2 where t1.user_id=t2.ID AND status='sold' AND url<>'' $bid_sql  GROUP BY user_id, banner_id order by approved  DESC";

//echo $sql;

$result = mysql_query ($sql) or die (mysql_error().$sql);
$count = mysql_num_rows($result);
$records_per_page = 20;

if ($count > $records_per_page) {

	mysql_data_seek($result, $_REQUEST['offset']);

}

$pages = ceil($count / $records_per_page);
$cur_page = $offset / $records_per_page;
$cur_page++;

?>
<!--
<center><b><?php echo mysql_num_rows($result); ?> Advertisers Returned (<?php echo $pages;?> page[s]) </b></center>
-->

<?php
if ($count > $records_per_page)  {
		// calculate number of pages & current page

		//$q_string .= "&show=".$_REQUEST['show'];
	
	
		echo "<center>";
		$label["navigation_page"] =  str_replace ("%CUR_PAGE%", $cur_page, $label["navigation_page"]);
		$label["navigation_page"] =  str_replace ("%PAGES%", $pages, $label["navigation_page"]);
		//	echo "<span > ".$label["navigation_page"]."</span> ";
		$q_string = $q_string."&app=".$_REQUEST['app'];
		$nav = nav_pages_struct($result, $q_string, $count, $records_per_page);
		$LINKS = 40;
		render_nav_pages($nav, $LINKS, $q_string, $show_emp, $cat);
		echo "</center>";
	}
?>
<form name="form1" method="post" action="<?php echo $_SERVER['PHP_SELF']?>">
<input type="hidden" name="offset" value="<?php echo $_REQUEST['offset']; ?>">
<input type="hidden" name="BID" value="<?php echo $f2->bid($_REQUEST['BID']); ?>">
<input type="hidden" name="app" value="<?php echo $_REQUEST['app']; ?>">
<input type="hidden" name="all_go" value="">
<table width="100%" cellSpacing="1" cellPadding="3" align="center" bgColor="#d9d9d9" border="0">

<tr>
<td colspan="12"> 

 With selected: <input type="submit" value='Approve' style="font-size: 9px; background-color: #33FF66 " onclick="if (!confirmLink(this, 'Approve for all selected, are you sure?')) return false" name='mass_approve' > 
 <input type="submit" value='Disapprove' style="font-size: 9px; background-color: #FF6600" onclick="if (!confirmLink(this, 'Disapprove all selected, are you sure?')) return false" name='mass_disapprove' >
 <input type="checkbox" name="do_it_now" <?php if ( ($_REQUEST['do_it_now']=='true')) { echo ' checked '; } ?> value="true"> Process Grid Images immediately after approval / disapproval <br>




</td>
</tr>
<tr>

<tr>
    <td><b><font face="Arial" size="2"><input type="checkbox" onClick="checkBoxes(this, 'users[]');"></td>

    <td><b><font face="Arial" size="2">Customer Name</font></b></td>
    <td><b><font face="Arial" size="2">Username & ID</font></b></td>
	<td><b><font face="Arial" size="2">Email</font></b></td>
	<td><b><font face="Arial" size="2">Grid</font></b></td>
	<td><b><font face="Arial" size="2">Link Text(s) & Link URL(s)</font></b></td>
	
	<td><b><font face="Arial" size="2">Action</font></b></td>
</tr>

<?php

 $i=0;
  while (($row = mysql_fetch_array($result, MYSQL_ASSOC)) && ($i<$records_per_page)) {
	  

	  // is it approved?

	  $sql = "SELECT alt_text, url, approved  FROM blocks WHERE user_id=".$row[ID]." and approved='N' $bid_sql and banner_id=".$row[banner_id];
	  $a_result = mysql_query ($sql);	
	  $a_row=mysql_fetch_array($a_result);

	  if ($_REQUEST['app']=='Y') {
		  // let through approved pixels only
		  if ($a_row['approved']=='N') {
			  continue; // skip
		  }	 

	  } else {
		   // let through disapproved pixels only
		  if ($a_row['approved']!='N') {
			  
			  continue; // skip
		  }

	  }

	  $i++;

?>
<tr onmouseover="old_bg=this.getAttribute('bgcolor');this.setAttribute('bgcolor', '#FBFDDB', 0);" onmouseout="this.setAttribute('bgcolor', old_bg, 0);" bgColor="#ffffff">
    <td><input type="checkbox" name="users[]" value="<?php echo $row[ID]; ?>"></td>
    <td><font face="Arial" size="2"><?php echo $row[FirstName]." ".$row[LastName];?></font></td>
    <td><font face="Arial" size="2"><?php echo $row[Username];?> (#<?php echo $row[ID];?>)</font></td>
	<td><font face="Arial" size="2"><?php echo $row[Email]; ?></font></td>
	<td><font face="Arial" size="2"><?php 
		$sql = "SELECT name from banners where banner_id=".$row['banner_id'];
	//	echo "<br>".$sql;
		$t_result = mysql_query ($sql);
		$t_row=mysql_fetch_array($t_result);
		echo $t_row['name']; ?></font></td>
	<td ><font face="Arial" size="2"><?php 

		$sql = "SELECT alt_text, url, count(alt_text) AS COUNT, banner_id, ad_id FROM blocks WHERE user_id=".$row['ID']." and banner_id=".$row['banner_id']." $bid_sql group by url ";
	//	echo "<br>".$sql;
		$m_result = mysql_query ($sql);
		while ($m_row=mysql_fetch_array($m_result)) {
			if ($m_row[url] !='') {
				$js_str = " onmousemove=\"sB(event,'".htmlspecialchars(str_replace("'","\'",($m_row['alt_text'])))."',this, ".$m_row['ad_id'].")\" onmouseout=\"hI()\" ";

				echo "<font size='1'>".$m_row['url']." - <a $js_str href='".$m_row['url']."' target='_blank' >".$m_row['alt_text']."</a> (".$m_row['COUNT'].")</font><br>";
			}
		}
		//echo '<a href="approve.php?edit_links=yes&BID='.$row['banner_id'].'&offset='.$_REQUEST['offset'].'&user_id='.$row['ID'].'">[Edit Links]</a> ';

		echo  "<a target='_blank' href='show_map.php?user_id=".$row['ID']."&BID=".$row['banner_id']."'>[View Pixels...]</a>";
		
		//echo $row[alt_text]; ?></font>
	</td>

	

	<td><font face="Arial" size="2"><?php
	if ($a_row[approved]=='N') {
	?>
	<input type="button" style="font-size: 9px; background-color: #33FF66" value="Approve" onclick=" window.location='<?php echo $_SERVER['PHP_SELF'];?>?action=approve&BID=<?php echo $row[banner_id];?>&user_id=<?php echo $row['ID'].$date_link;?>&offset=<?php $_REQUEST['offset'];?>&app=<?php echo $_REQUEST['app']; ?>&do_it_now='+document.form1.do_it_now.checked "><?php
	}

	if ($a_row[approved]!='N') {
	?>
	<input type="button" style="font-size: 9px;" value="Disapprove" onclick=" window.location='<?php echo $_SERVER['PHP_SELF'];?>?action=disapprove&BID=<?php echo $row[banner_id];?>&user_id=<?php echo $row['ID'].$date_link;?>&offset=<?php $_REQUEST['offset'];?>&app=<?php echo $_REQUEST['app']; ?>&do_it_now='+document.form1.do_it_now.checked "><?php
	}



	?>
	 </font></td>
</tr>


<?php 

}

?>

</table>

</form>
<?php
if ($count > $records_per_page)  {
		// calculate number of pages & current page

		//$q_string .= "&show=".$_REQUEST['show'];
	
	
		echo "<center>";
		$label["navigation_page"] =  str_replace ("%CUR_PAGE%", $cur_page, $label["navigation_page"]);
		$label["navigation_page"] =  str_replace ("%PAGES%", $pages, $label["navigation_page"]);
	//	echo "<span > ".$label["navigation_page"]."</span> ";
		$nav = nav_pages_struct($result, $q_string, $count, $records_per_page);
		$LINKS = 40;
		render_nav_pages($nav, $LINKS, $q_string, $show_emp, $cat);
		echo "</center>";
	}
?>

