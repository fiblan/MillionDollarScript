<?php
/**
 * @version		$Id: select.php 137 2011-04-18 19:48:11Z ryan $
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

if ($f2->bid($_REQUEST['BID'])!='') {
	$BID = $f2->bid($_REQUEST['BID']);

	$_SESSION['BID']=$BID;


} else {
	$BID = $_SESSION['BID'];
}

load_banner_constants($BID);

if ($_REQUEST['order_id']!='') {

	$_SESSION['MDS_order_id']=$_REQUEST['order_id'];
	if (!is_numeric($_REQUEST['order_id'])) die();


}

if (!is_numeric($BID)) die();

//Begin -J- Edit: Force New Order on load of page unless user clicked "Edit" button on confirm/complete page (indicated by $_GET['jEditOrder'])
//Important: This chunk was moved from below the "load order from php" section

if (($_REQUEST['banner_change']!='')) {

	$sql = "SELECT * FROM orders where status='new' and banner_id='$BID' and user_id='".$_SESSION['MDS_ID']."'";
//	echo $sql;
	
	$res = mysql_query ($sql) or die (mysql_error().$sql);
//		echo "here:".mysql_num_rows($result);
	while ($row=mysql_fetch_array($res, MYSQL_ASSOC)) {

		$sql = "delete from orders where order_id=".$row['order_id'];
		$result = mysql_query ($sql) or die (mysql_error().$sql);
		$sql = "delete from blocks where order_id=".$row['order_id'];
		$result = mysql_query ($sql) or die (mysql_error().$sql);
	}

	//echo "deleted pixels";
}

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

/*
old_order_id comes the form which allows users to change banners.

When the users change the grid, the order that was in-progress is deleted
from the system. The user can start making a new order for the new banner.

(Only one order-in-progress is allowed)

*/


if ($_REQUEST['banner_change']!='') {
		
		$_SESSION[MDS_order_id] = ''; // clear the current order

		$sql = "SELECT * FROM orders where status='new' and banner_id='$BID' and user_id='".$_SESSION['MDS_ID']."'";
//	echo $sql;
		
		$res = mysql_query ($sql) or die (mysql_error().$sql);
//		echo "here:".mysql_num_rows($result);
		while ($row=mysql_fetch_array($res, MYSQL_ASSOC)) {
			$sql = "delete from orders where order_id=".$row['order_id'];
			$result = mysql_query ($sql) or die (mysql_error().$sql);
			$sql = "delete from blocks where order_id=".$row['order_id'];
			$result = mysql_query ($sql) or die (mysql_error().$sql);
		}

	//	echo "deleted pixels";
	}


$sql = "select * from banners where banner_id='$BID'";
$result = mysql_query ($sql) or die (mysql_error().$sql);
$b_row = mysql_fetch_array($result);

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

