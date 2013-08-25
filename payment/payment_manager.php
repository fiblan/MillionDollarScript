<?php
//require_once ("config.php");


// include all the payment modules

$p = explode ("/",SERVER_PATH_TO_ADMIN);
array_pop($p);
array_pop($p);
$PAYMENT_PATH = implode ("/",$p);
$PAYMENT_PATH .= "/payment/";

$dir = dirname(__FILE__);
$dir = preg_split ('%[/\\\]%', $dir);
$blank = array_pop($dir);
$dir = implode('/', $dir);


$dh = opendir ($dir."/payment/");

while (($file = readdir($dh)) !== false) {
	
	if (($file != '.') && ($file != '..') && (strpos($file, ".php")>0) && ($file != "payment_manager.php")){
	   //echo $dir."/payment/"."$file<br>\n";
	   include($dir."/payment/".$file);
	   //include ("$file");
	   //include (SERVER_PATH_TO_ADMIN."../payment/$file");
	}
}
closedir($dh);
if ($_REQUEST['action']== 'save') {

	$obj = $_PAYMENT_OBJECTS[$_REQUEST['pay']];
	$obj->save_config();
	

}

if ($_REQUEST['action']== 'install') {

	$obj = $_PAYMENT_OBJECTS[$_REQUEST['pay']];
	$obj->install();
	// reload object
	$_PAYMENT_OBJECTS[$_REQUEST['pay']] = new $_REQUEST['pay'];


}

if ($_REQUEST['action']== 'uninstall') {

	$obj = $_PAYMENT_OBJECTS[$_REQUEST['pay']];
	$obj->uninstall();

}

if ($_REQUEST['action']== 'enable') {

	$obj = $_PAYMENT_OBJECTS[$_REQUEST['pay']];
	$obj->enable();


}


if ($_REQUEST['action']== 'disable') {

	$obj = $_PAYMENT_OBJECTS[$_REQUEST['pay']];
	$obj->disable();


}

