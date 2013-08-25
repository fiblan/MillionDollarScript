<?php
/**
 * @version		$Id: ads.inc.php 162 2012-12-12 16:48:21Z ryan $
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

require_once ('category.inc.php');
require_once ('lists.inc.php');
require_once ('dynamic_forms.php');
global $ad_tag_to_field_id;
global $ad_tag_to_search; 
global $CACHE_ENABLED;
if ($CACHE_ENABLED=='YES') {
	$dir = dirname(__FILE__);
	$dir = preg_split ('%[/\\\]%', $dir);
	$blank = array_pop($dir);
	$dir = implode('/', $dir);
	include ("$dir/cache/form1_cache.inc.php");
	$ad_tag_to_search = $tag_to_search;
	$ad_tag_to_field_id = $tag_to_field_id;
} else {
	$ad_tag_to_search = tag_to_search_init(1);
	$ad_tag_to_field_id = ad_tag_to_field_id_init();
}



#####################################

function ad_tag_to_field_id_init () {
	global $CACHE_ENABLED;
	if ($CACHE_ENABLED=='YES') {

		global $ad_tag_to_field_id;
		return $ad_tag_to_field_id;

	}
	global $label;

	//$sql = "SELECT *, t2.field_label AS NAME FROM `form_fields` as t1, form_field_translations as t2 where t1.field_id = t2.field_id AND t2.lang='".$_SESSION['MDS_LANG']."' AND form_id=1 ORDER BY list_sort_order ";
	$sql = "SELECT * FROM `form_fields`, form_field_translations where form_fields.field_id = form_field_translations.field_id AND form_field_translations.lang='".$_SESSION['MDS_LANG']."' AND form_id=1 ORDER BY list_sort_order ";
	$result = mysql_query($sql) or die (mysql_error());
	# do a query for each field
	while ($fields = mysql_fetch_array($result, MYSQL_ASSOC)) {

		//$form_data = $row[]
		$tag_to_field_id[$fields['template_tag']]['field_id'] = $fields['field_id'];
		$tag_to_field_id[$fields['template_tag']]['field_type'] = $fields['field_type'];
		$tag_to_field_id[$fields['template_tag']]['field_label'] = $fields['field_label'];
	}


	$tag_to_field_id["ORDER_ID"]['field_id'] = 'order_id';
	$tag_to_field_id["ORDER_ID"]['field_label'] = 'Order ID';
	//$tag_to_field_id["ORDER_ID"]['field_label'] = $label["employer_resume_list_date"];

	$tag_to_field_id["BID"]['field_id'] = 'banner_id';
	$tag_to_field_id["BID"]['field_label'] = 'Grid ID';

	$tag_to_field_id["USER_ID"]['field_id'] = 'user_id';
	$tag_to_field_id["USER_ID"]['field_label'] = 'User ID';

	$tag_to_field_id["AD_ID"]['field_id'] = 'ad_id';
	$tag_to_field_id["AD_ID"]['field_label'] = 'Ad ID';

	$tag_to_field_id["DATE"]['field_id'] = 'ad_date';
	$tag_to_field_id["DATE"]['field_label'] = 'Date';




	return $tag_to_field_id;



}

######################################################################

function load_ad_values ($ad_id) {

	global $f2;

	$prams = array();


	$sql = "SELECT * FROM `ads` WHERE ad_id='$ad_id'   ";
	

	$result = mysql_query($sql) or die ($sql. mysql_error());

	

	if ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		
		$prams['ad_id'] = $ad_id;
		$prams['user_id'] = $row['user_id'];
		$prams['order_id'] = $row['order_id'];
		$prams['banner_id'] = $row['banner_id'];
		

		$sql = "SELECT * FROM form_fields WHERE form_id=1 AND field_type != 'SEPERATOR' AND field_type != 'BLANK' AND field_type != 'NOTE' ";
		$result = mysql_query($sql) or die(mysql_error());
		while ($fields = mysql_fetch_array($result, MYSQL_ASSOC)) {

			$prams[$fields['field_id']] =  $row[$fields['field_id']];

			if ($fields['field_type']=='DATE')  {
				$day = $_REQUEST[$row['field_id']."d"];
				$month = $_REQUEST[$row['field_id']."m"];
				$year = $_REQUEST[$row['field_id']."y"];

				$prams[$fields['field_id']] = "$year-$month-$day";

			} elseif (($fields['field_type']=='MSELECT') || ($row['field_type']=='CHECK'))  {
				if (is_array($_REQUEST[$row['field_id']])) {	
					$prams[$fields['field_id']] = implode (",", $_REQUEST[$fields['field_id']]);
				} else {
					$prams[$fields['field_id']] = $_REQUEST[$fields['field_id']];
				}
				
			}

		}
		return $prams;
	} else {
		return false;
	}

	


}

#########################################################


function assign_ad_template($prams) {

	global $label;

	$str = $label['mouseover_ad_template'];

	$sql = "SELECT * FROM form_fields WHERE form_id='1' AND field_type != 'SEPERATOR' AND field_type != 'BLANK' AND field_type != 'NOTE' ";
		//echo $sql;
	$result = mysql_query($sql) or die(mysql_error());
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		
		if ($row['field_type']=='IMAGE') {
			if ((file_exists(UPLOAD_PATH.'images/'.$prams[$row['field_id']]))&&($prams[$row['field_id']])) {
				$str = str_replace('%'.$row['template_tag'].'%', '<img alt="" src="'. UPLOAD_HTTP_PATH."images/".$prams[$row['field_id']].'" >', $str);
			} else {
				//$str = str_replace('%'.$row['template_tag'].'%',  '<IMG SRC="'.UPLOAD_HTTP_PATH.'images/no-image.gif" WIDTH="150" HEIGHT="150" BORDER="0" ALT="">', $str);
				$str = str_replace('%'.$row['template_tag'].'%',  '', $str);
			}
		} else {
			$str = str_replace('%'.$row['template_tag'].'%', get_template_value($row['template_tag'],1), $str);
		} 
 
		$str = str_replace('$'.$row['template_tag'].'$', get_template_field_label($row['template_tag'],1), $str);
		
		
	}
	return $str;



}

#########################################################

function display_ad_form ($form_id, $mode, $prams) {

	global $f2, $label, $error, $BID;

	if ($prams == '' ) {

		$prams['mode'] = $_REQUEST['mode'];
		$prams['ad_id']= $_REQUEST['ad_id'];
		$prams['banner_id'] = $BID;
		$prams['user_id'] = $_REQUEST['user_id'];

		$sql = "SELECT * FROM form_fields WHERE form_id='$form_id' AND field_type != 'SEPERATOR' AND field_type != 'BLANK' AND field_type != 'NOTE' ";
		//echo $sql;
		$result = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

			//$prams[$row[field_id]] = $_REQUEST[$row[field_id]];

			if ($row['field_type']=='DATE')  {
				$day = $_REQUEST[$row['field_id']."d"];
				$month = $_REQUEST[$row['field_id']."m"];
				$year = $_REQUEST[$row['field_id']."y"];
				$prams[$row['field_id']] = "$year-$month-$day";

			} elseif (($row['field_type']=='MSELECT') || ($row['field_type']=='CHECK'))  {
				if (is_array($_REQUEST[$row['field_id']])) {	
					$prams[$row['field_id']] = implode (",", $_REQUEST[$row['field_id']]);
				} else {
					$prams[$row['field_id']] = $_REQUEST[$row['field_id']];
				}
				
			} else {
				$prams[$row['field_id']] = stripslashes ($_REQUEST[$row['field_id']]);
			}


		}
 
	}


/*
	if (!defined('SCW_INCLUDE')) {
		?>
		<script type='text/JavaScript' src='<?php echo BASE_HTTP_PATH."scw/scw_js.php?lang=".$_SESSION['MDS_LANG']; ?>'></script>
		<?php
		define ('SCW_INCLUDE', 'Y');
	}
*/
	?>
	<form method="POST"  action="<?php htmlentities($_SERVER['PHP_SELF']); ?>" name="form1" onsubmit=" form1.savebutton.disabled=true;" enctype="multipart/form-data">
	
	<input type="hidden" name="mode" size="" value="<?php echo $mode; ?>">
	<input type="hidden" name="ad_id" size="" value="<?php echo $prams['ad_id']; ?>">
	<input type="hidden" name="user_id" size="" value="<?php echo $prams['user_id']; ?>">
	<input type="hidden" name="order_id" size="" value="<?php echo $prams['order_id']; ?>">
	<input type="hidden" name="banner_id" size="" value="<?php echo $prams['banner_id']; ?>">

	<table cellSpacing="1" cellPadding="5" class="ad_data"  id="ad"   >
	<?php  if (($error != '' ) && ($mode!='edit')) { ?>
	<tr>
		<td bgcolor="#F2F2F2" colspan="2"><?php  echo "<span class='error_msg_label'>".$label['ad_save_error']."</span><br> <b>".$error."</b>";  ?></td>
	</tr>
	<?php } ?>
  <tr  bgColor="#ffffff">
    <td  bgColor="#eaeaea">
	<?php if ($mode == "edit") {
					echo "[Ad Form]";
				}
		 // section 1
		display_form ($form_id, $mode, $prams, 1);
	?>
  
  </tr>
	<tr><td colspan="2" bgcolor="#ffffff">
		<input type="hidden" name="save" id="save101" value="">
		<?php if ($mode=='edit' || $mode == 'user') { ?>
		<input class="form_submit_button big_button" type="submit" name="savebutton" value="<?php echo $label['ad_save_button'];?>" onClick="save101.value='1';">
		<?php } ?>
		</td></tr>
	</table>
	</form>

	<?php

}



