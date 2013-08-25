<?php
/**
 * @version		$Id: order_pixels.php 137 2011-04-18 19:48:11Z ryan $
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

//process_login();

//echo "session id:".session_id();
//echo " ".strlen(session_id());

//print_r($_SESSION);
//print_r($_REQUEST);

$BID = $f2->bid($_REQUEST['BID']);
$_SESSION['BID'] = $BID;

###############################
if ($_REQUEST['order_id']!='') {

	$_SESSION['MDS_order_id']=$_REQUEST['order_id'];
	
	if ((!is_numeric($_REQUEST['order_id'])) && ($_REQUEST['order_id']!='temp')) die();

}
################################
/*

Delete temporary order when the banner was chnaged.

*/

if ( ($_REQUEST['banner_change']!='') || ($_FILES['graphic']['tmp_name']!='') ) {


	delete_temp_order(session_id());
	
}

#################################

$tmp_image_file = get_tmp_img_name();

# load order from php
# only allowed 1 new order per banner

 $sql = "SELECT * from orders where user_id='".$_SESSION['MDS_ID']."' and status='new' and banner_id='$BID' ";
//$sql = "SELECT * from orders where order_id=".$_SESSION[MDS_order_id];
 $order_result = mysql_query($sql);
 $order_row = mysql_fetch_array($order_result);

 if (($order_row['user_id']!='') && $order_row['user_id']!=$_SESSION['MDS_ID']) { // do a test, just in case.
	 
	 die('you do not own this order!');

 }

 if (($_SESSION[MDS_order_id]=='')||(USE_AJAX=='YES')) { // guess the order id
	$_SESSION[MDS_order_id]=$order_row[order_id];
 }

###############################

load_banner_constants($BID);

// Update time stamp on temp order (if exists)

update_temp_order_timestamp();

###############################

$sql = "select block_id, status, user_id FROM blocks where banner_id='$BID' ";
$result = mysql_query ($sql) or die (mysql_error().$sql);
while ($row=mysql_fetch_array($result)) {
	$blocks[$row[block_id]] = $row['status'];
	//if (($row[user_id] == $_SESSION['MDS_ID']) && ($row['status']!='ordered') && ($row['status']!='sold')) {
	//	$blocks[$row[block_id]] = 'onorder';
	//	$order_exists = true;
	//}
	//echo $row[block_id]." ";
}

###############################

require ("header.php");

?>

<script type="text/javascript">

var browser_compatible=false;
var browser_checked=false;
var selectedBlocks= new Array();
var selBlocksIndex = 0;

function refreshSelectedLayers() {
	var pointer = document.getElementById('block_pointer');
		
} //End testing()
//End -J- Edit: Custom functions for resize bug

//Begin -J- Edit: Custom functions for resize bug
//Taken from http://www.quirksmode.org/js/findpos.html; but modified
function findPosX(obj)
{
	var curleft = 0;
	if (obj.offsetParent)
	{
		while (obj.offsetParent)
		{
			curleft += obj.offsetLeft
			obj = obj.offsetParent;
		}
	}
	else if (obj.x)
		curleft += obj.x;
	return curleft;
}

//Taken from http://www.quirksmode.org/js/findpos.html; but modified
function findPosY(obj)
{
	var curtop = 0;
	if (obj.offsetParent)
	{
		while (obj.offsetParent)
		{
			curtop += obj.offsetTop
			obj = obj.offsetParent;
		}
	}
	else if (obj.y)
		curtop += obj.y;
	return curtop;
}


