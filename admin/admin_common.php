<?php
/**
 * @version		$Id: admin_common.php 137 2011-04-18 19:48:11Z ryan $
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

// setup filters
require_once '../include/functions2.php';
$f2 = new functions2(); 

require_once '../library/HTMLPurifier.auto.php';
$purifier = new HTMLPurifier(); 

if (($_REQUEST['pass'] != '') && (MAIN_PHP=='1')) {
	if ($_REQUEST['pass'] == ADMIN_PASSWORD) {
		$_SESSION[ADMIN] = '1';
	}
}
if (($_SESSION[ADMIN]=='') ) {
	if (MAIN_PHP=='1') {
	?>
Please input admin password:<br>
<form method='post'>
<input type="password" name='pass'>
<input type="submit" value="OK">
</form>
	<?php

	}

	die();

} 

?>