if ($_REQUEST[select]!='') {

	if ($_REQUEST[sel_mode]=='sel4') {

		$max_x = $b_row[grid_width]*BLK_WIDTH;
		$max_y = $b_row[grid_height]*BLK_HEIGHT;

		$cannot_sel = select_block ($_REQUEST['map_x'], $_REQUEST['map_y']);
		if (($_REQUEST['map_x']+BLK_WIDTH <= $max_x)) {
			$cannot_sel = select_block ($_REQUEST['map_x']+BLK_WIDTH, $_REQUEST['map_y']);
		}
		if ( ($_REQUEST['map_y']+BLK_HEIGHT <= $max_y)) {
			$cannot_sel = select_block ($_REQUEST['map_x'], $_REQUEST['map_y']+BLK_HEIGHT);
		}
		if (($_REQUEST['map_x']+BLK_WIDTH <= $max_x) && ($_REQUEST['map_y']+BLK_HEIGHT <= $max_y)) {
			$cannot_sel = select_block ($_REQUEST['map_x']+BLK_WIDTH, $_REQUEST['map_y']+BLK_HEIGHT);
		}

	} elseif ($_REQUEST[sel_mode]=='sel6') {

		$max_x = $b_row[grid_width]*BLK_WIDTH;
		$max_y = $b_row[grid_height]*BLK_HEIGHT;

		$cannot_sel = select_block ($_REQUEST['map_x'], $_REQUEST['map_y']);

		if (($_REQUEST['map_x']+BLK_WIDTH <= $max_x)) {
			$cannot_sel = select_block ($_REQUEST['map_x']+BLK_WIDTH, $_REQUEST['map_y']);
		}
		if ( ($_REQUEST['map_y']+BLK_HEIGHT <= $max_y)) {
			$cannot_sel = select_block ($_REQUEST['map_x'], $_REQUEST['map_y']+BLK_HEIGHT);
		}
		if (($_REQUEST['map_x']+BLK_WIDTH <= $max_x) && ($_REQUEST['map_y']+BLK_HEIGHT <= $max_y)) {
			$cannot_sel = select_block ($_REQUEST['map_x']+BLK_WIDTH, $_REQUEST['map_y']+BLK_HEIGHT);
		}

		if (($_REQUEST['map_x']+(BLK_WIDTH*2) <= $max_x)) {
			$cannot_sel = select_block ($_REQUEST['map_x']+(BLK_WIDTH*2), $_REQUEST['map_y']);
		}

		if (($_REQUEST['map_x']+(BLK_WIDTH*2) <= $max_x) && ($_REQUEST['map_y']+BLK_HEIGHT <= $max_y)) {
			$cannot_sel = select_block ($_REQUEST['map_x']+(BLK_WIDTH*2), $_REQUEST['map_y']+BLK_HEIGHT);
		}

		
	} else {

		$cannot_sel = select_block ($_REQUEST['map_x'], $_REQUEST['map_y']);

	}

}




require ("header.php");

//print_r($_REQUEST);

?>

<div id="blocks">

</div>

<script language="JavaScript">

var browser_compatible=false;
var browser_checked=false;
var selectedBlocks= new Array();
var selBlocksIndex = 0;

