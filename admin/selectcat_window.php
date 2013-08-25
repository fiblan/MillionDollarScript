<?php
/**
 * @version		$Id: selectcat_window.php 62 2010-09-12 01:17:36Z ryan $
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
require ("../config.php");
require ("admin_common.php");
//require_once ("../include/posts.inc.php");
require_once ("../include/category.inc.php");

?>
Please select the category and click OK<br>
The category will be the starting category for the field.
<form name="cat_selector" >
<select height="10" name="select" onchange="change_it(); ">
<option value="0">[Select Starting Category]</option>
<?php
		//showCatOptions ( 0, "Main");
		$form_id = $_REQUEST['form_id'];
		if ($form_id=='') {
			$form_id = 1;

		}
		category_option_list2(0, $selected, $form_id);
?>
	</select>
	
	<input type="button" value="OK" onclick="window.close()" >

	</form>

	<script>
function change_it() {
	var selectBox = document.forms[0].select;
		user_input = selectBox.options[selectBox.selectedIndex].value
		user_text = selectBox.options[selectBox.selectedIndex].text
		window.opener.document.form2.category_init_id.value = user_input;
		window.opener.document.form2.category_init_name.value = user_text;

	}


	</script>