function is_browser_compatible() {

	/*
userAgent should not be used, but since there is a bug in Opera, and there is
no way to detect this bug unless userAgent is used...
	*/

	if ((navigator.userAgent.indexOf("Opera") != -1)) {
		// does not work in Opera
		// cannot work out why?
		return false;
	} else {

		if (navigator.userAgent.indexOf("Gecko") != -1){
			// gecko based browsers should be ok
			// this includes safari?
			// continue to other tests..

		} else {
			if (navigator.userAgent.indexOf("MSIE") == -1) {
				return false; // unknown..
			}
		}

		//return false; // mozilla incompatible

	}

	// check if we can get by element id

	if (!document.getElementById ){
		
		return false;
	}

	// check if we can XMLHttpRequest

	var xmlhttp=false;
		/*@cc_on @*/
		/*@if (@_jscript_version >= 5)
		// JScript gives us Conditional compilation, we can cope with old IE versions.
		// and security blocked creation of the objects.
		 try {
		  xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
		 } catch (e) {
		  try {
		   xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		  } catch (E) {
		   xmlhttp = false;
		  }
		 }
		@end @*/
		if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
		  xmlhttp = new XMLHttpRequest();
		}

		if (!xmlhttp)
		{
			
			return false
		}

		return true;

}

///////////////////////////////////////////////
var trip_count = 0;

function check_selection(OffsetX, OffsetY) {

	var grid_width=<?php echo G_WIDTH*BLK_WIDTH; ?>;
	var grid_height=<?php echo G_HEIGHT*BLK_HEIGHT; ?>;

	var blk_width = <?php echo BLK_WIDTH; ?>;
	var blk_height = <?php echo BLK_HEIGHT; ?>;

	var map_x = OffsetX
	var map_y = OffsetY


	var clicked_block = ((map_x) / blk_width) + ((map_y/blk_height) * (grid_width/blk_width)) ;

	//var clicked_block = ((map_y*grid_width)+map_x) / blk_width ;
	if (clicked_block==0) {
		// convert to string
		clicked_block="0";

	}

	//alert ('clicked block is: '+clicked_block+" map_y: "+map_y+" map_x: "+map_x+" grid_width: "+grid_width);

	//return;

	//////////////////////////////////////////////////
	// Trip to the database.
	//////////////////////////////////////////////////

		var xmlhttp=false;
		/*@cc_on @*/
		/*@if (@_jscript_version >= 5)
		// JScript gives us Conditional compilation, we can cope with old IE versions.
		// and security blocked creation of the objects.
		 try {
		  xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
		 } catch (e) {
		  try {
		   xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
		  } catch (E) {
		   xmlhttp = false;
		  }
		 }
		@end @*/
		if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
		  xmlhttp = new XMLHttpRequest();
		}

		// Note: do not use &amp; for & here
		xmlhttp.open("GET", "check_selection.php?user_id=<?php echo $_SESSION['MDS_ID'];?>&map_x="+OffsetX+"&map_y="+OffsetY+"&block_id="+clicked_block+"&BID=<?php 
		$sesname = ini_get('session.name');
		if ($sesname==''){
			$sesname = 'PHPSESSID';
		}
		echo $BID."&amp;t=".time()."&amp;$sesname=".session_id(); ?>",true);

		//alert("before trup_count:"+trip_count);

		if (trip_count != 0){ // trip_count: global variable counts how many times it goes to the server
			document.getElementById('submit_button1').disabled=true;
			document.getElementById('submit_button2').disabled=true;
			var pointer = document.getElementById('block_pointer');
			pointer.style.cursor='wait';
			var pixelimg = document.getElementById('pixelimg');
			pixelimg.style.cursor='wait';
			
		}
		
		xmlhttp.onreadystatechange=function() {
			if (xmlhttp.readyState==4) {
				//
				
				//alert(xmlhttp.responseText+clicked_block);

				if (xmlhttp.responseText.indexOf('E432')>-1) { // bad selection - not available
					alert(xmlhttp.responseText);
					is_moving=true;

				}

				document.getElementById('submit_button1').disabled=false;
				document.getElementById('submit_button2').disabled=false;

				var pointer = document.getElementById('block_pointer');
				pointer.style.cursor='pointer';
				var pixelimg = document.getElementById('pixelimg');
				pixelimg.style.cursor='pointer';
				
			}
			
		}

		xmlhttp.send(null)

}

