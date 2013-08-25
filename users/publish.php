<?php
/**
 * @version		$Id: publish.php 147 2011-09-04 18:22:57Z ryan $
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

@set_time_limit ( 260); // 180 sec
session_start();
include ("../config.php");
include ("login_functions.php");
require_once ("../include/ads.inc.php");

process_login();

$gd_info = @gd_info();
if ($gd_info['GIF Read Support']) {$gif_support="GIF";} ;
if ($gd_info['JPG Support']) {$jpeg_support="JPG";};
if ($gd_info['PNG Support']) {$png_support="PNG";};

require ("header.php");

// Work out the banner id...

if ($f2->bid($_REQUEST['BID'])!='') {
	$BID = $f2->bid($_REQUEST['BID']);
	
} elseif ($_REQUEST['ad_id']!='') {
	$sql = "select banner_id from ads where ad_id='".$_REQUEST['ad_id']."'";
	$res = mysql_query($sql);
	$row = mysql_fetch_array($res);
	$BID = $row['banner_id'];
} else {
	// get the banner_id of one if the blocks the customer owns
	$sql = "SELECT DISTINCT(blocks.banner_id) as banner_id, name FROM blocks, banners where blocks.banner_id=banners.banner_id AND user_id='".$_SESSION['MDS_ID']."' and (status='sold' or status='expired') LIMIT 1";
	
	$sql = "select *, banners.banner_id AS BID FROM orders, banners where orders.banner_id=banners.banner_id  AND user_id=".$_SESSION['MDS_ID']." and (orders.status='completed' or status='expired') group by orders.banner_id order by orders.banner_id ";

	$res = mysql_query($sql);
	if ($row = mysql_fetch_array($res)) {
		$BID = $row['BID'];
	} else {
		$BID = 1; # this should not happen unless the above queries failed.
	}
}

//$sql = "select * from banners where banner_id='".$BID."'";
//$result = mysql_query ($sql) or die (mysql_error().$sql);
//$banner_row = mysql_fetch_array($result);

load_banner_constants($BID);

$sql = "select * from users where ID='".$_SESSION['MDS_ID']."'";
$result = mysql_query ($sql) or die (mysql_error().$sql);
$user_row = mysql_fetch_array($result);

##################################################
# Entry point for completion of orders which are made by super users or if the order was for free
if ($_REQUEST['action']=='complete') {

	// check if order is $0 & complete it

	if ($_REQUEST['order_id']=='temp') { // convert the temp order to an order.

		$sql = "select * from temp_orders where session_id='".addslashes(session_id())."' ";
		$order_result = mysql_query ($sql) or die(mysql_error());

		if (mysql_num_rows($order_result)==0) { // no order id found...
		//require ("header.php");
			?>
		<h1><?php echo $label['no_order_in_progress']; ?></h1>
		<p><?php $label['no_order_in_progress_go_here'] = str_replace ('%ORDER_PAGE%', $order_page ,  $label['no_order_in_progress_go_here']); echo $label['no_order_in_progress_go_here']; ?></p>
			<?php
			require ("footer.php");
			die();

		} elseif($order_row = mysql_fetch_array($order_result)) {

			$_REQUEST['order_id'] = reserve_pixels_for_temp_order($order_row);

		} else {

			?>
			<h1><?php echo $label['sorry_head']; ?></h1>
			<p><?php 
			if (USE_AJAX=='SIMPLE') {
				$order_page = 'order_pixels.php';
			} else {
				$order_page = 'select.php';
			}
			$label['sorry_head2'] = str_replace ('%ORDER_PAGE%', $order_page , $label['sorry_head2']);	
			echo $label['sorry_head2'];?></p>
			<?php
			require ("footer.php");
			die();

		}

	}

	$sql="select * from orders where order_id='".$_REQUEST['order_id']."' AND user_id='".$_SESSION['MDS_ID']."' ";
	$result = mysql_query($sql) or die(mysql_error());
	$row = mysql_fetch_array($result);
	if (($row['price']==0)||($user_row['Rank']==2)) {
		complete_order ($row['user_id'], $row['order_id']);
		// no transaction for this order
		echo "<h3>".$label['advertiser_publish_free_order']."</h3>";
	}
	// publish

	if (AUTO_PUBLISH=='Y') {
		process_image($BID);
		publish_image($BID);
		process_map($BID);
		
	}

}

###############################################################

# Banner Selection form
# Load this form only if more than 1 grid exists with pixels purchased.

$sql = "select * FROM orders, banners where orders.banner_id=banners.banner_id  AND user_id=".$_SESSION['MDS_ID']." and (orders.status='completed' or status='expired') group by orders.banner_id order by `name`";

$res = mysql_query($sql) or die(mysql_error().$sql);

if (mysql_num_rows($res)>1) {
	?>
	<p>
	<div class="fancy_heading" width="85%"><?php echo $label['advertiser_publish_pixinv_head']; ?></div>

	<?php
	$label['advertiser_publish_select_init2'] = str_replace("%GRID_COUNT%", mysql_num_rows($res),  $label['advertiser_publish_select_init2']);
	echo $label['advertiser_publish_select_init2'];
	?>
	</p>
	<p>
	<?php display_banner_selecton_form($BID, $_SESSION['MDS_order_id'], $res); ?>
	</p>
	<?php
		
} 


#####################################################
# A block was clicked. Fetch the ad_id and initialize $_REQUEST['ad_id']
# If no ad exists for this block, create it. 

if ($_REQUEST['block_id']!='') {

	$sql = "SELECT user_id, ad_id, order_id FROM blocks where banner_id='$BID' AND block_id='".$_REQUEST['block_id']."'";
	$result = mysql_query ($sql) or die (mysql_error());
	$blk_row = mysql_fetch_array($result);


	if (!$blk_row['ad_id']) { // no ad exists, create a new ad_id


		$_REQUEST[$ad_tag_to_field_id['URL']['field_id']]='http://';
		$_REQUEST[$ad_tag_to_field_id['ALT_TEXT']['field_id']] = 'ad text';
		$_REQUEST['order_id'] = $blk_row['order_id'];
		$_REQUEST['banner_id'] = $BID;
		$_REQUEST['user_id'] = $_SESSION['MDS_ID'];
		$ad_id = insert_ad_data();

		$sql = "UPDATE orders SET ad_id='$ad_id' WHERE order_id='".$blk_row['order_id']."' ";
		$result = mysql_query ($sql) or die (mysql_error());
		$sql = "UPDATE blocks SET ad_id='$ad_id' WHERE order_id='".$blk_row['order_id']."' ";
		$result = mysql_query ($sql) or die (mysql_error());

		$_REQUEST['ad_id'] = $ad_id;

	} else { // initialize $_REQUEST['ad_id']

	// make sure the ad exists..

		$sql = "select * from ads where ad_id='".$blk_row['ad_id']."' ";
		$result = mysql_query ($sql) or die (mysql_error());
		//echo $sql;
		if (mysql_num_rows($result)==0) {
			echo "No ad exists..";
			$_REQUEST[$ad_tag_to_field_id['URL']['field_id']]='http://';
			$_REQUEST[$ad_tag_to_field_id['ALT_TEXT']['field_id']] = 'ad text';
			$_REQUEST['order_id'] = $blk_row['order_id'];
			$_REQUEST['banner_id'] = $BID;
			$_REQUEST['user_id'] = $_SESSION['MDS_ID'];
			$ad_id = insert_ad_data();

			$sql = "UPDATE orders SET ad_id='$ad_id' WHERE order_id='".$blk_row['order_id']."' ";
			$result = mysql_query ($sql) or die (mysql_error());
			$sql = "UPDATE blocks SET ad_id='$ad_id' WHERE order_id='".$blk_row['order_id']."' ";
			$result = mysql_query ($sql) or die (mysql_error());

			$_REQUEST['ad_id'] = $ad_id;
		} else {
		
			$_REQUEST['ad_id'] = $blk_row['ad_id'];

		}
		// bug in previous versions resulted in saving the ad's user_id with a session_id
		// fix user_id here
		$sql = "UPDATE ads SET user_id='".$blk_row['user_id']."' WHERE order_id='".$blk_row['order_id']."' AND user_id <> '".$_SESSION['MDS_ID']."' limit 1 ";
		mysql_query ($sql) or die (mysql_error());

		


	}
	

}
//////////////

function disapprove_modified_order($order_id, $BID) {

	$sql = "UPDATE orders SET approved='N' WHERE order_id='".$order_id."' AND banner_id='".$BID."' ";
	//echo $sql;
	mysql_query($sql) or die(mysql_error());
	$sql = "UPDATE blocks SET approved='N' WHERE order_id='".$order_id."' AND banner_id='".$BID."' ";
	///echo $sql;
	mysql_query($sql) or die(mysql_error());

}

/////////////////////////
# Display ad editing forms if the ad was clicked, or 'Edit' button was pressed.

if ($_REQUEST['ad_id']) {
	//print_r($_REQUEST);

	$sql = "SELECT * from ads as t1, orders as t2 where t1.ad_id=t2.ad_id AND t1.user_id=".$_SESSION['MDS_ID']." and t1.banner_id='$BID' and t1.ad_id='".$_REQUEST['ad_id']."' AND t1.order_id=t2.order_id ";
	$result = mysql_query($sql) or die (mysql_error());
	//echo $sql."<br>";
	$row = mysql_fetch_array($result);
	$order_id = $row['order_id'];
	$blocks = explode(',',$row['blocks']);

	$size = get_pixel_image_size($row['order_id']);
	$pixels = $size['x'] * $size['y'];
	//print_r($size);
	//echo "order id:".$row['order_id']."<br>";
	//echo "$sql<br>";

	$sql = "SELECT * from blocks WHERE order_id='".$order_id."'";
	$blocks_result = mysql_query($sql) or die (mysql_error());


	if ($_REQUEST['change_pixels']) {

		// a new image was uploaded...

		// move the file

		$uploaddir = SERVER_PATH_TO_ADMIN."temp/";

		$parts = explode ('.', $_FILES['pixels']['name']);
		$ext = strtolower(array_pop($parts));

		// CHECK THE EXTENSION TO MAKE SURE IT IS ALLOWED
		$ALLOWED_EXT= 'jpg, jpeg, gif, png';
		$ext_list = preg_split ("/[\s,]+/i", ($ALLOWED_EXT));	
		if (!in_array($ext, $ext_list)) {

			$error .=  "<b>".$label['advertiser_file_type_not_supp']."</b><br>";
			$image_changed_flag = false;

		} else {

			$uploadfile = $uploaddir . "tmp_".md5(session_id()).".$ext";

			if (move_uploaded_file($_FILES['pixels']['tmp_name'], $uploadfile)) {
				//echo "File is valid, and was successfully uploaded.\n";
				$tmp_image_file = $uploadfile;

				// check image size

				$img_size = getimagesize($tmp_image_file);
				// check the size
				if ((MDS_RESIZE!='YES') && (($img_size[0] > $size['x']) || ($img_size[1] > $size['y'])) ) {
					$label['adv_pub_sizewrong'] = str_replace ('%SIZE_X%', $size['x'], $label['adv_pub_sizewrong']);
					$label['adv_pub_sizewrong'] = str_replace ('%SIZE_Y%', $size['y'], $label['adv_pub_sizewrong']);
					$error = $label['adv_pub_sizewrong']."<br>";

				} else { // size is ok. change the blocks.

					// create the new img...

					while ($block_row = mysql_fetch_array($blocks_result)) {

						//

						if ($high_x=='') {
							$high_x = $block_row['x'];
							$high_y = $block_row['y'];
							$low_x = $block_row['x'];
							$low_y = $block_row['y'];

						}

						if ($block_row['x'] > $high_x) {
							$high_x = $block_row['x'];
						}

						if ($block_row['y'] > $high_y) {
							$high_y = $block_row['y'];
						}

						if ($block_row['y'] < $low_y) {
							$low_y = $block_row['y'];
						}

						if ($block_row['x'] < $low_x) {
							$low_x = $block_row['x'];
						}

					}

					
					$_REQUEST['map_x'] = $high_x;
					$_REQUEST['map_y'] = $high_y;

					// create the requ

					if (function_exists("imagecreatetruecolor")) {
						$dest = imagecreatetruecolor(BLK_WIDTH, BLK_HEIGHT);
						$whole_image = imagecreatetruecolor ($size['x'], $size['y']);
					} else {
						$dest = imagecreate(BLK_WIDTH, BLK_HEIGHT);
						$whole_image = imagecreate ($size['x'], $new_size['y']);
					}

					$parts = explode ('.', $tmp_image_file);
					$ext = strtolower(array_pop($parts));
					//echo $ext."($upload_image_file)\n";
					switch (strtolower($ext)) {
						case 'jpeg':
						case 'jpg':
							$upload_image = imagecreatefromjpeg ($tmp_image_file);
							break;
						case 'gif':
							$upload_image = imagecreatefromgif ($tmp_image_file);
							break;
						case 'png':
							$upload_image = imagecreatefrompng ($tmp_image_file);
							break;
					}

					
					$imagebg = imageCreateFromstring (GRID_BLOCK);

					imageSetTile ($whole_image, $imagebg);
					imageFilledRectangle ($whole_image, 0, 0, $size['x'], $size['y'], IMG_COLOR_TILED);

					

					//echo " size x y ".$size['x'].' '.$size['y'].' img siz [0] [1]'.$img_size[0]." ". $img_size[1];

					if (MDS_RESIZE=='YES') { // make it smaller
						// resize
						$newsize_img = imagecreate($size['x'], $size['y']);
						imagecopyresampled ( $newsize_img, $upload_image, 0, 0, 0, 0, $size['x'], $size['y'], $img_size[0], $img_size[1] );
						imagecopy ($whole_image, $newsize_img, 0, 0, 0, 0, $size['x'], $size['y'] );
					} else {

						imagecopy ($whole_image, $upload_image, 0, 0, 0, 0, $img_size[0], $img_size[1] );
					}

					//imagepng($whole_image);

					

					for ($i=0; $i<($size['y']); $i+=BLK_HEIGHT) {
						
						for ($j=0; $j<($size['x']); $j+=BLK_WIDTH) {
							 
							$map_x = $j+$low_x;
							$map_y = $i+$low_y;

							$r_x = $map_x;
							$r_y = $map_y;

							$GRD_WIDTH = BLK_WIDTH * G_WIDTH;

							$cb = (($map_x) / BLK_WIDTH) + (($map_y/BLK_HEIGHT) * ($GRD_WIDTH / BLK_WIDTH)) ;

							// bool imagecopy ( resource dst_im, resource src_im, int dst_x, int dst_y, int src_x, int src_y, int src_w, int src_h )
							imagecopy ( $dest, $whole_image, 0, 0, $j, $i, BLK_WIDTH,  BLK_HEIGHT);
							
							ob_start();
							imagepng($dest);
							$data = ob_get_contents();
							ob_end_clean();
							$data = base64_encode($data);

							$sql = "UPDATE blocks SET image_data='$data' where block_id='".$cb."' AND banner_id='".$BID."' ";
							mysql_query($sql);
							

							//echo $sql."------>".mysql_affected_rows()."<br>";

						}

					}


				}

				unlink($tmp_image_file);

				if (AUTO_APPROVE!='Y') { // to be approved by the admin

					disapprove_modified_order($order_id, $BID);
				}

				if (AUTO_PUBLISH=='Y') {
					process_image($BID);
					publish_image($BID);
					process_map($BID);
					
				}


			} else {
				//echo "Possible file upload attack!\n";
				echo $label['pixel_upload_failed'];
			}

			

		}


	}

# Ad forms:
	?>
	<p>
<div class="fancy_heading" width="85%"><?php echo $label['adv_pub_editad_head']; ?></div>
<p><?php echo $label['adv_pub_editad_desc']; ?> </p>
<p><b><?php echo $label['adv_pub_yourpix'] ; ?></b></p>
<table border=0 bgcolor='#d9d9d9' cellspacing="1" cellpadding="5">
<tr bgcolor="#ffffff">
<td valign="top"><b><?php echo $label['adv_pub_piximg']; ?></b><br>
<center>
<?php
if ($_REQUEST['ad_id']!='') { 
		//echo "ad is".$_REQUEST['ad_id'];
		?><img src="get_order_image.php?BID=<?php echo $BID; ?>&aid=<?php echo $_REQUEST['ad_id']; ?>" border=1><?php
	} else {
		?><img src="get_order_image.php?BID=<?php echo $BID; ?>&block_id=<?php echo $_REQUEST['block_id']; ?>" border=1><?php
	} ?>
</center>
</td>
<td valign="top"><b><?php echo $label['adv_pub_pixinfo']; ?></b><br><?php
			
		$label['adv_pub_pixcount'] = str_replace('%SIZE_X%',$size['x'],$label['adv_pub_pixcount']);
		$label['adv_pub_pixcount'] = str_replace('%SIZE_Y%', $size['y'],$label['adv_pub_pixcount']);
		$label['adv_pub_pixcount'] = str_replace('%PIXEL_COUNT%', $pixels,$label['adv_pub_pixcount']);
		echo $label['adv_pub_pixcount'];
		?><br></td>
<td valign="top"><b><?php echo $label['adv_pub_pixchng']; ?></b><br><?php
			$label['adv_pub_pixtochng'] = str_replace('%SIZE_X%',$size['x'],$label['adv_pub_pixtochng']);
			$label['adv_pub_pixtochng'] = str_replace('%SIZE_Y%',$size['y'],$label['adv_pub_pixtochng']);
			echo $label['adv_pub_pixtochng'];
			?><form name="change" enctype="multipart/form-data" method="post">
<input type="file" name='pixels'><br>
<input type="hidden" name="ad_id" value="<?php echo $_REQUEST['ad_id']; ?>">
<input type="submit" name="change_pixels" value="<?php echo $label['adv_pub_pixupload']; ?>"></form><?php if ($error) { echo "<font color='red'>".$error."</font>"; $error='';} ?>
<font size='1'><?php echo $label['advertiser_publish_supp_formats']; ?> <?php echo "$gif_support $jpeg_support $png_support"; ?></font>
</td>
</tr>
</table>

<p><b><?php echo $label['adv_pub_edityourad']; ?></b></p>
<?php

	if ($_REQUEST['save'] != "" ) { // saving

		$error = validate_ad_data(1);
		if ($error != '') { // we have an error
			$mode = "user";
			//display_ad_intro();
			display_ad_form (1, $mode, '');
		} else {

			$ad_id = insert_ad_data();
			update_blocks_with_ad($ad_id, $_SESSION['MDS_ID']);
			
			global $prams;
			$prams = load_ad_values ($ad_id);
			//print_r($prams);

			?>
			<center><div class='ok_msg_label'><?php echo $label['adv_pub_adsaved']; ?></div></center>
			<p>&nbsp;</p>
			<?php

			$mode = "user";
		
			display_ad_form (1, $mode, $prams);

			// disapprove the pixels because the ad was modified..

			if (AUTO_APPROVE!='Y') { // to be approved by the admin
				disapprove_modified_order($prams['order_id'], $BID);
			}
			
			if (AUTO_PUBLISH=='Y') {
				process_image($BID);
				publish_image($BID);
				process_map($BID);
				//echo 'published.';
			}

			// send pixel change notification
			if (EMAIL_ADMIN_PUBLISH_NOTIFY=='YES') {
				send_published_pixels_notification($_SESSION['MDS_ID'], $BID);
			}

		}

	} else {
			
			$prams = load_ad_values ($_REQUEST['ad_id']);
			display_ad_form (1, 'user', $prams);

	}

	

} # end of ad forms
?>&nbsp;</p><?php
#########################################

# List Ads

ob_start();
$count = list_ads ($admin=false,$order, $offset, 'USER');
$contents = ob_get_contents();
ob_end_clean();

if ($count > 0) {
?>
	<p>

	<div class="fancy_heading" width="85%"><?php echo $label['adv_pub_yourads']; ?></div>
	</p><p>
	<?php
		echo $contents;
	?>
	</p>
	<?php

}
		
//}

?>
	<div class="fancy_heading" width="85%"><?php echo $label['advertiser_publish_head']; ?></div>
	<p>
	<?php echo $label['advertiser_publish_instructions2']; ?>
	
	
	<?php
	
	// infrom the user about the approval status of the iamges.

	$sql = "select * from orders where user_id='".$_SESSION['MDS_ID']."' AND status='completed' and  approved='N' and banner_id='$BID' ";
	$result4 = mysql_query ($sql) or die (mysql_error()); 

	if (mysql_num_rows($result4)>0) {	
		?>
		<p><div width='100%' style="border-color:#FF9797; border-style:solid;padding:5px;"><?php echo $label['advertiser_publish_pixwait']; ?></div></p>
		<?php
	} else {

		$sql = "select * from orders where user_id='".$_SESSION['MDS_ID']."' AND status='completed' and  approved='Y' and published='Y' and banner_id='$BID' ";
		//echo $sql;
		$result4 = mysql_query ($sql) or die (mysql_error()); 

		if (mysql_num_rows($result4)>0) {	
			?>
			<p><div width='100%' style="border-color:green;border-style:solid;padding:5px;"><?php echo $label['advertiser_publish_published']; ?></div></p>
			<?php
		} else {

			$sql = "select * from orders where user_id='".$_SESSION['MDS_ID']."' AND status='completed' and  approved='Y' and published='N' and banner_id='$BID' ";
			
			$result4 = mysql_query ($sql) or die (mysql_error()); 

			if (mysql_num_rows($result4)>0) {	
				?>
				<p><div width='100%' style="border-color:yellow;border-style:solid;padding:5px;"><?php echo $label['advertiser_publish_waiting']; ?></div></p>
				<?php
			}

		}

	}

	


	?>

	<?php

	// Generate the Area map form the current sold blocks.

	$sql = "SELECT * FROM blocks WHERE user_id='".$_SESSION['MDS_ID']."' AND status='sold' and banner_id='$BID' ";
	$result = mysql_query ($sql) or die (mysql_error());

	?>
	</div>
	<center>
	<map name="main" id="main">

	<?php

	while ($row=mysql_fetch_array($result)) {

		//if (strlen($row['image_data'])>0) {
	?>

	<area shape="RECT" coords="<?php echo $row['x'];?>,<?php echo $row['y'];?>,<?php echo $row['x']+BLK_WIDTH;?>,<?php echo $row['y']+BLK_HEIGHT;?>" href="publish.php?BID=<?php echo $BID;?>&block_id=<?php echo ($row['block_id']);?>" title="<?php echo ($row[alt_text]);?>" alt="<?php echo ($row[alt_text]);?>"  />

	<?php
	//	}

	}
	?>

	<img src="show_map.php?BID=<?php echo $BID;?>&time=<?php echo (time()); ?>" width="<?php echo (G_WIDTH*BLK_WIDTH); ?>" height="<?php echo (G_HEIGHT*BLK_HEIGHT); ?>" border="0" usemap="#main" />
	</center>
	<div style='background-color: #ffffff; border-color:#C0C0C0; border-style:solid;padding:10px'>

<?php


require ("footer.php");

?>