###########################################################################

function list_ads ($admin=false, $order, $offset, $list_mode='ALL', $user_id='') {

	## Globals
	global $f2, $label, $tag_to_field_id;
	$tag_to_field_id = ad_tag_to_field_id_init();

	###########################################
	# Load in the form data, including column names
	# (dont forget LANGUAGE TOO)

	global $ad_tag_to_field_id;

    $records_per_page = 40;
    global $label; // languages array

   
   $order_str = $order;

   if ($order == '') {
     $order = " `order_id` ";           
   } else {
      $order = " `$order` ";
   }

   
	global $action;
   // process search result
	if ($_REQUEST['action'] == 'search') {
		$q_string = generate_q_string(1);  	   
		$where_sql = generate_search_sql(1);
	}
	   
	// DATE_FORMAT(`adate`, '%d-%b-%Y') AS formatted_date

	$order = $_REQUEST['order_by'];

	if ($_REQUEST['ord']=='asc') {
		$ord = 'ASC';
	} elseif ($_REQUEST['ord']=='desc') {
		$ord = 'DESC';
	} else {
		$ord = 'DESC'; // sort descending by default
	}

	if ($order == '') {
		$order = " `ad_date` ";           
	} else {
		$order = " `$order` ";
	}
	global $BID;
	if ($list_mode == 'USER' ) {

		if (!is_numeric($user_id)) {
			$user_id = $_SESSION['MDS_ID'];
		} 
		//$sql = "Select *  FROM `ads` as t1, `orders` as t2 WHERE t1.ad_id=t2.ad_id AND t1.order_id > 0 AND t1.banner_id='".$BID."' AND t1.user_id='".$user_id."' AND (t2.status = 'completed' OR t2.status = 'expired') $where_sql ORDER BY $order $ord ";
		$sql = "Select *  FROM `ads`, `orders` WHERE ads.ad_id=orders.ad_id AND ads.order_id > 0 AND ads.banner_id='".$BID."' AND ads.user_id='".$user_id."' AND (orders.status = 'completed' OR orders.status = 'expired') $where_sql ORDER BY $order $ord ";

	} elseif ($list_mode =='TOPLIST') {

	//	$sql = "SELECT *, DATE_FORMAT(MAX(order_date), '%Y-%c-%d') as max_date, sum(quantity) AS pixels FROM orders, ads where ads.order_id=orders.order_id AND status='completed' and orders.banner_id='$BID' GROUP BY orders.user_id, orders.banner_id order by pixels desc ";
		
	} else {
		
		//$sql = "Select *  FROM `ads` as t1, `orders` AS t2 WHERE t1.ad_id=t2.ad_id AND t1.banner_id='$BID' and t1.order_id > 0 $where_sql ORDER BY $order $ord ";
		$sql = "Select *  FROM `ads`, `orders` WHERE ads.ad_id=orders.ad_id AND ads.banner_id='$BID' and ads.order_id > 0 $where_sql ORDER BY $order $ord ";

	}

	//echo "[".$sql."]";

	$result = mysql_query($sql) or die (mysql_error());
	############
	# get the count
	$count = mysql_num_rows($result);

	if ($count > $records_per_page) {

		mysql_data_seek($result, $offset);

	}
 

	if ($count > 0 )  {

		if ($pages == 1) {
		   
	   } elseif ($list_mode!='USER') {

			$pages = ceil($count / $records_per_page);
			$cur_page = $_REQUEST['offset'] / $records_per_page;
			$cur_page++;

			echo "<center>";
			//echo "Page $cur_page of $pages - ";
			$label["navigation_page"] =  str_replace ("%CUR_PAGE%", $cur_page, $label["navigation_page"]);
			$label["navigation_page"] =  str_replace ("%PAGES%", $pages, $label["navigation_page"]);
			echo "<span > ".$label["navigation_page"]."</span> ";
			$nav = nav_pages_struct($result, $q_string, $count, $records_per_page);
			$LINKS = 10;
			render_nav_pages($nav, $LINKS, $q_string, $show_emp, $cat);
			echo "</center>";


		}
		$dir = dirname(__FILE__);
		$dir = preg_split ('%[/\\\]%', $dir);
		$blank = array_pop($dir);
		$dir = implode('/', $dir);

		include ($dir.'/mouseover_box.htm'); // edit this file to change the style of the mouseover box!
		
		echo '<script language="JAVASCRIPT">';
		include ('mouseover_js.inc.php');
		echo '</script>';
		?>
		<table border='0' bgcolor='#d9d9d9' cellspacing="1" cellpadding="5" id="adslist" >
		<tr bgcolor="#EAEAEA">
		<?php
		if ($admin == true ) {
			 echo '<td class="list_header_cell">&nbsp;</td>';
		}

		if ($list_mode == 'USER' ) {
			echo '<td class="list_header_cell">&nbsp;</td>';
		}

		echo_list_head_data(1, $admin);

		if (($list_mode == 'USER' ) || ($admin)) {
			echo '<td class="list_header_cell">'.$label['ads_inc_pixels_col'].'</td>';
			echo '<td class="list_header_cell">'.$label['ads_inc_expires_col'].'</td>';
			echo '<td class="list_header_cell" >'.$label['ad_list_status'].'</td>';
		}

		?>
		
		</tr>

		<?php
		$i=0; global $prams;
		while (($prams = mysql_fetch_array($result, MYSQL_ASSOC)) && ($i < $records_per_page)) {

			$i++;

	
		 ?>
			  <tr bgcolor="ffffff" onmouseover="old_bg=this.getAttribute('bgcolor');this.setAttribute('bgcolor', '#FBFDDB', 0);" onmouseout="this.setAttribute('bgcolor', old_bg, 0);">
	
			  <?php
		  
		 if ($admin == true ) {
			 echo '<td class="list_data_cell" >';

			 ?>
			 <!--<input style="font-size: 8pt" type="button" value="Delete" onClick="if (!confirmLink(this, 'Delete, are you sure?')) {return false;} window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=delete&ad_id=<?php echo $prams['ad_id']; ?>'"><br>!-->
				<input type="button" style="font-size: 8pt" value="Edit" onClick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=edit&ad_id=<?php echo $prams['ad_id']; ?>'">

				<?php
			 
			 echo '</td>';
		 }

		 if ($list_mode == 'USER' ) {
			 echo '<td class="list_data_cell">';

			 ?>
			 <!--<input style="font-size: 8pt" type="button" value="Delete" onClick="if (!confirmLink(this, 'Delete, are you sure?')) {return false;} window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=delete&ad_id=<?php echo $prams['ad_id']; ?>'"><br>-->
				<input type="button" style="font-size: 8pt" value="Edit" onClick="window.location='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?ad_id=<?php echo $prams['ad_id']; ?>'">

				<?php
			 
			 echo '</td>';
			 
		 }

		 echo_ad_list_data($admin);

		 if (($list_mode == 'USER' ) || ($admin)) {
			 /////////////////
			echo '<td class="list_data_cell"><img src="get_order_image.php?BID='.$BID.'&aid='.$prams['ad_id'].'"></td>';
			//////////////////
			echo '<td>';
			if ($prams['days_expire'] > 0) {


				if ($prams['published']!='Y') {
					$time_start = strtotime(gmdate('r'));
				} else {
					$time_start = strtotime($prams['date_published']." GMT");
				}

				$elapsed_time = strtotime(gmdate('r')) - $time_start;
				$elapsed_days = floor ($elapsed_time / 60 / 60 / 24);
				
				$exp_time =  ($prams['days_expire']  * 24 * 60 * 60);

				$exp_time_to_go = $exp_time - $elapsed_time;
				$exp_days_to_go =  floor ($exp_time_to_go / 60 / 60 / 24);

				$to_go = elapsedtime($exp_time_to_go);

				$elapsed = elapsedtime($elapsed_time);
				
				
				if  ($prams['status']=='expired') {
					$days = "<a href='orders.php'>".$label['ads_inc_expied_stat']."</a>";
				} elseif ($prams['date_published']=='') {
					$days = $label['ads_inc_nyp_stat'];
				} else {
					$days = str_replace ('%ELAPSED%', $elapsed, $label['ads_inc_elapsed_stat']);
					$days = str_replace ('%TO_GO%', $to_go, $days);
					//$days = "$elapsed elapsed<br> $to_go to go ";
				}

				//$days = $elapsed_time; 
				//print_r($prams);

			} else {

				$days = $label['ads_inc_nev_stat'];

			}
			echo $days;
			echo '</td>';
			/////////////////
			if ($prams['published']=='Y') {
				$pub =$label['ads_inc_pub_stat'];
			} else {
				$pub = $label['ads_inc_npub_stat'];
				
			}
			if ($prams['approved']=='Y') {
				$app = $label['ads_inc_app_stat'].', ';
			} else {
				$app = $label['ads_inc_napp_stat'].', ';
			}
			//$label['ad_list_st_'.$prams['status']]." 
			echo '<td class="list_data_cell">'.$app.$pub."</td>";
		}

		  ?>


		</tr>
		  <?php
			 //$prams[file_photo] = '';
			// $new_name='';
		}

		echo "</table>";
   
   } else {

      echo "<center><font size='2' face='Arial'><b>".$label["ads_not_found"].".</b></font></center>";

   }

   return $count;


}