//////////////////////////////////////////
// Initialize
var block_str = "<?php echo $order_row[blocks]; ?>";
var trip_count = 0;

//////////////////////////////////

var pos;
function getObjCoords (obj) {
  var pos = { x: 0, y: 0 };
	var curtop = 0;
	var curleft = 0;
	if (obj.offsetParent)
	{
		while (obj.offsetParent)
		{
			curtop += obj.offsetTop
			curleft += obj.offsetLeft
			obj = obj.offsetParent;
		}
	}
	else if (obj.y) {
		curtop += obj.y;
		curleft += obj.x;
	}
	pos.x = curleft;
	pos.y = curtop;
	return pos;
}

///////////////////////////////////////////////////

function show_pointer (e) {
button = document.getElementById('submit_button1');
	
	//return;
	if (!browser_checked){
		browser_compatible = is_browser_compatible();
	}

	if (!browser_compatible){
		return false;
	}

	browser_checked=true;

	var pixelimg = document.getElementById('pixelimg');
	var pointer = document.getElementById('block_pointer');
	//pointer.style.visibility='visible';
	if(!is_moving) return;

	//if (!pos) {
		var pos = getObjCoords(pixelimg);
	//}
	
	if (e.offsetX != undefined) {
		var OffsetX = e.offsetX;
		var OffsetY = e.offsetY;
	} else {
		var OffsetX = e.pageX - pos.x;
		var OffsetY = e.pageY - pos.y;

	}

	OffsetX = Math.floor (OffsetX / <?php echo BLK_WIDTH; ?>)*<?php echo BLK_WIDTH; ?>;
	OffsetY = Math.floor (OffsetY / <?php echo BLK_HEIGHT; ?>)*<?php echo BLK_HEIGHT; ?>;

	//button = document.getElementById('submit_button1');
	//button.value='OffsetX:'+OffsetX+" OffsetY:"+OffsetY;


	if (isNaN(OffsetX)||isNaN(OffsetY))
	{
		return
		//alert(OffsetX)
	}
	
	if (pointer_height+OffsetY > <?php echo G_HEIGHT*BLK_HEIGHT;?>)
	{
		
	} else {
		pointer.style.top=pos.y+OffsetY + 'px';
		pointer.map_y=OffsetY;	
	}


	//button = document.getElementById('submit_button1');
	//button.value=''+OffsetX+'/'+OffsetY+' pointer left:'+pointer.style.left+" pointer.map_x:"+pointer.map_x+pointer_width;

	if (pointer_width+OffsetX > <?php echo G_WIDTH*BLK_WIDTH;?>)
	{
		
	} else {
		pointer.map_x=pos.x+OffsetX;
	
		pointer.style.left=pos.x+OffsetX + 'px';
	}

	

	return true;

	

}

var i_count =0;
///////////////////////



