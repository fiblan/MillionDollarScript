<?php
/**
 * @version		$Id: lists.inc.php 137 2011-04-18 19:48:11Z ryan $
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

$column_list; // column structure, global variable to cache the column info
$column_info;

/*
Display table heading, initalize column_struct.
*/
function echo_list_head_data($form_id, $admin) { 
	global $f2, $CACHE_ENABLED;
	if ($CACHE_ENABLED=='YES') {
		$colspan=0;
		if (!is_numeric($form_id)) return false;
		if (sizeof($admin)>1) return false;
		eval ('$colspan = cache_echo_list'.$form_id.'_head($admin);');
		
		return $colspan;

	}

	global $q_string, $column_list, $column_info;

	$ord = $_REQUEST['ord'];
	if ($ord=='asc') {
		$ord = 'desc';
	}elseif ($ord=='desc') {
		$ord = 'asc';
	} else {
		$ord = 'desc';
	}


	$colspan = 0;

	$sql = "SELECT * FROM form_lists where form_id='$form_id' ORDER BY sort_order ASC ";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$colspan++;
		$column_list[$row['template_tag']]=$row['template_tag'];
		$column_info[$row['template_tag']]['trunc'] = $row['truncate_length'];
		$column_info[$row['template_tag']]['admin'] = $row['admin'];
		$column_info[$row['template_tag']]['link'] = $row['linked'];
		$column_info[$row['template_tag']]['is_bold'] = $row['is_bold'];
		$column_info[$row['template_tag']]['no_wrap'] = $row['no_wrap'];
		$column_info[$row['template_tag']]['clean'] = $row['clean_format'];
		//$column_info[$row['template_tag']]['is_sortable'] = $row['is_sortable'];
		if (($row['admin']=='Y') && (!$admin)) {
			continue; // do not render this column
		}
		?>
		<td class="list_header_cell" <?php if ($row['template_tag']=='POST_SUMMARY') echo ' width="100%" '; ?>>
		<?php

		if ($row['is_sortable']=='Y') { // show column order by link?
			$field_id = get_template_field_id ($row['template_tag'], $form_id);
			?><a class="list_header_cell" href='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?order_by=<?php echo $field_id;?>&ord=<?php echo $ord; ?><?php echo $q_string;?>'>
			<?php
		}
		echo get_template_field_label ($row['template_tag'], $form_id);
		if ($row['is_sortable']=='Y') { // show column order by link?
			?></a><?php
		}
		?>
		</td>
		<?php
	}
	?>
		<?php

		return $colspan;


}





########################################

function echo_ad_list_data($admin) {

	global $f2, $column_list, $column_info, $label, $cur_offset, $order_str, $q_offset, $show_emp, $cat, $list_mode;

	if ($_REQUEST['order_by']!='') {

			$ord = $_REQUEST['ord'];
			if ($ord=='asc') {
				$ord = 'desc';
			}elseif ($ord=='desc') {
				$ord = 'asc';
			} else {
				$ord = 'desc';
			}

			$order_str = "&order_by=".$_REQUEST['order_by']."&ord=".$ord;
		
	}

	
	foreach ($column_list as $template_tag) {

		$val = get_template_value ($template_tag, 1, $admin);
		//$val = $val.$template_tag;
		if (($column_info[$template_tag]['admin']=='Y') && (!$admin)) {
				continue; // do not render this column
		}

		if ($column_info[$template_tag]['trunc']>0) {
			$val = truncate_html_str($val, $column_info[$template_tag]['trunc'], $trunc_str_len);
		}

		
	
		// process the value depending on what kind of template tag it was given.
		if ($template_tag=='DATE') {

			$init_date = strtotime(trim_date($val)." GMT"); // the last date modified
			$dst_date =  strtotime(trim_date((gmdate("r")))." GMT" ); // now
			if (!$init_date) {
			   $days = "x";
			} else {
				$diff = $dst_date-$init_date; 
				$days = floor($diff/60/60/24); 
			}
			//echo $days;
			$FORMATTED_DATE = get_formatted_date(get_local_time($val));
			$val = $FORMATTED_DATE."<br>";
			
			if ($days==0) {
				$val = $val. '<span class="today"><b>'.$label["ads_list_today"].'</span>';

			} elseif (($days > 0) && ($days < 2)) { 
				$val = $val .'<span class="days_ago">'. $days." ".$label["ads_list_day_ago"]."</span>"; 
			} elseif (($days > 1) && ($days < 8)) { 
				$val = $val .'<span class="days_ago">'. $days." ". $label["ads_list_days_ago"]."</span>"; 
			} elseif (($days >= 8)) { 
				$val = $val .'<span class="days_ago2">'.$days." ". $label["ads_list_days_ago"]."</span>";
			}
			


		}

		

		if ($column_info[$template_tag]['is_bold']=='Y') {
			$b1="<b>"; $b2="</b>";
		} else {
			$b1='';$b2='';
		}

		if ($column_info[$template_tag]['clean']=='Y') { // fix up punctuation spacing

			$val = preg_replace('/ *(,|\.|\?|!|\/|\\\) */i', '$1 ', $val);
			
		}

		if ($column_info[$template_tag]['link']=='Y')  { // Render as a Link to the record?

			$AD_ID = get_template_value ('AD_ID', 1, $admin);

			$val = '<a href="' .htmlentities($_SERVER['PHP_SELF']).'?ad_id='.$AD_ID.'&offset='.$cur_offset.$order_str.$q_string.'"'; 
			
			/// IMAGE PREVIEW MOUSEOVER Code
			   // Note: to have this feature working, you must have a template tag called 'IMAGE' defined in the resume form
			   define ('PREVIEW_AD', 'YES');
			   if (PREVIEW_AD == 'YES') {

					$ALT_TEXT = get_template_value ('ALT_TEXT', 1, $admin);
					//$AD_ID = get_template_value ('AD_ID', 1, $admin);

					$js_str = " onmousemove=\"sB(event,'".htmlspecialchars(str_replace("'","\'",($ALT_TEXT)))."',this, ".$AD_ID.")\" onmouseout=\"hI()\" ";

					$val = $val. $js_str;

			   }
			   
			   $val = $val.'>'.get_template_value ($template_tag, 1, $admin)."</a>";

		}


		?>
		<td class="list_data_cell" <?php if ($column_info[$template_tag]['no_wrap']=='Y') { echo ' nowrap '; } ?>>
			<?php echo $b1.$val.$b2; ?>
		</td>

		<?php

	}


}



