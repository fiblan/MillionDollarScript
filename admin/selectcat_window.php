<?php
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

