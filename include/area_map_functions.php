<?php
/**
 * @version		$Id: area_map_functions.php 164 2012-12-14 21:22:24Z ryan $
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

// Contributed by Martin 
	// AREA render  function
	// Million Penny Home Page
	// http://www.onecentads.com/
	function render_map_area($fh,$data, $b_row) {

		$BID = $b_row['banner_id'];
		
		if (isset($data['x2'])) {
		  $x2 = $data['x2'];
		  $y2 = $data['y2'];
		} else {
		  $x2 = $data['x1'];
		  $y2 = $data['y1'];
		}
		fwrite($fh, "<area ");
		if (ENABLE_CLOAKING == 'YES') {
		  fwrite($fh, "onclick=\"return po(".$data['block_id'].");\" href=\"http://".$data['url']."\" ");
		} else {
		  fwrite($fh, "onclick=\"block_clicked=true;\" href=\"click.php?block_id=".$data['block_id']."&BID=$BID\" target=\"_blank\" " );
		}
		if ((ENABLE_MOUSEOVER=='YES') || (ENABLE_MOUSEOVER=='POPUP')) {
			//$data['alt_text']=$data['ad_id'];
			if ($data['ad_id']>0) {
			  $data['alt_text'] = $data['alt_text'].'<img src="'.BASE_HTTP_PATH.'periods.gif" border="0">';
		  }
		  fwrite($fh, "onmouseover=\"sB(event,'".htmlspecialchars(str_replace("'","\'",($data['alt_text'])))."',this, ".$data['ad_id'].")\" onmousemove=\"sB(event,'".htmlspecialchars(str_replace("'","\'",($data['alt_text'])))."',this, ".$data['ad_id'].")\" onmouseout=\"hI()\" ");
		}
		fwrite($fh, "coords=\"".$data['x1'].",".$data['y1'].",".($x2+$b_row['block_width']).",".($y2+$b_row['block_height'])."\"");
		if (ENABLE_MOUSEOVER=='NO') {
		  fwrite($fh, " title=\"".htmlspecialchars($data['alt_text'])."\" alt=\"".htmlspecialchars($data['alt_text'])."\"");
		}
		fwrite($fh, ">\n");
	
	}

/*

This function generates the <AREA> tags
The output is saved into a file.

*/