########################################
# Admin Stuff
########################################

function field_type_option_list ($form_id, $selected) {

	global $f2, $label;

	$col_row['field_id'] = $selected;

	switch ($form_id) {

		
		case 1:
			
			?>
			<option <?php if ($col_row['field_id']=='ad_date') { echo ' selected '; }?> value="ad_date"><?php echo 'Ad Date'; ?></option>
			<option <?php if ($col_row['field_id']=='ad_id') { echo ' selected '; }?> value="ad_id"><?php echo 'Ad ID';?></option>
			<option <?php if ($col_row['field_id']=='user_id') { echo ' selected '; }?> value="user_id"><?php echo 'User ID';?></option>
			<option <?php if ($col_row['field_id']=='order_id') { echo ' selected '; }?> value="order_id"><?php echo 'Order ID';?></option>
			<option <?php if ($col_row['field_id']=='grid_id') { echo ' selected '; }?> value="grid_id"><?php echo 'Grid ID';?></option>
			<?php
			break;
		
		default:
			break;


	}

	$sql = "SELECT *, t2.field_label AS NAME FROM form_fields AS t1, form_field_translations AS t2 WHERE t1.field_id=t2.field_id AND lang='".$_SESSION['MDS_LANG']."' AND t1.form_id='".$form_id."' AND  field_type != 'SEPERATOR' AND field_type != 'BLANK' AND field_type != 'NOTE' and t2.field_label <>'' " ;
	$result = mysql_query($sql);
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

		if ($row['field_id']==$selected) {
			$sel = " selected ";

		} else {
			$sel = "";

		}

		echo "<option $sel value='".$row['field_id']."'>".escape_html($row['NAME'])."</option>\n";


	}


}


###################################################################

function echo_list_head_data_admin($form_id) { 

	global $q_string, $column_list;


	$sql = "SELECT * FROM form_lists where form_id='$form_id' ORDER BY sort_order ASC ";
	$result = mysql_query($sql);
	while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
		$column_list[$row['field_id']]=$row['template_tag'];
		//echo $row['template_tag'];
		?>
		<td class="list_header_cell" nowrap>
		<?php echo '<small>('.$row['sort_order'].')</small>'; ?>
		<a href='<?php echo htmlentities($_SERVER['PHP_SELF']);?>?action=edit&column_id=<?php echo $row['column_id'];?>'><?php echo get_template_field_label ($row['template_tag'], $form_id);?></a> <a onClick="return confirmLink(this, 'Delete this column from view, are you sure?') " href="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?action=del&column_id=<?php echo $row['column_id']?>"><IMG src='delete.gif' width='16' height='16' border='0' alt='Delete'></a> 
<a href="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>?action=edit&column_id=<?php echo $row['column_id']; ?>">
   <IMG alt="edit" src="edit.gif" width="16" height="16" border="0" alt="Edit">
		</td>
		<?php
	}
	?>
		<?php


}


?>