window.onresize = refreshSelectedLayers;

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

		//return false; // mozilla incopatible

	}

	// check if we can get by element id

	if (!document.getElementById )
	{
		
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

function update_order() {
	//alert ('updated');
	document.form1.selected_pixels.value=block_str;
}



////////////////////////////////////////////////

function reserve_block (clicked_block, OffsetX, OffsetY) {
	//alert ("j res sel:"+clicked_block+", x:"+OffsetX+", y"+OffsetY);

	if (block_str!='') {
		var blocks = block_str.split (",");
			
	} else {
		var blocks = new Array();
	
	}
	
	var len = blocks.length;
	len++;
	blocks[len]=clicked_block;
	block_str = implode(blocks);

	//alert (block_str);



}
/////////////////////////////////

function unreserve_block(clicked_block, OffsetX, OffsetY) {
	//alert ("unres sel:"+clicked_block+", x:"+OffsetX+", y"+OffsetY);

	if (block_str!='') {
		var blocks = block_str.split (",");
			
	} else {
		var blocks = new Array();
	
	}
	var new_blocks = new Array();

	for (var i=0; i < blocks.length; i++) {
		if (blocks[i] != clicked_block) {
		//if (strcmp($blocks[$i], $clicked_block)!=0) {
			new_blocks[i] = blocks[i];
		} else {
			//clicked for 2nd time;
			//double_clicked = true;
		}
			
	}

	block_str = implode(new_blocks);

	//alert (block_str);

}

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

function refreshSelectedLayers()
{
	var grid = document.getElementById("pixelimg"); //Get image grid element
	var gridLeft = findPosX(grid); //Get image grid's new X position
	var gridTop = findPosY(grid); //Get image grid's new Y position
	var layer; //Used to hold layer elements below
	for(i = 0; i < selectedBlocks.length; i++) //Loop through selectedBlocks array
	{
		if(selectedBlocks[i] != '') //If spot isn't empty
		{
			//alert("Found a blockID!\ni=" + i + "\nallBlockIDs[" + i + "]=" + allBlockIDs[i]);
			layer = document.getElementById("block" + selectedBlocks[i]); //Get layer element given blockID stored in selectedBlocks array
			//Update layer relative to new pos of image grid
			layer.style.left = gridLeft + parseFloat(layer.getAttribute("tempLeft"));
			layer.style.top = gridTop + parseFloat(layer.getAttribute("tempTop"));
		} //End of if(selectedBlockIDs[i] != ''....
	} //End for loop
} //End testing()
//End -J- Edit: Custom functions for resize bug

////////////////////////////////////////
function show_block(clicked_block, OffsetX, OffsetY) {


	var myblock = document.getElementById("block"+clicked_block);
	if (myblock) {
		myblock.style.visibility='visible';
		//alert(clicked_block);
	} else {
		var myblock = document.getElementById('blocks');
		var pixelimg = document.getElementById('pixelimg');
//-J- Edit: Added tempTop and tempLeft values to span tag for resize bug
		myblock.innerHTML = myblock.innerHTML+"<span id='block"+clicked_block+"' tempTop=" + OffsetY + " tempLeft=" + OffsetX + " style='cursor: pointer;position: absolute; top: "+(OffsetY+pixelimg.offsetTop)+"; left: "+(OffsetX+pixelimg.offsetLeft)+";' onclick='change_block_state("+OffsetX+", "+OffsetY+");' onmousemove='show_pointer2(this, event)' ><img src='selected_block.png' width='<?php echo BLK_WIDTH; ?>' height='<?php echo BLK_HEIGHT; ?>'></span>";
		//alert('new block created');
		//alert(clicked_block);
//Begin -J- Edit: For resize bug
		selectedBlocks[selBlocksIndex] = clicked_block;
		selBlocksIndex = selBlocksIndex + 1;
//End -J- Edit

	}

	reserve_block (clicked_block, OffsetX, OffsetY)

}
/////////////////////////////////////////
function hide_block(clicked_block, OffsetX, OffsetY, status) {


	var myblock = document.getElementById("block"+clicked_block);
	//alert(status+'<-hiding');
	if ((!myblock)) {
		
		var pixelimg = document.getElementById('pixelimg');
		var myblocks = document.getElementById("blocks");
		myblocks.innerHTML = myblocks.innerHTML+"<span id='block"+clicked_block+"' style='cursor: pointer;position: absolute; top: "+(OffsetY+pixelimg.offsetTop)+"; left: "+(OffsetX+pixelimg.offsetLeft)+";' onclick='change_block_state("+OffsetX+", "+OffsetY+");' onmousemove='show_pointer2(this, event)' ><img src='get_block_image.php?BID=<?php echo $BID;?>&image_name=grid_block'></span>";
		// remove from db
		//alert(status);
		
	} else {
		myblock.style.visibility='hidden';
		//alert('2');
		//save_order();

	}

	unreserve_block(clicked_block, OffsetX, OffsetY);

	
	
	

}
/////////////////////////////////////////

//////////////////////////////////////////
// Initialize
var block_str = "<?php echo $order_row[blocks]; ?>";
var trip_count = 0;
/////////////////////////////////////////
function select_pixels(e) {

	// cannot select while AJAX is in action  
	if (document.getElementById('submit_button1').disabled){
		return false;
	}

	if (!browser_checked){
		browser_compatible = is_browser_compatible();
	}

	if (!browser_compatible){
		return false;
	}

	browser_checked=true;

	var pixelimg;
	pixelimg = document.getElementById('pixelimg');

	var pointer = document.getElementById('block_pointer');
	pointer.style.visibility='hidden';


	var pointer = document.getElementById('block_pointer');
	var OffsetX=pointer.map_x;
	var OffsetY=pointer.map_y;

	var BLK_WIDTH = <?php echo BLK_WIDTH; ?>;
	var BLK_HEIGHT = <?php echo BLK_HEIGHT; ?>;

	trip_count=1; // default

	if (document.getElementById('sel4').checked){
		// select 4 at a time
		trip_count=4; 
		change_block_state(OffsetX, OffsetY);
		change_block_state(OffsetX+BLK_WIDTH, OffsetY);
		change_block_state(OffsetX, OffsetY+BLK_HEIGHT);
		change_block_state(OffsetX+BLK_WIDTH, OffsetY+BLK_HEIGHT);

	} else {

		if  (document.getElementById('sel6').checked) {

			trip_count=6; 
			change_block_state(OffsetX, OffsetY);
			change_block_state(OffsetX+BLK_WIDTH, OffsetY);
			change_block_state(OffsetX, OffsetY+BLK_HEIGHT);
			change_block_state(OffsetX+BLK_WIDTH, OffsetY+BLK_HEIGHT);
			change_block_state(OffsetX+(BLK_WIDTH*2), OffsetY);
			change_block_state(OffsetX+(BLK_WIDTH*2), OffsetY+BLK_HEIGHT);

		} else {
			trip_count=1;
			change_block_state(OffsetX, OffsetY);
		}
		
	}

		

	return true;


}

//////////////////////

function IsNumeric(str)

{
   var ValidChars = "0123456789";
   var IsNumber=true;
   var Char;

 
   for (i = 0; i < str.length && IsNumber == true; i++) 
      { 
      Char = str.charAt(i); 
      if (ValidChars.indexOf(Char) == -1) 
         {
         IsNumber = false;
         }
      }
   return IsNumber;
   
   }

//////////////////////////////////

function change_block_state(OffsetX, OffsetY) {

	var grid_width=<?php echo $b_row[grid_width]?>;
	var grid_height=<?php echo $b_row[grid_height]?>;

	var BLK_WIDTH=<?php echo BLK_WIDTH?>;
	var BLK_HEIGHT=<?php echo BLK_HEIGHT?>;

	var map_x = OffsetX
	var map_y = OffsetY

	//var clicked_block = ((map_y*grid_width)+map_x)/10 ;
	var GRD_WIDTH = BLK_WIDTH * grid_width;
	var clicked_block = ((map_x) / BLK_WIDTH) + ((map_y/BLK_HEIGHT) * (GRD_WIDTH / BLK_WIDTH)) ;
	if (clicked_block==0) {
		clicked_block="0";// convert to string

	}

	//alert (clicked_block);
	if (block_str!='') {
		var blocks = block_str.split ( ",");
			
	} else {
		var blocks = new Array();
		//blocks = array ();
	}
	var new_blocks = new Array();
	//$new_blocks = array ();
 // remove selected block 
	var double_clicked;
	for (var i=0; i < blocks.length; i++) {
		if (blocks[i] != clicked_block) {
		//if (strcmp($blocks[$i], $clicked_block)!=0) {
			new_blocks[i] = blocks[i];
		} else {
			//clicked for 2nd time;
			double_clicked = true;
		}
			
	}

	if (!double_clicked) { // add newly selected block

		show_block(clicked_block, map_x, map_y);
		
	} else { // block was clicked for 2nd time:
		//hide_block(clicked_block, map_x, map_y, '');

		//update_order();
		//alert ('hiding');
	}

	block_str=implode(new_blocks);

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

		xmlhttp.open("GET", "update_order.php?user_id=<?php echo $_SESSION['MDS_ID'];?>&block_id="+clicked_block+"&BID=<?php 
		$sesname = ini_get('session.name');
		if ($sesname==''){
			$sesname = 'PHPSESSID';
		}
		echo $BID."&t=".time()."&$sesname=".session_id(); ?>",true);

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

				if (xmlhttp.responseText.indexOf('lock')>-1){
					alert(xmlhttp.responseText);

				}
				
			//	alert(xmlhttp.responseText);
				if ( (xmlhttp.responseText=='new') ){	
					hide_block(clicked_block, map_x, map_y, xmlhttp.responseText);
					
				} else {
					if (IsNumeric(xmlhttp.responseText)) {
					
						// add block
						new_blocks[i] = clicked_block;
						blocks = new_blocks;
						block_str = implode(blocks);
						document.form1.order_id.value=xmlhttp.responseText;

					} else {

						if (xmlhttp.responseText.indexOf('max_selected')>-1) {
							<?php
							$label['max_blocks_selected'] = str_replace('%MAX_BLOCKS%', G_MAX_BLOCKS, $label['max_blocks_selected']);	
							?>
							alert('<?php echo js_out_prep($label['max_blocks_selected']); ?> ');
						}

						if (xmlhttp.responseText.indexOf('max_orders')>-1) {
							alert ('<?php echo js_out_prep($label['advertiser_max_order'])?>');
						}

						hide_block(clicked_block, map_x, map_y, xmlhttp.responseText);

					}
					

				}
				update_order();
				trip_count--; // count down, enable button when 0
				//alert(trip_count);
				if (trip_count <= 0) {
					document.getElementById('submit_button1').disabled=false;
					document.getElementById('submit_button2').disabled=false;
					var pointer = document.getElementById('block_pointer');
					pointer.style.cursor='pointer';
					var pixelimg = document.getElementById('pixelimg');
					pixelimg.style.cursor='pointer';
					trip_count=0;
					//alert(block_str);
				}
				
			}
			
		}

		xmlhttp.send(null)

}