########################################################
function delete_ads_files ($ad_id) {

	$sql = "select * from form_fields where form_id=1 ";
	$result = mysql_query ($sql) or die (mysql_error());

	while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

		$field_id = $row['field_id'];
		$field_type = $row['field_type'];

		if (($field_type == "FILE")) {
			
			deleteFile("ads", "ad_id", $ad_id, $field_id);
			
		}

		if (($field_type == "IMAGE")){
			
			deleteImage("ads", "ad_id", $ad_id, $field_id);
			
		}
		
	}


}

####################

function delete_ad ($ad_id) {

	 delete_ads_files ($ad_id);
  

   $sql = "delete FROM `ads` WHERE `ad_id`='".$ad_id."' ";
   $result = mysql_query($sql) or die (mysql_error().$sql);


}
################################

function search_category_tree_for_ads() {
	global $f2;

	if (func_num_args() > 0 ) {
		$cat_id = func_get_arg(0);
		
	} else {
		$cat_id = $_REQUEST[cat];
	}

	$sql = "select search_set from categories where category_id='$cat_id' ";
	$result2 = mysql_query ($sql) or die (mysql_error());
	$row = mysql_fetch_array($result2);
	$search_set = $row[search_set];

	$sql = "select * from form_fields where field_type='CATEGORY' AND form_id='1'";
	$result = mysql_query ($sql) or die (mysql_error());
	$i=0;

	if (mysql_num_rows($result) >0) {
		while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

			if ($i>0) {
				$where_cat .= " OR ";
			}

			$where_cat .= " `$row[field_id]` IN ($search_set) ";
			$i++;

		}
	}

	if ($where_cat=='') {
		return " AND 1=2 ";
	}

	if ($search_set=='') {
		return "";

	}

	return " AND ($where_cat) ";
	

}



