<?php
/**
 * @version		$Id: map_iframe.php 137 2011-04-18 19:48:11Z ryan $
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

ini_set('max_execution_time', 10000);
define ('NO_HOUSE_KEEP', 'YES');

require("../config.php");
require ('admin_common.php');

if ($f2->bid($_REQUEST['BID'])!='') {
	$BID = $f2->bid($_REQUEST['BID']);
} else {
	$BID = 1;

}

//print_r($_REQUEST);
//$sql = "select * from banners where banner_id=$BID";
//$result = mysql_query ($sql) or die (mysql_error().$sql);
//$b_row = mysql_fetch_array($result);
/*


*/
load_banner_constants($BID);

?>
<span onmouseout="hideBubble()" id="bubble" style="position:absolute;left:0; top:0; visibility:hidden; background-color:#FFFFFF; border-color:#33CCFF; border-style:solid; border-width:1; padding:3px; width:250px; font-family:Arial; font-size:11px;"></span>

<script language="JavaScript">

function is_right_available(box,e) {
	if ((box.clientWidth+e.clientX+h_padding)>=winWidth){
		return false; // not available
	}
	return true;
}

function is_top_available(box,e) {
	
	if ((e.clientY-box.clientHeight-v_padding) < 0){
		return false;
	}
	return true;

}

function is_bot_available(box,e) {
	if ((e.clientY+box.clientHeight+v_padding) > winHeight){
		return false;
	}
	return true;
}

function is_left_available(box,e) {
	if ((e.clientX-box.clientWidth-h_padding)<0){

		return false;
	}
	return true;

}

function boxFinishedMoving(box) {

	var y=box.offsetTop;
	var x=box.offsetLeft;

	if ((y<box.ypos)||(y>box.ypos)||(x<box.xpos)||(x>box.xpos)) {
		return false;
	} else {
		return true;
	}


}


function moveBox() {

	var box = document.getElementById('bubble');

	var y=box.offsetTop;
	var x=box.offsetLeft;

	if (!boxFinishedMoving(box))
	{
		if (y<box.ypos)
		{

			y++;
			box.style.top = y;
		}

		if (y>box.ypos)
		{
			y--;
			box.style.top = y;
		}

		if (x<box.xpos)
		{
			x++;
			box.style.left = x;
		}

		if (x>box.xpos)
		{
			x--;
			box.style.left = x;
		}
		window.setTimeout("moveBox()", <?php if (!is_numeric(ANIMATION_SPEED)) { echo '10'; } else {
	
		echo ANIMATION_SPEED; } ?>);
	} 

	
}
///////////////


function moveBox2() {

	var box = document.getElementById('bubble');

	var y=box.offsetTop;
	var x=box.offsetLeft;

	var diffx;
	var diffy;

	diffx = Math.abs(x-box.xpos);
	diffy = Math.abs(y-box.ypos);

	if (!boxFinishedMoving(box))
	{
		if (y<box.ypos)
		{

			y=y+diffy;
			box.style.top = y;
		}

		if (y>box.ypos)
		{
			y=y-diffy;
			box.style.top = y;
		}

		if (x<box.xpos)
		{
			x=x+diffx;
			box.style.left = x;
		}

		if (x>box.xpos)
		{
			x=x-diffx;
			box.style.left = x;
		}
		window.setTimeout("moveBox2()", <?php if (!is_numeric(ANIMATION_SPEED)) { echo '10'; } else {
	
		echo ANIMATION_SPEED; } ?>);
	} 

	
}
winWidth=0;
winHeight=0;
initFrameSize();
function initFrameSize() {
	
  var myWidth = 0, myHeight = 0;
  if( typeof( window.innerWidth ) == 'number' ) {
    //Non-IE
    myWidth = window.innerWidth;
    myHeight = window.innerHeight;
  } else if( document.documentElement &&
      ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
    //IE 6+ in 'standards compliant mode'
    myWidth = document.documentElement.clientWidth;
    myHeight = document.documentElement.clientHeight;
  } else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
    //IE 4 compatible
    myWidth = document.body.clientWidth;
    myHeight = document.body.clientHeight;
  }
  winWidth=myWidth;
  winHeight=myHeight;

}