function list_avalable_payments () {

	global $_PAYMENT_OBJECTS;

	?>

	<script language="JavaScript" type="text/javascript">

	function confirmLink(theLink, theConfirmMsg) {

       if (theConfirmMsg == '' || typeof(window.opera) != 'undefined') {
           return true;
       }

       var is_confirmed = confirm(theConfirmMsg + '\n');
       if (is_confirmed) {
           theLink.href += '&is_js_confirmed=1';
       }

       return is_confirmed;
	}
	</script>
	<table border="0">
	<tr><td  valign="top">
	<table border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9" width="400" >
			<tr bgColor="#eaeaea">
				<td><b><font size="2">Payment Module</b></font></td>
				<td><b><font size="2">Description</b></font></td>
				<td><b><font size="2">Status</b></font></td>
				<td><b><font size="2">&nbsp;</b></font></td>
				
			</tr>
	<?php

	foreach ($_PAYMENT_OBJECTS as $obj_key => $obj) {
		

		?>

		<tr <?php if ($obj_key==$_REQUEST['pay'])  { echo ' bgColor="#FFFF99" ';} else echo ' bgColor="#ffffff" '; ?>  onmouseover="old_bg=this.getAttribute('bgcolor');this.setAttribute('bgcolor', '#FBFDDB', 0);" onmouseout="this.setAttribute('bgcolor', old_bg, 0);">
			<td><font size="2"><a href="<?php echo $_SERVER['PHP_SELF'];?>?pay=<?php echo $obj_key;?>"><?php echo $obj->name; ?></a></font></td>
			<td><font size="2"><?php echo $obj->description; ?></font></td>
			<td><font size="2"><?php

				if (!$obj->is_installed()) {
					echo "<font color='red'>Not Installed</font>";

				} else {

					if ($obj->is_enabled()) {
						echo "<font color='green'>Enabled</font>";

					} else {
						echo "<font color='red'>Not Enabled</font>";

					}

				}
			
			?></font></td>
			<td nowrap><font size="2"><?php

			if ($obj_key==$_REQUEST['pay']) {
				if (!$obj->is_installed()) {
					echo "<input type='button' style='font-size: 10px;' value='Install' onclick=\"if (!confirmLink(this, 'Install, are you sure?')) return false;window.location='".$_SERVER['PHP_SELF']."?pay=".$obj_key."&action=install'\">";

				} else {

					if ($obj->is_enabled()) {
					//	echo "Enabled";
						echo "<input type='button' style='font-size: 10px;' value='Disable' onclick=\"if (!confirmLink(this, 'Disable, are you sure?')) return false;window.location='".$_SERVER['PHP_SELF']."?pay=".$obj_key."&action=disable'\">";

					} else {
						//echo "Not Enabled";
						echo "<input style='font-size: 10px;' type='button' value='Enable' onclick=\"if (!confirmLink(this, 'Enable, are you sure?')) return false; window.location='".$_SERVER['PHP_SELF']."?pay=".$obj_key."&action=enable'\">";

					}

					echo " &nbsp; <input style='font-size: 10px;' type='button' value='Uninstall' onclick=\" if (!confirmLink(this, 'Uninstall, are you sure?')) return false; window.location='".$_SERVER['PHP_SELF']."?pay=".$obj_key."&action=uninstall'\">";

				}


				if ($obj->is_installed()) {
					//	$obj->config_form();
				}


			} else {

				if ($obj->is_installed()) {
					echo "<input style='font-size: 10px;' type='button' value='Configure' onclick=\"window.location='".$_SERVER['PHP_SELF']."?pay=".$obj_key."'\">";
				} else {

					echo "<input type='button' style='font-size: 10px;' value='Install' onclick=\"if (!confirmLink(this, 'Install, are you sure?')) return false;window.location='".$_SERVER['PHP_SELF']."?pay=".$obj_key."&action=install'\">";



				}


			}
				
			
			?></font></td>
				
		</tr>
		
		<?php

	}

	?>
	</table>

	</td>
		<td valign="top">
		<?php
			
			if ($_REQUEST['pay']!='') {
			
				if ($_PAYMENT_OBJECTS[$_REQUEST['pay']]->is_installed()) {
					$_PAYMENT_OBJECTS[$_REQUEST['pay']]->config_form();
				}	

			}
				
				?>
		</td>
		</tr>
		</table>
	<?php

	


}


#######################################

function payment_option_list($order_id) {
	
	global $_PAYMENT_OBJECTS, $label;

	?>
	<table border="0" cellpadding="3" cellspacing="0" bgcolor="#C0C0C0" width="95%" align="center" >
	<tr bgcolor="#d9d9d9">
	<td><b><?php echo $label['payment_mab_btt'];?></b></td>
	<td><b><?php echo $label['payment_man_pt']; ?></b></font></td>
	<td><b><?php echo $label['payment_man_descr']; ?></b></td>
	
	</tr>
	<?php

	foreach ($_PAYMENT_OBJECTS as $obj_key => $obj) {

		
		$alt_color = "#E9E9E9";

		if ($obj->is_enabled()){


			?>
			<tr bgcolor="<?php echo $alt_color; ?>" onmouseover="old_bg=this.getAttribute('bgcolor');this.setAttribute('bgcolor', '#FBFDDB', 0);" onmouseout="this.setAttribute('bgcolor', old_bg, 0);">
			<td><p style="margin:5px;"><?php echo $obj->payment_button($order_id); ?></p></td>
			<td><font size="2"><?php echo $obj->name; ?></font></td>
			<td><?php echo $obj->description; ?></td>
			
			<tr>

			<?php

		}

	}

	?>
	</table>


	<?php


}



####################################
# Called from thanks.php page.
# The call is then delegated to the specific class process_payment_return() function

function process_payment_return($className) {

	global $_PAYMENT_OBJECTS;

	$obj = $_PAYMENT_OBJECTS[$className];

	if (isset($obj)) {

		$obj->process_payment_return();

	} else {
		echo "Warning: payment_manager.php detected that the return URL is incorrect for this payment method.";

	}



}

?>