///////////////////////////////////

function implode(myArray) {

	//alert (myArray.length);
	var str='';
	var comma='';

	for (var i in myArray) {
		str = str+ comma + myArray[i];
		comma = ',';
	}

	return str;


}

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

	if (!browser_checked){
		browser_compatible = is_browser_compatible();
	}

	if (!browser_compatible){
		return false;
	}

	browser_checked=true;

	var pixelimg = document.getElementById('pixelimg');

	if (!pos) {
		var pos = getObjCoords(pixelimg);
	}
	
	
 
	if (e.offsetX) {
		var OffsetX = e.offsetX;
		var OffsetY = e.offsetY;
	} else {
		var OffsetX = e.pageX - pos.x;
		var OffsetY = e.pageY - pos.y;

	}

	// drop 1/10 from the OffsetX and OffsetY, eg 612 becomes 610

	OffsetX = Math.floor (OffsetX / <?php echo BLK_WIDTH; ?>)*<?php echo BLK_WIDTH; ?>;
	OffsetY = Math.floor (OffsetY / <?php echo BLK_HEIGHT; ?>)*<?php echo BLK_HEIGHT; ?>;

	var pointer = document.getElementById('block_pointer');
	
	pointer.style.visibility='visible';

	pointer.style.top=pos.y+OffsetY;
	pointer.style.left=pos.x+OffsetX;

	pointer.map_x=OffsetX;
	pointer.map_y=OffsetY;

	

	return true;

}