pos = 'right';

h_padding=10;
v_padding=10;

function  showBubble(e, str, area)
{
	var relTarg;
	var bubble = document.getElementById('bubble');
	if (!e) var e = window.event;
	if (e.relatedTarget) relTarg = e.relatedTarget;
	else if (e.fromElement) relTarg = e.fromElement;

	b = bubble.style
	
		

	//str=str+"hello: "+bubble.clientWidth;
	document.getElementById('bubble').innerHTML=str;

	initFrameSize();

	var mytop =  is_top_available(bubble,e);
	var mybot = is_bot_available(bubble, e);
	var myright = is_right_available(bubble,e);
	var myleft = is_left_available(bubble,e);

	if (mytop)
	{
		// move to the top
		//b.top=e.clientY-bubble.clientHeight-v_padding;
		bubble.ypos=e.clientY-bubble.clientHeight-v_padding;
		//alert(bubble.xpos);
	}

	if (myright)
	{
		// move to the right
		//b.left=e.clientX+h_padding;//+bubble.clientWidth;
		bubble.xpos=e.clientX+h_padding;
	}

	if (myleft)
	{
		// move to the left
		//b.left=e.clientX-bubble.clientWidth-h_padding ;
		bubble.xpos=e.clientX-bubble.clientWidth-h_padding ;
	}

	

	if (mybot)
	{
		// move to the bottom
		//b.top=e.clientY+v_padding;
		bubble.ypos=e.clientY+v_padding;
	}

	b.visibility='visible';

	<?php

	

		
		?>

		//bubble.style.top=e.clientY;
		//bubble.style.left=e.clientX;
		moveBox2()
		//moveBox(bubble);
		window.setTimeout("moveBox2()", <?php if (!is_numeric(ANIMATION_SPEED)) { echo '10'; } else { echo ANIMATION_SPEED; } ?>);

		<?php


	?>

}

function hideBubble(e) {

	
	var bubble = document.getElementById('bubble');
	b = bubble.style;
	b.visibility='hidden';

	

}

var timeoutId=0;

function hideIt() {

	if (timeoutId==0) {

		timeoutId = window.setTimeout('hideBubble()', '500')

	}

}

function cancelIt() {

	if (timeoutId!=0) {

		window.clearTimeout(timeoutId);
		timeoutId=0;
	}

}

///////////////////////

/*

Block moving functions



*/

var bm_move_order_state = false;
var bm_move_block_state = false;

var BID = <?php echo $BID; ?>

function bm_state_change(button) {

	is_moving = false;

	if (button == 'MOVE_ORDER')
	{
		bm_move_block_state = false;
		document.button_move_b.src='move_b.gif';
		if (bm_move_order_state)
		{
			bm_move_order_state = false;
			document.button_move.src='move.gif';
		} else {
			bm_move_order_state = true;
			document.button_move.src='move_down.gif';
		}
	}

	if (button == 'MOVE_BLOCK')
	{
		bm_move_order_state = false;
		document.button_move.src='move.gif';

		if (bm_move_block_state)
		{
			bm_move_block_state = false;
			document.button_move_b.src='move_b.gif';
		} else {
			bm_move_block_state = true
				document.button_move_b.src='move_b_down.gif';
		}
	}

	//alert('state changed!')

	if ((bm_move_block_state==true) || (bm_move_order_state==true)){
		document.body.style.cursor = 'move';

	} else {
		document.body.style.cursor = 'default';

	}


}


/////////////////////

var is_moving=false;

var cb_from;

function do_block_click(banner_id) {

	
	//var move_done = bm_move_block_state | bm_move_order_state;

	//return move_done;
	document.body.style.cursor = 'default';
	is_moving = true;
	var cb = get_clicked_block()
	//var pointer = document.getElementById('block_pointer');
//	document.pointer_img.src = 'move.gif';
	if (bm_move_order_state) {
		document.pointer_img.src = 'get_pointer_image2.php?BID='+banner_id+'&block_id='+cb;
	} else {
		document.pointer_img.src = 'get_pointer_image.php?BID='+banner_id+'&block_id='+cb;

	}
	cb_from = cb

}

//////////////