function show_pointer2 (e) {
	//function called when mouse is over the actual pinting image

	if(!is_moving) return;

	var pixelimg = document.getElementById('pixelimg');
	var pointer = document.getElementById('block_pointer');

	var pos = getObjCoords(pixelimg);
	var p_pos = getObjCoords(pointer);
	
	if (e.offsetX != undefined) {
		var OffsetX = e.offsetX;
		var OffsetY = e.offsetY;
		var ie = true; 
	} else {
		var OffsetX = e.pageX - pos.x;
		var OffsetY = e.pageY - pos.y;
		var ie = false;
	}
	// normalize.
	//OffsetX = Math.floor (OffsetX / <?php echo BLK_WIDTH; ?>)*<?php echo BLK_WIDTH; ?>;
	//OffsetY = Math.floor (OffsetY / <?php echo BLK_HEIGHT; ?>)*<?php echo BLK_HEIGHT; ?>;


	if (ie) { // special routine for internet explorer...
	
			
		rel_posx = p_pos.x-pos.x;
		rel_posy = p_pos.y-pos.y;
		
		pointer.map_x = rel_posx;
		pointer.map_y = rel_posy;

	
		if (isNaN(OffsetX)||isNaN(OffsetY)){
			return
		}
		
		if (OffsetX>=<?php echo BLK_WIDTH; ?>) { // move the pointer right
			if (rel_posx+pointer_width >= <?php echo G_WIDTH*BLK_WIDTH; ?>) {
			} else {
				//pointer.map_x=rel_posx;
				//pointer.style.left=pos.x+rel_posx;

				pointer.map_x=p_pos.x+<?php echo BLK_WIDTH; ?>;
				pointer.style.left=pointer.map_x + 'px';
			}
			
		}

		//i_count++; 
		//button = document.getElementById('submit_button1');
		//button.value='OffsetX:'+OffsetX+" OffsetY:"+OffsetY+" map_x:"+pointer.map_x+" map_y:"+pointer.map_y
		//	+" left:"+pointer.style.left+" top:"+pointer.style.top+" relposx"+rel_posx;

		if (OffsetY><?php echo BLK_HEIGHT; ?>) { // move the pointer down
		
			if (rel_posy+pointer_height >= <?php echo G_HEIGHT*BLK_HEIGHT; ?>) {

				//return
			} else {

				pointer.map_y=p_pos.y+<?php echo BLK_HEIGHT; ?>;
				pointer.style.top=pointer.map_y + 'px';
			}	
		}
		
	} else {

		tOffsetX = Math.floor (OffsetX / <?php echo BLK_WIDTH; ?>)*<?php echo BLK_WIDTH; ?>;
		tOffsetY = Math.floor (OffsetY / <?php echo BLK_HEIGHT; ?>)*<?php echo BLK_HEIGHT; ?>;

		
		if (isNaN(OffsetX)||isNaN(OffsetY)) {
			//alert ('naan');
			return
			
		}
		if (OffsetX>tOffsetX) {

			if (pointer_width+tOffsetX > <?php echo G_WIDTH*BLK_WIDTH;?>) {
				// dont move left
			} else {
				pointer.map_x=tOffsetX;
				pointer.style.left=pos.x+tOffsetX + 'px';
			}

		}

		if (OffsetY>tOffsetY) {

			if (pointer_height+tOffsetY > <?php echo G_HEIGHT*BLK_HEIGHT;?>)
			{ // dont move down
				
			} else {

				pointer.style.top=pos.y+tOffsetY + 'px';
				pointer.map_y=tOffsetY;	
			}

		}

	//	i_count++;
//	button = document.getElementById('submit_button1');
//	button.value='OffsetX:'+OffsetX+" OffsetY:"+OffsetY+" map_x:"+pointer.map_x+" map_y:"+pointer.map_y
//		+" left:"+pointer.style.left+" top:"+pointer.style.top+"   "+i_count+" tOffsetX:"+tOffsetX+" tOffsetY:"+tOffsetY;



	}
	
	

}

//////
function get_clicked_block() {

	var pointer = document.getElementById('block_pointer');

	var grid_width=<?php echo G_WIDTH*BLK_WIDTH;?>;
	var grid_height=<?php echo G_HEIGHT*BLK_HEIGHT;?>;

	var blk_width = <?php echo BLK_WIDTH; ?>;
	var blk_height = <?php echo BLK_HEIGHT; ?>;

	//var clicked_block = ((pointer.map_y*grid_width)+pointer.map_x)/<?php echo BLK_HEIGHT; ?> ;
	
	var clicked_block = ((pointer.map_x) / blk_width) + ((pointer.map_y/blk_height) * (grid_width/blk_width)) ;

	if (clicked_block==0) {
		clicked_block="0";// convert to string

	}
	return clicked_block;
	//alert ('clicked block'+clicked_block)



}
////////////////////