####################

function search_category_for_ads() {
	global $f2;

	if (func_num_args() > 0 ) {
		$cat_id = func_get_arg(0);
		
	} else {
		$cat_id = $_REQUEST[cat];
	}

	$sql = "select * from form_fields where field_type='CATEGORY' AND form_id='1'";
	$result = mysql_query ($sql) or die (mysql_error());
	$i=0;

	if (mysql_num_rows($result) >0) {
		while ($row=mysql_fetch_array($result, MYSQL_ASSOC)) {

			if ($i>0) {
				$where_cat .= " OR ";
			}

			$where_cat .= " `$row[field_id]`='$cat_id' ";
			$i++;
		}
	}

	if ($where_cat=='') {
		return " AND 1=2 ";
	}

	return " AND ($where_cat) ";
	//$sql ="Select * from posts_table where $where_cat ";
	//echo $sql."<br/>";
	//$result2 = mysql_query ($sql) or die (mysql_error());

}
##################

function generate_ad_id () {

   $query ="SELECT max(`ad_id`) FROM `ads`";
   $result = mysql_query($query) or die(mysql_error());
   $row = mysql_fetch_row($result);
   $row[0]++;
   return $row[0];

}

#################

function temp_ad_exists($sid) {

	$query ="SELECT ad_id FROM `ads` where user_id='$sid' ";
	$result = mysql_query($query) or die(mysql_error());
	// $row = mysql_fetch_row($result);
	return mysql_num_rows($result);


}