function process_map($BID, $map_file='') {

	if (!is_numeric($BID)) die();

	$sql = "UPDATE orders SET published='N' where `status`='expired' ";
	mysql_query($sql) or die(mysql_error());

	$sql = "SELECT * FROM `banners` WHERE `banner_id`='$BID' ";
	$result = mysql_query($sql) or die(mysql_error());
	$b_row = mysql_fetch_array($result);

	if (!$b_row['block_width']) { $b_row['block_width'] = 10;}
	if (!$b_row['block_height']) { $b_row['block_height'] = 10;}



	if (!$map_file) {
		$map_file = get_map_file_name($BID);
	}

  // open file
  $fh = fopen("$map_file","w");

  fwrite($fh, '<map name="main" id="main" onmousemove="cI()">');

  // render client-side click areas
  $sql = "SELECT DISTINCT order_id, user_id,url,image_data,block_id,alt_text,MIN(x) AS x1,MAX(x) AS x2,MIN(y) AS y1,MAX(y) AS y2, ad_id, COUNT(*) AS Total
                     FROM blocks
                    WHERE (published = 'Y')
					  AND (status = 'sold' ) 
                      AND (banner_id = '$BID')
                      AND (image_data > '')
                      AND (image_data = image_data)
                 GROUP BY order_id";
  $result = mysql_query ($sql) or die (mysql_error());
  
  while ($row = mysql_fetch_array($result)) {

	// Determine height and width of an optimized rect
	$x_span = $row['x2'] - $row['x1'] + $b_row['block_width'];
	$y_span = $row['y2'] - $row['y1'] + $b_row['block_height'];

	// Determine if reserved space is not equal to a single-ad user's optimized RECT
	if ( ( ($x_span * $y_span) / ($b_row['block_width']*$b_row['block_height']) ) != $row['Total'] ) {

	  // Render POLY or RECT (given reasonable possibilities)
	  $sql_i = "SELECT DISTINCT url, image_data, block_id, alt_text, MIN(x) AS x1, MAX(x) AS x2, y AS y1, y AS y2, ad_id, COUNT(*) AS Total
						   FROM blocks
						  WHERE (published = 'Y')
							AND (status = 'sold' ) 
							AND (banner_id = '$BID')
							AND (image_data > '')
							AND (image_data = image_data)
							AND (order_id = ".$row['order_id'].")
					   GROUP BY y";
	  $res_i = mysql_query($sql_i) or die(mysql_error());
	  while ($row_i = mysql_fetch_array($res_i)) {

		// If the min/max measure does not equal number of boxes, then we have to render this row's boxes individually
		//$box_count = ( ( ( $row_i['x2'] + 10 ) - $row_i['x1'] ) / 10 );
		$box_count = ( ( ( $row_i['x2'] + $b_row['block_width'] ) - $row_i['x1'] ) / $b_row['block_width'] );
		if ($box_count != $row_i['Total']) {
		  // must render individually as RECT
		  $sql_r = "SELECT ad_id, url, image_data, block_id, alt_text, x AS x1, x AS x2, y AS y1, y AS y2
					  FROM blocks
					 WHERE (published = 'Y')
					   AND (status = 'sold' ) 
					   AND (banner_id = '$BID')
					   AND (image_data > '')
					   AND (image_data = image_data)
					   AND (order_id = ".$row['order_id'].")
					   AND (y = ".$row_i['y1'].")";
		  $res_r = mysql_query($sql_r);
		  while ($row_r = mysql_fetch_array($res_r)) {
			// render single block RECT
			render_map_area($fh,$row_r, $b_row);
		  }
		} else {
		  // render multi-block RECT
		  render_map_area($fh,$row_i, $b_row);
		}
	  }
	} else {
	  // Render full ad RECT
	  render_map_area($fh,$row, $b_row);
	}

  }

  fwrite($fh, "</map>");
  fclose($fh);

}

////////////////////////////////////////
/*

This function outputs the HTML for the display_map.php file.
The structure of output:

<head>
<script>
<!-- Javascript in here ->
</script>
</head>
<body> <!--- render the grid's background image ->

<MAP> <!--- generated by process_map() ->

<AREA></AREA> <!--- generated by process_map() ->
<AREA></AREA> <!--- generated by process_map() ->
<AREA></AREA> <!--- generated by process_map() ->
...

</MAP> <!--- generated by process_map() ->

<img>

</body>

*/