function do_block_click() {
	
	if (is_moving) {
		var cb = get_clicked_block();
		var pointer = document.getElementById('block_pointer');
		trip_count=1;
		check_selection(pointer.map_x, pointer.map_y);
		low_x = pointer.map_x;
		low_y = pointer.map_y;

		
		is_moving = false;
	} else {
		is_moving = true;
	}

	
	

}

var low_x=0;
var low_y=0;

<?php




// get the top-most, left-most block
$low_x = G_WIDTH*BLK_WIDTH;
$low_y = G_HEIGHT*BLK_HEIGHT;

//$sql = "select x,y  from blocks where session_id='".session_id()."' ";

$sql = "SELECT block_info FROM temp_orders WHERE session_id='".addslashes(session_id())."' ";

//echo $sql;
$result = mysql_query($sql);
$row = mysql_fetch_array($result);


$filename = SERVER_PATH_TO_ADMIN.'temp/'."info_".md5(session_id()).".txt";
if (file_exists($filename)) {

$fh = fopen ($filename, 'rb');
$block_info = fread($fh, filesize($filename));
fclose($fh);

}
//$block_info = unserialize ($row['block_info']);
$block_info = unserialize ($block_info);

//echo "size of block_info:".sizeof($block_info[0]);
if (is_array($block_info)) {

//print_r ($block_info);

	foreach ($block_info as $block) {

		if ($low_x >= $block['map_x']) {
			$low_x = $block['map_x'];
			$init = true;
		}

		if ($low_y >= $block['map_y']) {
			$low_y = $block['map_y'];
			$init = true;
		}

	}

}

if (($low_x == (G_WIDTH*BLK_WIDTH)) && ($low_y == (G_HEIGHT*BLK_HEIGHT))) {

}

if (!$init) {
	$low_x=0;
	$low_y=0;
	$is_moving = " is_moving=true ";
} else {
	$is_moving = " is_moving=false ";
}

echo "low_x = $low_x;";
echo "low_y = $low_y; $is_moving";

?>

function move_image_to_selection() {

	
	var pointer = document.getElementById('block_pointer');
	var pixelimg = document.getElementById('pixelimg');
	var pos = getObjCoords (pixelimg)

	pointer.style.top=pos.y+low_y + 'px';
	pointer.map_y=low_y;

	pointer.style.left=pos.x+low_x + 'px';
	pointer.map_x=low_x;

	pointer.style.visibility='visible';
	//show_pointer ();

}

</script>
<?php