function show_pointer2 (block, e) {

	var pixelimg = document.getElementById('pixelimg');

	if (!pos) {
		var pos = getObjCoords(pixelimg);
	}

	var pointer = document.getElementById('block_pointer');

	if (block.offsetLeft){
		var OffsetX = block.offsetLeft;
		var OffsetY = block.offsetTop;
	} else {
		var OffsetX = e.pageX - pos.x;
		var OffsetY = e.pageY - pos.y;
	
	}
	OffsetX = Math.floor (OffsetX / <?php echo BLK_WIDTH; ?>)*<?php echo BLK_WIDTH; ?>;
	OffsetY = Math.floor (OffsetY / <?php echo BLK_HEIGHT; ?>)*<?php echo BLK_HEIGHT; ?>;

	pointer.style.visibility='hidden';
	

}


</script>

<span onmouseout="this.style.visibility='hidden' " id='block_pointer'  onclick="select_pixels(event);" style='cursor: pointer;position:absolute;left:0; top:0;background-color:#FFFFFF; visibility:hidden; '><img src='pointer.png' width="<?php echo BLK_WIDTH; ?>" height="<?php echo BLK_HEIGHT; ?>"></span>

<p>
<?php echo $label['advertiser_sel_trail']; 


?>
</p>


<p id="select_status" ><?php echo $cannot_sel; ?></p>