function show_map($BID = 1) {

	if (!is_numeric($BID)) die();

	if (BANNER_DIR=='BANNER_DIR') {	
		$BANNER_DIR = "banners/";
	} else {
		$BANNER_DIR = BANNER_DIR;
	}

	$p = explode ("/",SERVER_PATH_TO_ADMIN);
	 array_pop($p);
	 array_pop($p);
	$BANNER_PATH = implode ("/",$p);
	$BANNER_PATH .= "/".$BANNER_DIR;

	$sql = "SELECT grid_width,grid_height, block_width, block_height, bgcolor, time_stamp FROM banners WHERE (banner_id = '$BID')";
	$result = mysql_query ($sql) or die (mysql_error().$sql);
	$b_row = mysql_fetch_array($result);

	if (!$b_row['block_width']) { $b_row['block_width'] = 10;}
	if (!$b_row['block_height']) { $b_row['block_height'] = 10;}

	/*

	Cache controls:

	We have to make sure that this html page is cashed by the browser.
	If the banner was not modified, then send out a HTTP/1.0 304 Not Modified and exit
	otherwise output the HTML to the browser.

	*/

	if (MDS_AGRESSIVE_CACHE=='YES') {

		header('Cache-Control: public, must-revalidate'); // cache all requests, browsers must respect this php script
		$if_modified_since = preg_replace('/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE']);
		$gmdate_mod = gmdate('D, d M Y H:i:s', $b_row['time_stamp']) . ' GMT';

		if ($if_modified_since == $gmdate_mod) {
			header("HTTP/1.0 304 Not Modified");
			exit;	
		}
		header("Last-Modified: $gmdate_mod");

	}

	?>
	<head>
	<script language="JavaScript">
	var h_padding=10;
	var v_padding=10;
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

		//window.status="x:"+x+" y:"+y+" box.ypos:"+box.ypos+" box.xpos:"+box.xpos;
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

				y+=Math.round(diffy*(0.01))+1; // calculate acceleration
				box.style.top = y;
			}

			if (y>box.ypos)			{
				y-=Math.round(diffy*(0.01))+1;
				box.style.top = y;
			}

			if (x<box.xpos)	{
				
				x+=Math.round(diffx*(0.01))+1; 
				box.style.left = x;
			}

			if (x>box.xpos){
				x-=Math.round(diffx*(0.01))+1; ;
				box.style.left = x;
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
	///////////////

	// This function is used for the instant pop-up box
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
				box.style.top = y;
			}

			if (y>box.ypos)	{
				y=y-diffy;
				box.style.top = y;
			}

			if (x<box.xpos)	{
				x=x+diffx;
				box.style.left = x;
			}

			if (x>box.xpos)	{
				x=x-diffx;
				box.style.left = x;
			}
			window.setTimeout("moveBox2()", <?php if (!is_numeric(ANIMATION_SPEED)) { echo '10'; } else {
		
			echo ANIMATION_SPEED; } ?>);
		} 

		
	}
	var winWidth=0;
	var winHeight=0;

	initFrameSize();
	function initFrameSize() {

		
		//
		winWidth=<?php echo $b_row['grid_width']*$b_row['block_width']; ?>;
		winHeight=<?php echo $b_row['grid_height']*$b_row['block_height']; ?>;

	}

	var pos = 'right';

	var strCache = new Array();

	var lastStr;
	var trip_count = 0;

	function isBrowserCompatible() {

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

		if (!xmlhttp) {
			return false
		}
		return true;

	}