if ($_FILES['graphic']['tmp_name']!='') {

	$uploaddir = SERVER_PATH_TO_ADMIN."temp/";

	//$parts = split ('\.', $_FILES['graphic']['name']);
	$parts = explode('.', $_FILES['graphic']['name']);
	$ext = strtolower(array_pop($parts));

	// CHECK THE EXTENSION TO MAKE SURE IT IS ALLOWED
	$ALLOWED_EXT= 'jpg, jpeg, gif, png';
	$ext_list = preg_split ("/[\s,]+/i", ($ALLOWED_EXT));	


	if (!in_array($ext, $ext_list)) {
		$error .=  "<strong><font color='red'>".$label['advertiser_file_type_not_supp']." ($ext)</font></strong><br />";
		$image_changed_flag = false;
	
	} 
	if ($error) {
		//echo "<font color='red'>Error, image upload failed</font>";
		echo $error;

	} else {

	
		// clean up is handled by the delete_temp_order($sid) function...

		delete_temp_order(session_id());

		// delete temp_* files older than 24 hours 
		$dh = opendir ($uploaddir) ;
		while (($file = readdir($dh)) !== false) {
			
			$elapsed_time = 60*60*24; // 24 hours

			// delete old files
			$stat =stat($uploaddir.$file);
			if ($stat[9] < (time()-$elapsed_time)) {
				if (strpos( $file, 'tmp_'.md5(session_id())) !== false) {
					unlink($uploaddir.$file);
				}

			}
			
		}
		
		$uploadfile = $uploaddir . "tmp_".md5(session_id()).".$ext";

		if (move_uploaded_file($_FILES['graphic']['tmp_name'], $uploadfile)) {
			//echo "File is valid, and was successfully uploaded.\n";
			$tmp_image_file = $uploadfile;

			// check the file size for min an max blocks.

			$size = getimagesize($tmp_image_file);
			$size = get_required_size($size[0], $size[1]);
			$pixel_count = $size[0]*$size[1];
			$block_size = $pixel_count / (BLK_WIDTH * BLK_HEIGHT);

			if (($block_size > G_MAX_BLOCKS) && (G_MAX_BLOCKS>0)) {

				$limit = G_MAX_BLOCKS * BLK_WIDTH * BLK_HEIGHT;

				$label['max_pixels_required'] = str_replace('%MAX_PIXELS%', $limit, $label['max_pixels_required']);	
				$label['max_pixels_required'] = str_replace('%COUNT%', $pixel_count, $label['max_pixels_required']);	
				echo "<strong><font color='red'>";
				echo $label['max_pixels_required'];
				echo "</font></strong>";
				unlink ($tmp_image_file);
				unset($tmp_image_file);

			} elseif (($block_size < G_MIN_BLOCKS) && (G_MIN_BLOCKS>0)) {

				$label['min_pixels_required'] = str_replace('%COUNT%', $pixel_count, $label['min_pixels_required']);	
				$label['min_pixels_required'] = str_replace('%MIN_PIXELS%', G_MIN_BLOCKS*BLK_WIDTH * BLK_HEIGHT , $label['min_pixels_required']);	
				echo "<strong><font color='red'>";
				echo $label['min_pixels_required'];
				echo "</font></strong>";
				unlink ($tmp_image_file);
				unset($tmp_image_file);

			}
			
		} else {
			//echo "Possible file upload attack!\n";
			echo $label['pixel_upload_failed'];
		}

	}

	
	




}

// pointer.png


?>

<span id="block_pointer" onmousemove="show_pointer2(event)" onclick="do_block_click(event);" style='cursor: pointer;position:absolute;left:0px; top:0px;background-color:#FFFFFF; visibility:hidden '><img src="get_pointer_graphic.php?BID=<?php echo $BID; ?>" alt="" /></span>


<p>
<?php 
show_nav_status (1);
?>
</p>


<p id="select_status" ><?php echo $cannot_sel; ?></p>

<?php

$sql = "SELECT * FROM banners order by `name` ";
$res = mysql_query($sql);

if (mysql_num_rows($res)>1) {
?>
<div class="fancy_heading" style="width:85%;"><?php echo $label['advertiser_sel_pixel_inv_head']; ?></div>
<p >
<?php

$label['advertiser_sel_select_intro'] = str_replace("%IMAGE_COUNT%",mysql_num_rows($res), $label['advertiser_sel_select_intro']);

//echo $label['advertiser_sel_select_intro'];

?>

</p>
<p>
<?php display_banner_selecton_form($BID, $_SESSION['MDS_order_id'], $res); ?>
</p>
<?php
}



if ($order_exists) {
	echo "<p>".$label['advertiser_order_not_confirmed']."</p>";

}
?>

<?php

$has_packages = banner_get_packages($BID);
if ($has_packages) {
	display_package_options_table($BID, '', false);

} else {
	display_price_table($BID);
}



?>
<div class="fancy_heading" style="width:85%;"><?php echo $label['pixel_uploaded_head']; ?></div>
<p>
<?php $label['upload_pix_description']; ?>
</p>
<p>
<?php

if (USE_AJAX=='SIMPLE') {
	$order_page = 'order_pixels.php';
} else {
	$order_page = 'select.php';
}

