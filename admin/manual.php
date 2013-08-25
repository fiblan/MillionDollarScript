<?php
/**
 * @version		$Id: manual.php 62 2010-09-12 01:17:36Z ryan $
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

require("../config.php");
require ('admin_common.php');


if ($_REQUEST['pass']!='') {

	if ($_REQUEST['pass']==ADMIN_PASSWORD) {
		$_SESSION[ADMIN] = '1';

	}

}
if ($_SESSION[ADMIN]=='') {

	?>
	<head>
<META HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE">

	</head>
Please input admin password:<br>
<form method='post'>
<input type="password" name='pass'>
<input type="submit" value="OK">
</form>
	<?php

	die();

}

?>

<h3>Instructions for manual update</h3>
<p>
1. <a href='preview.php' target='_blank'>Click this link</a> to view the entire grid on the screen.
</p>
<p>
2. Take a screenshot of the page. (You can press the 'Print Scr' button if you have a windows PC to copy the a screenshot into the clipboard. You may also use a screen capture tool such as Snag It which will help you capture the whole scrolling window in one go.)
</p>
<p>
3. Paste the screenshort into your favorite image editing program. Crop the image to your desired size. Save as 'main.gif'
</p>
<p>
4. Upload main.gif into the script's main directory.
</p>