////////////////////

	function fillAdContent(aid, bubble) {

		if (!isBrowserCompatible()) {
			return false;
		}

		// is the content cached?
		if (strCache[aid])
		{
			bubble.innerHTML = strCache[aid];
			return true;
		}

		//////////////////////////////////////////////////
		// AJAX Magic.
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

		xmlhttp.open("GET", "ga.php?AID="+aid+"<?php 
		
		echo "&t=".time(); ?>", true);

		//alert("before trup_count:"+trip_count);

		if (trip_count != 0){ // trip_count: global variable counts how many times it goes to the server
			// waiting state...
			
		}


		xmlhttp.onreadystatechange=function() {
			if (xmlhttp.readyState==4) {
				//

				
				//alert(xmlhttp.responseText);

				

				if (xmlhttp.responseText.length > 0) {
					bubble.innerHTML = xmlhttp.responseText;
					strCache[''+aid] = xmlhttp.responseText
				} else {
					
					bubble.innerHTML = bubble.innerHTML.replace('<img src=\"<?php echo BASE_HTTP_PATH;?>periods.gif\" border=\"0\">','');
					


				}
				

				trip_count--;

				//document.getElementById('submit_button1').disabled=false;
				//document.getElementById('submit_button2').disabled=false;

				//var pointer = document.getElementById('block_pointer');
				//pointer.style.cursor='pointer';
				//var pixelimg = document.getElementById('pixelimg');
				//pixelimg.style.cursor='pointer';
				
			}
			
		}

		xmlhttp.send(null)


	}

////////////////

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
	
		//window.status="x:"+x+" y:"+y+" box.ypos:"+box.ypos+" box.xpos:"+box.xpos;
	//	window.status="e.clientX"+e.clientX+" e.clientY:"+e.clientY;
		//str=str+"hello: "+bubble.clientWidth;
			//b.filter="progid:DXImageTransform.Microsoft.Blinds(Duration=0.5)";
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
			//alert(document.getElementById('bubble').innerHTML);
		}

		var mytop =  is_top_available(bubble,e);
		var mybot = is_bot_available(bubble, e);
		var myright = is_right_available(bubble,e);
		var myleft = is_left_available(bubble,e);

		//window.status="e.clientX"+e.clientX+" e.clientY:"+e.clientY+" mytop:"+mytop+" mybot:"+mybot+" myright:"+myright+" myleft:"+myleft+" | clientWidth:"+bubble.clientWidth+" clientHeight:"+bubble.clientHeight+" ww:"+winWidth+" wh:"+winHeight;

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
		

		
		
		//ChangeBgd(bubble);

		<?php
		if (ENABLE_MOUSEOVER=='POPUP') {
		?>

			//bubble.style.top=e.clientY;
			//bubble.style.left=e.clientX;
			moveBox2()
			//moveBox(bubble);
			window.setTimeout("moveBox2()", <?php if (!is_numeric(ANIMATION_SPEED)) { echo '10'; } else { echo ANIMATION_SPEED; } ?>);
			<?php
		} else {

		?>

			moveBox()
			//moveBox(bubble);
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

	var block_clicked=false; // did the user click a sold block? 
		</script>
		</head>
		<body <?php if (DISPLAY_PIXEL_BACKGROUND =='YES') { ?> bgcolor='<?php echo $b_row['bgcolor'];?>'	background="<?php echo BASE_HTTP_PATH.$BANNER_DIR;?>bg-main<?php echo $BID; ?>.gif" <?php } ?> >
	<?php
	include ('mouseover_box.htm'); // edit this file to change the style of the mouseover box!
	?>
	<script language="JavaScript">
	//document.getElementById('bubble').style.filer="progid:DXImageTransform.Microsoft.Iris(irisstyle='STAR',duration=4)";

	</script>
	<?php
	

	$map_file = get_map_file_name($BID);

	if (!file_exists($map_file)) {
		process_map($BID, $map_file);
	}

	include_once($map_file);

?>
<?php

	if (OUTPUT_JPEG == 'Y') {
		$ext = "jpg";
	} elseif (OUTPUT_JPEG=='N') {
		$ext = 'png';
	} elseif (OUTPUT_JPEG == 'GIF') {
		$ext = 'gif';
	}

	if (file_exists($BANNER_PATH."main".$BID.".$ext")) {
		if (REDIRECT_SWITCH=='YES') {
			$available_block_window = "parent.window.open('".REDIRECT_URL."', '', '');return false;";
		}
		?><img <?php if (REDIRECT_SWITCH=='YES') { ?>onclick="if (!block_clicked) {<?php echo $available_block_window; 
?> }block_clicked=false;" <?php } ?> id="theimage" src="<?php echo $BANNER_DIR; ?>main<?php echo $BID;?>.<?php echo $ext;?>?time=<?php echo ($b_row['time_stamp']); ?>" width="<?php echo $b_row['grid_width']*$b_row['block_width']; ?>" height="<?php echo $b_row['grid_height']*$b_row['block_height']; ?>" border="0" usemap="#main" /><?php

	} else {
		echo "<b>The file: ".$BANNER_PATH."main".$BID.".$ext"." doesn't exist.</b><br>";
		echo "<b>Please process your pixels from the Admin section (Look under 'Pixel Admin')</b>";
	}
	?>
	</body>
	<?php

}

///////////////////

function get_map_file_name($BID) {

	if (!is_numeric($BID)) {
		return false;

	}

	if (BANNER_DIR=='BANNER_DIR') {	
		$BANNER_DIR = "banners/";
	} else {
		$BANNER_DIR = BANNER_DIR;
	}

	//$p = explode ("/",SERVER_PATH_TO_ADMIN);
	$p = preg_split ('%[/\\\]%', SERVER_PATH_TO_ADMIN);

	 array_pop($p);
	 array_pop($p);
	$BANNER_PATH = implode ("/",$p);
	$BANNER_PATH .= "/".$BANNER_DIR;

	$map_file = $BANNER_PATH."map_$BID.inc";

	return $map_file;


}


?>