?>
<form method='post' action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>" enctype="multipart/form-data" >
<strong><?php $label['upload_your_pix']; ?></strong> <input type='file' name='graphic' style=' font-size:14px;' /><br />
<input type='hidden' name='BID' value='<?php echo $BID;?>' />
<input type='submit' value='<?php echo $label['pix_upload_button']; ?>' style=' font-size:18px;' />
</p>

<?php



?>

</form>

<?php



if (!$tmp_image_file) {

	?>

	<?php


} else {



	?>

	<div class="fancy_heading" style="margin-top:20px;width:85%;"><?php echo $label['your_uploaded_pix']; ?></div>
	<p>
	<?php

	echo "<img style=\"border:0px;\" src='get_pointer_graphic.php?BID=".$BID."' alt=\"\" /><br />";

	$size = getimagesize($tmp_image_file);
	
	?><?php 
	$label['upload_image_size'] = str_replace("%WIDTH%", $size[0], $label['upload_image_size']);
	$label['upload_image_size'] = str_replace("%HEIGHT%", $size[1], $label['upload_image_size']);

	echo $label['upload_image_size']; 
	?>
	<br />
	<?php
	
	$size = get_required_size($size[0], $size[1]);

	$pixel_count = $size[0]*$size[1];
	$block_size = $pixel_count / (BLK_WIDTH * BLK_HEIGHT);
	$label['advertiser_require_pur'] = str_replace('%PIXEL_COUNT%',$pixel_count , $label['advertiser_require_pur']);
	$label['advertiser_require_pur'] = str_replace('%BLOCK_COUNT%',$block_size, $label['advertiser_require_pur']);
	echo $label['advertiser_require_pur'];
	?>
	
	</p>
	<?php //echo $label['advertiser_select_instructions']; ?>
	

	<form method="post" action="order_pixels.php" name='pixel_form'>
	<input type="hidden" name="jEditOrder" value="true">
	
	<p>
	<input  class='big_button' <?php if ($_REQUEST['order_id']!='temp') { echo 'disabled'; } ?> type="button" name='submit_button1' id='submit_button1' value='<?php echo $label['advertiser_write_ad_button']; ?>' onclick='document.form1.submit()'>
	
	</p>

	<input type="hidden" value="1" name="select">
	<input type="hidden" value="<?php echo $BID;?>" name="BID">


	<img style="cursor: pointer;" id="pixelimg" <?php if ((USE_AJAX=='YES') || (USE_AJAX=='SIMPLE')) { ?> onmousemove="show_pointer(event)"  <?php } ?> type="image" name="map" value='Select Pixels.' width="<?php echo G_WIDTH*BLK_WIDTH; ?>"  height="<?php echo G_HEIGHT*BLK_HEIGHT; ?>" src="show_selection.php?BID=<?php echo $BID;?>&amp;gud=<?php echo time();?>" alt="" />

	</form>

	<input type="hidden" name="action" value="select">
	</form>
	<div style='background-color: #ffffff; border-color:#C0C0C0; border-style:solid;padding:10px'>
	<hr>

	<form method="post" action="write_ad.php" name="form1">
	<input type="hidden" name="package" value="">
	<input type="hidden" name="selected_pixels" value=''>
	<input type="hidden" name="order_id" value="<?php echo $_SESSION[MDS_order_id]; ?>">
	<input type="hidden" value="<?php echo $BID;?>" name="BID">
	<input type="submit" class='big_button' disabled  name='submit_button2' id='submit_button2' value='<?php echo $label['advertiser_write_ad_button']; ?>'>
	<hr />
	</form>

	<script type="text/javascript">

	document.form1.selected_pixels.value=block_str;

	</script>

	</div>
<script type="text/javascript">

var pointer_width = <?php echo $size[0]; ?>;
var pointer_height =  <?php echo $size[1]; ?>; 
window.onresize = move_image_to_selection;
window.onload = move_image_to_selection;
move_image_to_selection();

</script>

<?php 

	

	//print_r($str);
}

require "footer.php";

?>