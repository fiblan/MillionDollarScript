<?php
/**
 * @version		$Id: show_email.php 62 2010-09-12 01:17:36Z ryan $
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



$sql ="SELECT * FROM mail_queue where mail_id=".$_REQUEST[mail_id];
$result = mysql_query($sql);
$row = mysql_fetch_array($result);
?>

<table border="1" id="table1" width="600">
	<tr>
		<td width="118">Template ID:</td>
		<td width="322"><?php echo $row[template_id]; ?></td>
	</tr>
	<tr>
		<td width="118">To Name:</td>
		<td width="322"><?php echo $row[to_name]; ?></td>
	</tr>
	<tr>
		<td width="118">To Address:</td>
		<td width="322"><?php echo $row[to_address]; ?></td>
	</tr>
	<tr>
		<td width="118">From Name:</td>
		<td width="322"><?php echo $row[from_name]; ?></td>
	</tr>
	<tr>
		<td width="118">From Address:</td>
		<td width="322"><?php echo $row[from_address]; ?></td>
	</tr>
	<tr>
		<td width="118">Subject:</td>
		<td width="322"><?php echo $row[subject]; ?></td>
	</tr>
	<tr>
		<td width="118">Message (text)</td>
		<td width="322"></td>
	</tr>
	<tr>
		<td colspan="2"><pre><?php echo $row[message]; ?></pre></td>
	</tr>
	<tr>
		<td width="118">Message (HTML)</td>
		<td width="322"></td>
	</tr>
	<tr>
		<td colspan="2"><?php echo $row[html_message]; ?></td>
	</tr>
	<tr>
		<td width="118">Attachments</td>
		<td width="322"><?php echo $row[att1_name]; ?><br>
		<?php echo $row[att2_name]; ?><br>
		<?php echo $row[att3_name]; ?><br>
		</td>
	</tr>
</table>

