<?php
/**
 * @version		$Id: get_block_image.php 137 2011-04-18 19:48:11Z ryan $
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
// accepts $_REQUEST['BID'] and $_REQUEST['image_name']
define ('NO_HOUSE_KEEP', 'YES');

require ('../config.php');

$row = load_banner_constants($f2->bid($_REQUEST['BID']));

$image = $row[$_REQUEST['image_name']];

if ($image=='') {
	$image = get_default_image($_REQUEST['image_name']);
}


header("Cache-Control: max-age=60, must-revalidate"); // HTTP/1.1

header("Expires: ".gmdate('r',time()+60)); // Date in the past

header ("Content-type: image/x-png");
echo base64_decode($image);

?>