<?php
/**
 * @version		$Id: index.php 164 2012-12-14 21:22:24Z ryan $
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

// set the root path
define("MDSROOT", dirname(__FILE__));

// check if a config.php exists, if not then rename the default one and redirect to install
if(!file_exists(MDSROOT . "/config.php")) {
	if(file_exists(MDSROOT . "/config-default.php")) {
		if(rename(MDSROOT . "/config-default.php", MDSROOT . "/config.php")){
			$host  = $_SERVER['HTTP_HOST'];
			$uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
			$loc   = "http://$host$uri/admin/install.php";
			header("Location: $loc");
		}
	}
	echo "The file config.php was not found and I was unable to automatically rename it. You may have to manually rename config-default.php to config.php and then visit $loc to install the script.";
	exit;
}

// include the config file
include_once (MDSROOT . "/config.php");

// include the header
include_once (MDSROOT . "/html/header.php");

// Note: Below is the iframe which displays the image map. Use Process Pixels in the admin to update the image map.
?>			

<iframe style="margin:0 auto;" width="1001" height="1001" frameborder="0" marginwidth="0" marginheight="0" vspace="0" hspace="0" scrolling="no" allowtransparency="true" src="display_map.php?BID=1"></iframe>

<?php
// include footer
include_once (MDSROOT . "/html/footer.php");
?>