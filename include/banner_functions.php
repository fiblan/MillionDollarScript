<?php
/**
 * @version		$Id: banner_functions.php 64 2010-09-12 01:18:42Z ryan $
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

function load_banner_row($BID) {

	if (!is_numeric($BID)) {
		return false;
	}

	$sql = "SELECT * FROM `banners` WHERE `banner_id`='$BID' ";
	$result = mysql_query($sql) or die(mysql_error());
	$row = mysql_fetch_array($result);

	return $row;


}

function load_banner_constants($BID) {


	$row = load_banner_row($BID);

	// defaults

	if (!$row['block_width']) { $row['block_width'] = 10;}
	if (!$row['block_height']) { $row['block_height'] = 10;}

	if (!$row['grid_block']) $row['grid_block'] = get_default_image('grid_block');
	if (!$row['nfs_block']) $row['nfs_block'] = get_default_image('nfs_block');
	if (!$row['usr_grid_block']) $row['usr_grid_block'] = get_default_image('usr_grid_block');
	if (!$row['usr_nfs_block']) $row['usr_nfs_block'] = get_default_image('usr_nfs_block');
	if (!$row['usr_sel_block']) $row['usr_sel_block'] = get_default_image('usr_sel_block');
	if (!$row['usr_ord_block']) $row['usr_ord_block'] = get_default_image('usr_ord_block');
	if (!$row['usr_res_block']) $row['usr_res_block'] = get_default_image('usr_res_block');
	if (!$row['usr_sol_block']) $row['usr_sol_block'] = get_default_image('usr_sol_block');


	// define constants

	define ("G_NAME", $row['name']);
	define ("G_PRICE", $row['price_per_block']);
	define ("G_CURRENCY", $row['currency']);

	define ("DAYS_EXPIRE", $row['days_expire']);

	define ("BLK_WIDTH", $row['block_width']);
	define ("BLK_HEIGHT", $row['block_height']);
	define ("BANNER_ID", $row['banner_id']);
	define ("G_WIDTH", $row['grid_width']);
	define ("G_HEIGHT", $row['grid_height']);

	define ("GRID_BLOCK", base64_decode($row['grid_block']));
	define ("NFS_BLOCK", base64_decode($row['nfs_block']));

	define ("USR_GRID_BLOCK", base64_decode($row['usr_grid_block']));
	define ("USR_NFS_BLOCK", base64_decode($row['usr_nfs_block']));
	define ("USR_SEL_BLOCK", base64_decode($row['usr_sel_block']));
	define ("USR_ORD_BLOCK", base64_decode($row['usr_ord_block']));
	define ("USR_RES_BLOCK", base64_decode($row['usr_res_block']));
	define ("USR_SOL_BLOCK", base64_decode($row['usr_sol_block']));

	define ("G_BGCOLOR", $row['bgcolor']);
	define ("AUTO_APPROVE", $row['auto_approve']);
	define ("AUTO_PUBLISH", $row['auto_publish']);

	define ("G_MAX_ORDERS", $row['max_orders']);
	define ("G_MAX_BLOCKS", $row['max_blocks']);
	define ("G_MIN_BLOCKS", $row['min_blocks']);
	//define ("BANNER_ROW", serialize($row));

	return $row;

}
//////////////////////////

function get_default_image($image_name) {


	switch ($image_name) {

		case "grid_block": 
		return "iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAAHklEQVR4nGO8cuUKA27AwsDAoK2tjUuaCY/W4SwNAJbvAxP1WmxKAAAAAElFTkSuQmCC";

		//temp/not_for_sale_block.png
		case "nfs_block": 
		return "iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAAFUlEQVR4nGP8//8/A27AhEduBEsDAKXjAxF9kqZqAAAAAElFTkSuQmCC";
		//../bg-main.gif
		case "tile": 
		return "iVBORw0KGgoAAAANSUhEUgAAAHgAAAB4AQMAAAADqqSRAAAABlBMVEXW19b///9ZVCXjAAAAJklEQVR4nGNgQAP197///Y8gBpw/6r5R9426b9R9o+4bdd8wdB8AiRh20BqKw9IAAAAASUVORK5CYII=";

		//--------------------------------------------------------------------------------
		//../users/block.png

		case "usr_grid_block": 
		return 
		"iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAAHklEQVR4nGO8cuUKA27AwsDAoK2tjUuaCY/W4SwNAJbvAxP1WmxKAAAAAElFTkSuQmCC";

		//../users/not_for_sale_block.png
		case "usr_nfs_block": 
		return
		"iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAAFUlEQVR4nGP8//8/A27AhEduBEsDAKXjAxF9kqZqAAAAAElFTkSuQmCC";
		//../users/ordered_block.png
		case "usr_ord_block": 
		return
		"iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAAFElEQVR4nGP83+DAgBsw4ZEbwdIAJ/sB02xWjpQAAAAASUVORK5CYII=";
		//../users/reserved_block.png
		case "usr_res_block": 
		return
		"iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAAE0lEQVR4nGP8/58BD2DCJzlypQF0BwISHGyJPgAAAABJRU5ErkJggg==";

		//../users/selected_block.png
		case "usr_sel_block": 
		return
		"iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAAE0lEQVR4nGNk+M+ABzDhkxy50gBALQETmXEDiQAAAABJRU5ErkJggg==";

		//../users/sold_block.png
		case "usr_sol_block": 
		return
		"iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAAEklEQVR4nGP8z4APMOGVHbHSAEEsAROxCnMTAAAAAElFTkSuQmCC";



	}




}


/////////////////////////////////////////


?>