function put_pixels(e) {

	is_moving = false;

	cb_to = get_clicked_block();

	document.move_form.cb_to.value = cb_to;
	document.move_form.cb_from.value = cb_from;

	if (bm_move_order_state) {
		document.move_form.move_type.value = 'O'; // Move order
	} else {
		document.move_form.move_type.value = 'B'; // Move block

	}

	document.move_form.submit();

	document.pointer_img.src = 'pointer.png';

	



}


///////////////////////


function show_pointer (e) {

	//if (!browser_checked){
	//	browser_compatible = is_browser_compatible();
	//}

	//if (!browser_compatible){
	//	return false;
	//}

	//browser_checked=true;

	

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

	// OffsetX = Math.floor (OffsetX / 10)*10;
	// OffsetY = Math.floor (OffsetY / 10)*10;

	OffsetX = Math.floor (OffsetX / <?php echo BLK_WIDTH; ?>)*<?php echo BLK_WIDTH; ?>;
	OffsetY = Math.floor (OffsetY / <?php echo BLK_HEIGHT; ?>)*<?php echo BLK_HEIGHT; ?>;

	var pointer = document.getElementById('block_pointer');

	if (!is_moving) {
		//return false;
		pointer.style.visibility='hidden';
		
	} else {
		pointer.style.visibility='visible';


	}
	
	
	if (pos.y+OffsetY)
	{
	
		pointer.style.top=pos.y+OffsetY;
		pointer.style.left=pos.x+OffsetX;

		pointer.map_x=OffsetX;
		pointer.map_y=OffsetY;

		window.status='co-ords: x:'+OffsetX+" y:"+OffsetY;

	}

	

	return true;

}

////////////


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




//////////////////////////

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
	//alert ('clicked block'+clicked_block)
	return clicked_block;
	



}

////////////////


	</script>
<!-- took out: onmouseout="this.style.visibility='hidden' " -->
	<span  id='block_pointer'  onclick="put_pixels(event);" style='cursor: pointer;position:absolute;left:0; top:0;background-color:#FFFFFF; visibility:hidden; '><img name='pointer_img' src='pointer.png'></span>


<form method='post' name="move_form" action='map_iframe.php'>
<input name='cb_from' type="hidden" value="">
<input name='cb_to' type="hidden" value="">
<input name='move_type' type="hidden" value="B">
<input name='BID' type="hidden" value="<?php echo $BID;?>">
</form>


<?php

//print_r($_REQUEST);

//echo "bannerid is --- ".$BID;

if ($_REQUEST['move_type']!='') { 

	if ($_REQUEST['move_type']=='B') {// move block

		move_block($_REQUEST['cb_from'], $_REQUEST['cb_to'], $BID);

	} else {

		move_order($_REQUEST['cb_from'], $_REQUEST['cb_to'], $BID);
	}

}

$sql = "SELECT * FROM blocks WHERE  banner_id='$BID'";
$result = mysql_query ($sql) or die (mysql_error());




?>

