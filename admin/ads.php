<?php
/**
 * @version		$Id: ads.php 137 2011-04-18 19:48:11Z ryan $
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
require("../config.php");
require('admin_common.php');
require_once ("../include/ads.inc.php");

function disapprove_modified_order($order_id, $BID) {
/*
	$sql = "UPDATE orders SET approved='N' WHERE order_id='".$order_id."' AND banner_id='".$BID."' ";
	//echo $sql;
	mysql_query($sql) or die(mysql_error());
	$sql = "UPDATE blocks SET approved='N' WHERE order_id='".$order_id."' AND banner_id='".$BID."' ";
	///echo $sql;
	mysql_query($sql) or die(mysql_error());

	// send pixel change notification

	if (EMAIL_ADMIN_PUBLISH_NOTIFY=='YES') {
		send_published_pixels_notification($_SESSION['MDS_ID'], $BID);
	}
*/

}
?>
<?php echo $f2->get_doc(); ?>

<link rel='StyleSheet' type="text/css" href="../users/style.css" >
<style type="text/css">


</style>

<title><?php echo SITE_NAME; ?></title>

</head>

<body>

<?php

if (is_numeric($_REQUEST['ad_id'])) {

		$gd_info = @gd_info();
if ($gd_info['GIF Read Support']) {$gif_support="GIF";} ;
if ($gd_info['JPG Support']) {$jpeg_support="JPG";};
if ($gd_info['PNG Support']) {$png_support="PNG";};

	$prams = load_ad_values($_REQUEST['ad_id']);

	// pre-check for failure
	if ($prams['user_id'] == "") {
		die("Either the user id for this ad doesn't exist or this ad doesn't exist.");
	}
	//echo "load const ";
	load_banner_constants($prams['banner_id']);

	$sql = "SELECT * from ads as t1, orders as t2 where t1.ad_id=t2.ad_id AND t1.user_id=".$prams['user_id']." and t1.banner_id='".$prams['banner_id']."' and t1.ad_id='".$prams['ad_id']."' AND t1.order_id=t2.order_id ";
	//echo $sql."<br>";
	$result = mysql_query($sql) or die (mysql_error());
	
	$row = mysql_fetch_array($result);
	$order_id = $row['order_id'];
	$blocks = explode(',',$row['blocks']);

	$size = get_pixel_image_size($row['order_id']);
	$pixels = $size['x'] * $size['y'];
	//print_r($size);
	//echo "order id:".$row['order_id']."<br>";
	//echo "$sql<br>";

	$sql = "SELECT * from blocks WHERE order_id='".$order_id."'";
	$blocks_result = mysql_query($sql) or die (mysql_error().$sql);


	if ($_REQUEST['change_pixels']) {

		// a new image was uploaded...

		// move the file

		$uploaddir = SERVER_PATH_TO_ADMIN."temp/";

		$parts = split ('\.', $_FILES['pixels']['name']);
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
				if (($img_size[0] > $size['x']) || ($img_size[1] > $size['y'])) {
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

					$parts = split ('\.', $tmp_image_file);
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
					imagecopy ($whole_image, $upload_image, 0, 0, 0, 0, $img_size[0], $img_size[1] );
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

							$sql = "UPDATE blocks SET image_data='$data' where block_id='".$cb."' AND banner_id='".$prams['banner_id']."' ";
							mysql_query($sql);
							

							//echo $sql."------>".mysql_affected_rows()."<br>";

						}

					}


				}

				unlink($tmp_image_file);

				if (AUTO_APPROVE!='Y') { // to be approved by the admin

					disapprove_modified_order($order_id, $prams['banner_id']);
				}

				if (AUTO_PUBLISH=='Y') {
					process_image($prams['banner_id']);
					publish_image($prams['banner_id']);
					process_map($prams['banner_id']);
					
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
			$mode = "edit";
			//display_ad_intro();
			//echo $error;
			display_ad_form (1, $mode, '');
		} else {
			insert_ad_data(true); // admin mode
			$prams = load_ad_values ($_REQUEST['ad_id']);
			update_blocks_with_ad($_REQUEST['ad_id'], $prams['user_id']);
			display_ad_form (1, "edit", $prams);
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
			echo 'Ad Saved. <A href="ads.php?BID='.$prams['banner_id'].'">&lt;&lt; Go to the Ad List</a>';
			echo "<hr>";
		}
	} else {

		$prams = load_ad_values ($_REQUEST['ad_id']);
		display_ad_form (1, 'edit', $prams);

	}
	$prams = load_ad_values ($_REQUEST['ad_id']);
	$sql = "select * FROM users where ID='".$prams['user_id']."' ";
	$result = mysql_query($sql);
	$u_row = mysql_fetch_array($result);
	
	
	$b_row = load_banner_row($prams['banner_id']);
	?>

	<h3>Additional Info</h3>
	<b>Customer:</b><?php echo $u_row['LastName'].', '.$u_row['FirstName'];  ?><BR>
	<b>Order #:</b><?php echo $prams['order_id'];?><br>
	<b>Grid:</b><a href='ordersmap.php?banner_id=<?php echo $prams['banner_id']; ?>'><?php echo $prams['banner_id']." - ".$b_row['name'];?></a>


	<?php
	echo '<hr>';
	
} else {

	// select banner id

	if ($f2->bid($_REQUEST['BID'])!='') {
		$BID = $f2->bid($_REQUEST['BID']);
	} else {
		$BID = 1;

	}

	$sql = "Select * from banners ";
$res = mysql_query($sql);
?>

<form name="bidselect" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">

	Select grid: <select name="BID" onchange="document.bidselect.submit()">
		<option> </option>
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
	<hr>
	<?php
}

$count = list_ads ($admin=true,$order, $offset,  $list_mode='ALL');
?>
</body>