################################################################

function insert_ad_data() {
	global $f2;

	if (func_num_args() > 0) {
		$admin = func_get_arg(0); // admin mode.

	}

	//print_r($_REQUEST);

	$user_id = $_SESSION['MDS_ID'];
	if ($user_id=='') {
		$user_id = addslashes(session_id());
	}
	$order_id = ($order_id)?$_REQUEST['order_id']:0;
	$ad_date = (gmdate("Y-m-d H:i:s")); 
	$banner_id = $_REQUEST['banner_id'];
	
	if ($_REQUEST['ad_id'] == '') {

		$ad_id = generate_ad_id ();
		$now = (gmdate("Y-m-d H:i:s"));

		//$extra_columns = get_sql_insert_fields(1);
		$extra_values = get_sql_insert_values(1, "ads", "ad_id", $_REQUEST['ad_id'], $user_id);
		$values = $ad_id . ", '" . $user_id . "', '" . mysql_real_escape_string($now) . "', " . $order_id . ", $banner_id" . $extra_values;

/*$sql = "INSERT INTO `ads` (`ad_id`, `user_id`, `ad_date`, `order_id`, `banner_id` " . $extra_columns .") " .
		"VALUES (" . $values . ") " .
		"ON DUPLICATE KEY UPDATE `ad_id`='" . $ad_id . "', `user_id` = '" . $user_id . "', `ad_date` = '" . mysql_real_escape_string($ad_date) . "', `order_id` = " . parseNull($order_id) . ", `banner_id` = '" . $banner_id ."'". get_sql_update_values(1, "ads", "ad_id", $_REQUEST['ad_id'], $user_id);
*/

		$sql = "REPLACE INTO ads VALUES (" . $values . ");";

	} else {
		
		$ad_id = intval($_REQUEST['ad_id']);

		if (!$admin) { // make sure that the logged in user is the owner of this ad.

			if (!is_numeric($_REQUEST['user_id'])) { // temp order (user_id = session_id())
				if ($_REQUEST['user_id']!=session_id()) return false;
			} else { // user is logged in
				$sql = "select user_id from `ads` WHERE ad_id='".intval($_REQUEST['ad_id'])."'";
				$result = mysql_query ($sql) or die(mysql_error());
				$row = @mysql_fetch_array($result);
				if ($_SESSION['MDS_ID']!==$row['user_id']) {
					
					return false; // not the owner, hacking attempt!
				}
			}
		}

		$now = (gmdate("Y-m-d H:i:s"));
		$sql = "UPDATE ads SET ad_date='$now'".get_sql_update_values(1, "ads", "ad_id", $_REQUEST['ad_id'], $user_id)." WHERE ad_id='".$ad_id."'";
		$f2->write_log($sql);
	}
	
	mysql_query($sql) or die("<br />SQL:[$sql]<br />ERROR:[".mysql_error()."]<br />");

	return $ad_id;
}
###############################################################
function validate_ad_data($form_id) {

	return validate_form_data(1);
	
	return $error;
}

################################################################

function update_blocks_with_ad($ad_id, $user_id) {
	global $prams, $f2;
	$prams = load_ad_values($ad_id);
	
	if ($prams['order_id']>0) {
		$sql = "UPDATE blocks SET alt_text='".addslashes(get_template_value('ALT_TEXT', 1))."', url='".addslashes(get_template_value('URL', 1))."'  WHERE order_id='".$prams['order_id']."' AND user_id='".$user_id."' ";
		mysql_query($sql) or die(mysql_error());
		$f2->debug("Updated blocks with ad URL, ALT_TEXT", $sql);
	}

}
?>