<IMG name='button_move' SRC="move.gif" WIDTH="24" HEIGHT="20" BORDER="0" ALT="Move Order" onclick='bm_state_change("MOVE_ORDER")'>
<IMG name='button_move_b' SRC="move_b.gif" WIDTH="24" HEIGHT="20" BORDER="0" ALT="Move Block" onclick='bm_state_change("MOVE_BLOCK")' >
<map name="main" id="main" onmousemove="cancelIt()">

	<?php

	while ($row=mysql_fetch_array($result)) {

		$sql = "select * from users where ID='".$row['user_id']."'";
		$res = mysql_query($sql) or die (mysql_error().$sql);
		$user_row = mysql_fetch_array($res);

		if (mysql_num_rows($res)==0) { 

			//$sql = "DELETE * FROM blocks where block_id=".$row['block_id'];
			//mysql_query($sql);

		}

		$sql = "select * from orders where order_id='".$row['order_id']."'";
		$res = mysql_query($sql) or die (mysql_error().$sql);
		$order_row = mysql_fetch_array($res);

		if (mysql_num_rows($res)==0) {

			//$sql = "DELETE * FROM blocks where block_id=".$row['block_id'];
			//mysql_query($sql);

		}

		//if ($row[date_stamp]!='') {

/*
			$time_expired = strtotime($order_row[date_stamp]);
			$time_when_cancel = $time_expired + ($b_row['days_expire '] * 24 * 60 * 60);
			$days =floor (($time_when_cancel - time()) / 60 / 60 / 24);
*/

			//$time_expired = strtotime($order_row[date_stamp]);
			//$time_when_cancel = $time_expired + ($b_row['days_expire '] * 24 * 60 * 60);

		if ($order_row['days_expire'] > 0) {

			//

			//} else {

			if ($order_row['published']!='Y') {
				$time_start = strtotime(gmdate('r'));
			} else {
				$time_start = strtotime($order_row['date_published']." GMT");
			}


			$elapsed_time = strtotime(gmdate('r')) - $time_start;
			$elapsed_days = floor ($elapsed_time / 60 / 60 / 24);
			

			$exp_time =  ($order_row['days_expire'] * 24 * 60 * 60);

			$exp_time_to_go = $exp_time - $elapsed_time;
			$exp_days_to_go =  floor ($exp_time_to_go / 60 / 60 / 24);

			$to_go = elapsedtime($exp_time_to_go);

			$elapsed = elapsedtime($elapsed_time);
			
			$days = "$elapsed passed<br> $to_go to go (".$order_row['days_expire'].")";

		//	}

			if ($order_row['published']!='Y') {
				$days = "not published";
			} elseif ($exp_time_to_go<=0) {
				$days .= 'Expired!';

			}

			//$days = $elapsed_time;

		} else {

				$days = "Never";

		}

		$alt_text = "<b>Customer:</b> ".$user_row['FirstName']." ".$user_row['LastName']." <br><b>Username:</b> ".$user_row['Username']."<br><b>Email:</b> ".$user_row['Email']."<br><b>Order</b> # : ".$row['order_id']." <br> <b>Block Status:</b> ".$row['status']."<br><b>Published:</b> ".$order_row['published']."<br><b>Approved:</b> ".$order_row['published']."<br><b>Expires:</b> ".$days."<br><b>Click Count:</b> ".$row['click_count']."<br><b>Co-ordinate:</b> x:".$row['x'].", y:".$row['y']."";

		//if (strlen($row['image_data'])>0) {
	?>

	<area 
	<?php if (true) { 

		// blow is another example for opening in a new window..

		//$new_window = "onclick=\"parent.window.open('click.php?block_id=".($row['block_id'])."', '', 'toolbar=yes,scrollbars=yes,location=yes,statusbar=yes,menubar=yes,resizable=1,width=800,height=600,left = 1,top = 1');return false;\"";
		
	
		$new_window = "onclick=\" if (bm_move_block_state|bm_move_order_state) {do_block_click(".$BID.")} else { parent.window.open('orders.php?user_id=".($row['user_id'])."&BID=".$BID."&order_id=".$row['order_id']."', '', '');}return false;\"";

		echo $new_window;

		?>
		 
		href="<?php echo ($row[url]);?>"
	<?php } else {?>
		href="orders.php?user_id=<?php echo ($row['user_id']);?>&BID=<?php echo $BID;?>&order_id=<?php echo $row['order_id'];?>";
	<?php } ?>
	
		onmousemove="showBubble(event, '<?php echo htmlspecialchars(str_replace("'", "\'", ($alt_text)));?>', this)"  
		onmouseout="hideIt()"
	
	shape="RECT" coords="<?php echo $row['x'];?>,<?php echo $row['y'];?>,<?php echo $row['x']+BLK_WIDTH;?>,<?php echo $row['y']+BLK_HEIGHT;?>"  
	<?php if (ENABLE_MOUSEOVER=='NO') { ?>
		title="<?php echo htmlspecialchars($alt_text);?>"
		alt="<?php echo htmlspecialchars($alt_text);?>"
	<?php } ?>
	target="_blank" >

	<?php
		//}

	}

	?>

	</map>

<img border=0 usemap="#main" id="pixelimg" onmousemove="show_pointer(event)" src='show_map.php?BID=<?php echo $BID;?>&time=<?php echo time(); ?>'>