<?php
/**
 * @version		$Id: top_ads_js.php 164 2012-12-14 21:22:24Z ryan $
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

require ('config.php');
//require ('include/mouseover_js.inc.php');

?>
	document.onmousemove = function(e) {cI();}

	var h_padding=10;
	var v_padding=10;
	function is_right_available(box,e) {
		
		if ((box.clientWidth+e.clientX+h_padding)>=winWidth){
			return false;
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

		var diffx;
		var diffy;

		diffx = Math.abs(x-box.xpos);
		diffy = Math.abs(y-box.ypos);

		if (!boxFinishedMoving(box)) {
			if (y<box.ypos){
				y+=Math.round(diffy*(0.01))+1;
				box.style.top = y + "px";
			}

			if (y>box.ypos)			{
				y-=Math.round(diffy*(0.01))+1;
				box.style.top = y + "px";
			}

			if (x<box.xpos)	{
				x+=Math.round(diffx*(0.01))+1; 
				box.style.left = x + "px";
			}

			if (x>box.xpos){
				x-=Math.round(diffx*(0.01))+1; ;
				box.style.left = x + "px";
			}

			window.setTimeout("moveBox()", <?php
			  if (!is_numeric(ANIMATION_SPEED)) {
				echo '10';
			  } else {
				echo ANIMATION_SPEED;
			  }
			?>);
		} 
	}
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
			if (y<box.ypos)	{
				y=y+diffy;
				box.style.top = y + "px";
			}

			if (y>box.ypos)	{
				y=y-diffy;
				box.style.top = y + "px";
			}

			if (x<box.xpos)	{
				x=x+diffx;
				box.style.left = x + "px";
			}

			if (x>box.xpos)	{
				x=x-diffx;
				box.style.left = x + "px";
			}
			
			window.setTimeout("moveBox2()", <?php
			  if (!is_numeric(ANIMATION_SPEED)) {
				echo '10';
			  } else {
				echo ANIMATION_SPEED;
			  }
			?>);
		} 
	}
	var winWidth=0;
	var winHeight=0;

	initFrameSize();
	function initFrameSize() {
		winWidth=window.innerWidth;
		winHeight=window.innerHeight;
	}

	var pos = 'right';

	var strCache = new Array();

	var lastStr;
	var trip_count = 0;

	function isBrowserCompatible() {

		var xmlhttp=false;
		if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
		  xmlhttp = new XMLHttpRequest();
		}

		if (!xmlhttp) {
			return false
		}
		return true;

	}

	function fillAdContent(aid, bubble) {

		if (!isBrowserCompatible()) {
			return false;
		}

		if (strCache[aid])
		{
			bubble.innerHTML = strCache[aid];
			return true;
		}

		var xmlhttp=false;
		if (!xmlhttp && typeof XMLHttpRequest!='undefined') {
		  xmlhttp = new XMLHttpRequest();
		}

		xmlhttp.open("GET", "ga.php?AID="+aid+"<?php echo "&t=".time(); ?>", true);

		if (trip_count != 0){}

		xmlhttp.onreadystatechange=function() {
			if (xmlhttp.readyState==4) {

				if (xmlhttp.responseText.length > 0) {
					bubble.innerHTML = xmlhttp.responseText;
					strCache[''+aid] = xmlhttp.responseText
				} else {
					
					bubble.innerHTML = bubble.innerHTML.replace('<img src="<?php echo BASE_HTTP_PATH;?>periods.gif" border="0">','');
				}
				trip_count--;
			}
		}
		xmlhttp.send(null)
	}

	function  sB(e, str, area, aid)
	{
		window.clearTimeout(timeoutId);

		var relTarg;
		var bubble = document.getElementById('bubble');
		if (!e) var e = window.event;
		if (e.relatedTarget) relTarg = e.relatedTarget;
		else if (e.fromElement) relTarg = e.fromElement;

		b = bubble.style

		if ((lastStr!=str)) {

			lastStr=str;
			
			hideBubble(e);
			<?php 
			if (ENABLE_TRANSITIONS=='YES'){
			
			?>
				if (bubble.filters) {
				
					bubble.filters[0].transition=<?php echo TRANSITION_EFFECT; ?>;
					bubble.filters[0].duration=<?php echo TRANSITION_DURATION; ?>;
					bubble.filters[0].Apply();
				}
			<?php 
			}
			
			?>
			document.getElementById('content').innerHTML=str;
			trip_count++
			
			fillAdContent(aid, document.getElementById('content'));

			<?php 
			if (ENABLE_TRANSITIONS=='YES'){
			
			?>
				if (bubble.filters) {
					bubble.filters[0].Play();
				}
			<?php 
			}	
			?>
		}

		var mytop =  is_top_available(bubble,e);
		var mybot = is_bot_available(bubble, e);
		var myright = is_right_available(bubble,e);
		var myleft = is_left_available(bubble,e);

		if (mytop)
		{
			bubble.ypos=(e.clientY-bubble.clientHeight-v_padding);
		}

		if (myright)
		{
			bubble.xpos=(e.clientX+h_padding);
		}

		if (myleft)
		{
			bubble.xpos=(e.clientX-bubble.clientWidth-h_padding);
		}

		if (mybot)
		{
			bubble.ypos=(e.clientY+v_padding);
		}
	
		b.visibility='visible';

		<?php
		if (ENABLE_MOUSEOVER=='POPUP') {
		?>
			moveBox2()
			window.setTimeout("moveBox2()", <?php if (!is_numeric(ANIMATION_SPEED)) { echo '10'; } else { echo ANIMATION_SPEED; } ?>);
			<?php
		} else {

		?>
			moveBox()
			window.setTimeout("moveBox()", <?php if (!is_numeric(ANIMATION_SPEED)) { echo '10'; } else { echo ANIMATION_SPEED; } ?>);

		<?php

		}

		?>
	}

	function hBTimeout(e) {
		lastStr='';
		hideBubble(e);
	}

	function hideBubble(e) {
		window.clearTimeout(timeoutId);

		var bubble = document.getElementById('bubble');
		b = bubble.style;
		
		<?php 
		if (ENABLE_TRANSITIONS=='YES'){
		?>
			if (bubble.filters) {
				bubble.filters[0].transition=<?php echo TRANSITION_EFFECT; ?>;
				bubble.filters[0].duration=<?php echo TRANSITION_DURATION; ?>;
				bubble.filters[0].Apply();
			}
		<?php
		}
		?>
		
		b.visibility='hidden';
		
		<?php 
		if (ENABLE_TRANSITIONS=='YES'){
		?>
			if (bubble.filters) {
				bubble.filters[0].Play();
			}
		<?php 
		}
		?>
	}

	var timeoutId=0;

	function hI() {
		if (timeoutId==0) {
			timeoutId = window.setTimeout('hBTimeout()', '<?php echo HIDE_TIMEOUT; ?>')
		}
	}

	function cI() {
		if (timeoutId!=0) {
			timeoutId=0;
		}
	}

	function po(block_id) {

	  block_clicked=true;
	  window.open('click.php?block_id=' + block_id + '&BID=<?php echo $BID; ?>','','');
	  return false;
	}
	<?php if (REDIRECT_SWITCH=='YES') { ?>
	p = parent.window;
	<?php } ?>

	var block_clicked=false;