<?php

$sql = "SELECT * FROM banners order by `name`";
$res = mysql_query($sql);

if (mysql_num_rows($res)>1) {
?>
<div class="fancy_heading" width="85%"><?php echo $label['advertiser_sel_pixel_inv_head']; ?></div>
<p >
<?php

$label['advertiser_sel_select_intro'] = str_replace("%IMAGE_COUNT%",mysql_num_rows($res), $label['advertiser_sel_select_intro']);

echo $label['advertiser_sel_select_intro'];

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
<div class="fancy_heading" width="85%"><?php echo $label['advertiser_select_pixels_head']; ?></div>
<?php 
$label['advertiser_select_instructions2'] = str_replace('%PIXEL_C%', BLK_HEIGHT*BLK_WIDTH, $label['advertiser_select_instructions2']);
$label['advertiser_select_instructions2'] = str_replace('%BLK_HEIGHT%', BLK_HEIGHT, $label['advertiser_select_instructions2']);
$label['advertiser_select_instructions2'] = str_replace('%BLK_WIDTH%', BLK_WIDTH, $label['advertiser_select_instructions2']);
echo $label['advertiser_select_instructions2']; ?>


</div>

<form method="post" action="select.php" name='pixel_form'>
<input type="hidden" name="jEditOrder" value="true">
<p><b><?php echo $label['selection_mode'];?></b> <input type="radio" id='sel1' name='sel_mode' value='sel1' <?php  if (($_REQUEST[sel_mode]=='')||($_REQUEST[sel_mode]=='sel1')) { echo " checked ";}?> > <label for='sel1'><?php echo $label['select1']; ?></label> | <input type="radio" name='sel_mode' id='sel4' value='sel4'  <?php  if (($_REQUEST[sel_mode]=='sel4')) { echo " checked ";}?> > <label for="sel4"><?php echo $label['select4']; ?></label> | <input type="radio" name='sel_mode' id='sel6' value='sel6'  <?php  if (($_REQUEST[sel_mode]=='sel6')) { echo " checked ";}?> > <label for="sel6"><?php echo $label['select6']; ?></label>
</p>
<p>
<input type="button" name='submit_button1' id='submit_button1' value='<?php echo $label['advertiser_buy_button']; ?>' onclick='document.form1.submit()'>
</p>

<input type="hidden" value="1" name="select">
<input type="hidden" value="<?php echo $BID;?>" name="BID">


<input style="cursor: pointer;" id="pixelimg" <?php if (USE_AJAX=='YES') { ?> onmousemove="show_pointer(event)" onclick="if (select_pixels(event)) return false;" <?php } ?> type="image" name="map" value='Select Pixels.' width="<?php echo $b_row['grid_width']*BLK_WIDTH; ?>"  height="<?php echo $b_row['grid_height']*BLK_HEIGHT; ?>" src="show_selection.php?BID=<?php echo $BID;?>&gud=<?php echo time();?>" >

</form>



<input type="hidden" name="action" value="select">
</form>
<div style='background-color: #ffffff; border-color:#C0C0C0; border-style:solid;padding:10px'>
<hr>

<form method="post" action="order.php" name="form1">
<input type="hidden" name="package" value="">
<input type="hidden" name="selected_pixels" value=''>
<input type="hidden" name="order_id" value="<?php echo $_SESSION['MDS_order_id']; ?>">
<input type="hidden" value="<?php echo $BID;?>" name="BID">
<input type="submit" name='submit_button2' id='submit_button2' value='<?php echo $label['advertiser_buy_button']; ?>'>
<hr>
</form>

<script language='javascript'>

document.form1.selected_pixels.value=block_str;

</script>

</div>

<?php 

require "footer.php";

?>