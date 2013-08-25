<?php
/**
 * @version		$Id: currency.php 150 2012-09-10 22:00:19Z ryan $
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

?>
<?php echo $f2->get_doc(); ?>

<title>Edit Currencies</title>

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

</head>

<body style=" font-family: 'Arial', sans-serif; font-size:10pt;  ">


<hr>

<?php
function is_reserved_currency ($code) {
	
	switch ($code) {
		case "AUD":
			return true;
			break;
		case "CAD":
			return true;
			break;
		case "EUR":
			return true;
			break;
		case "GBP":
			return true;
			break;
		case "JPY":
			return true;
			break;
		case "USD":
			return true;
			break;

	}

	return false;

}
function validate_input() {

	if (trim($_REQUEST['code'])=='') {
		$error .= "- Currency code is blank<br>";

	}

	if (trim($_REQUEST['name'])=='') {
		$error .= "- Currency name is blank<br>";

	}

	if (trim($_REQUEST['rate'])=='') {
		$error .= "- Currency rate is blank<br>";

	}

	if (trim($_REQUEST['decimal_point'])=='') {
		$error .= "- Decimal point is blank<br>";

	}

	if (trim($_REQUEST['thousands_sep'])=='') {
		$error .= "- Thousands seperator is blank<br>";

	}

	return $error;


}

if ($_REQUEST['action'] == 'delete') {
	
		if (!is_reserved_currency ($_REQUEST['code'])) {

			$sql = "DELETE FROM currencies WHERE code='".$_REQUEST['code']."' ";
			mysql_query($sql) or die(mysql_error().$sql);
		} else {

			echo "<p><b>Cannot delete currency: reserved by the system</b></p>";


		}

}

if ($_REQUEST['action'] == 'set_default') {
	$sql = "UPDATE currencies SET is_default = 'N' WHERE code <> '".$_REQUEST['code']."' ";
	mysql_query($sql) or die(mysql_error().$sql);

	$sql = "UPDATE currencies SET is_default = 'Y' WHERE code = '".$_REQUEST['code']."' ";
	mysql_query($sql) or die(mysql_error().$sql);

}

if ($_REQUEST['submit']!='') {

	$error = validate_input();

	if ($error != '') {

		echo "Error: cannot save due to the following errors:<br>";
		echo $error;

	} else {

		$sql = "REPLACE INTO currencies(code, name, rate, sign, decimal_places, decimal_point, thousands_sep, is_default) VALUES ('".$_REQUEST['code']."', '".$_REQUEST['name']."', '".$_REQUEST['rate']."',  '".$_REQUEST['sign']."', '".$_REQUEST['decimal_places']."', '".$_REQUEST['decimal_point']."', '".$_REQUEST['thousands_sep']."', '".$_REQUEST['is_default']."') ";

		//echo $sql;

		mysql_query ($sql) or die (mysql_error());

		$_REQUEST['new'] ='';
		$_REQUEST['action'] = '';
		//print_r ($_REQUEST);


	}

}

?>
<b>All currency rates are relative to the USD. (USD rate is always 1)</b><br>
All prices will be displayed in the default currency.<br>
<table border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9" >
			<tr bgColor="#eaeaea">
				<td><b><font size="2">Currency</b></font></td>
				<td><b><font size="2">Code</b></font></td>
				<td><b><font size="2">Rate</b></font></td>
				<td><b><font size="2">Sign</b></font></td>
				<td><b><font size="2">Decimal<br>Places</b></font></td>
				<td><b><font size="2">Decimal<br>Point</b></font></td>
				<td><b><font size="2">Thousands<br>Seperator</b></font></td>
				<td><b><font size="2">Is Default</b></font></td>
				<td><b><font size="2">Action</b></font></td>
			</tr>
<?php
			// http://php.net/manual/en/function.htmlspecialchars.php#103939
			function umlaute($text){
			    $returnvalue="";
			    for($i = 0; $i < strlen($text); $i++){
			        $teil = hexdec(rawurlencode(substr($text, $i, 1)));
			        if($teil<32||$teil>1114111){
			            $returnvalue .= substr($text, $i, 1);
			        }else{
			            $returnvalue .= "&#" . $teil . ";";
			        }
			    }
			    return $returnvalue;
			}

			$result = mysql_query("select * FROM currencies order by name") or die (mysql_error());
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

				$row['sign'] = umlaute($row['sign']);
				?>

				<tr bgcolor="#ffffff">

				<td><font size="2"><?php echo $row['name'];?></font></td>
				<td><font size="2"><?php echo $row['code'];?></font></td>
				<td><font size="2"><?php echo $row['rate'];?></font></td>
				<td><font size="2"><?php echo $row['sign'];?></font></td>
				<td><font size="2"><?php echo $row['decimal_places'];?></font></td>
				<td><font size="2"><?php echo $row['decimal_point'];?></font></td>
				<td><font size="2"><?php echo $row['thousands_sep'];?></font></td>
				<td><font size="2"><?php echo $row['is_default'];?></font></td>
				<td><font size="2"><?php if ($row['is_default']!='Y') { ?><a href='<?php echo $SERVER[PHP_SELF];?>?action=set_default&code=<?php echo $row['code'];?>'>Set to Default</a> /<?php } ?> <a href='<?php echo $SERVER[PHP_SELF];?>?action=edit&code=<?php echo $row['code'];?>'>Edit</a> / <a href='<?php echo $SERVER[PHP_SELF];?>?action=delete&code=<?php echo $row['code'];?>'>Delete</a></font></td>
				
				</tr>


				<?php

			}
?>
</table>
<input type="button" value="New Currency..." onclick="window.location='currency.php?new=1'">
<?php

if ($_REQUEST['new']=='1') {
	echo "<h4>New Currency:</h4>";
	//echo "<p>Note: Make sure that you create a file for your new language in the /lang directory.</p>";
}
if ($_REQUEST['action']=='edit') {
	echo "<h4>Edit Currency:</h4>";

	$sql = "SELECT * FROM currencies WHERE `code`='".$_REQUEST['code']."' ";
	$result = mysql_query ($sql) or die (mysql_error());
	$row = mysql_fetch_array($result);
	$_REQUEST['name'] = $row['name'];
	$_REQUEST['rate'] = $row['rate'];
	$_REQUEST['sign'] = $row['sign'];
	$_REQUEST['decimal_point'] = $row['decimal_point'];
	$_REQUEST['thousands_sep'] = $row['thousands_sep'];
	$_REQUEST['decimal_places'] = $row['decimal_places'];
	$_REQUEST['is_default'] = $row['is_default'];
}

if (($_REQUEST['new']!='') || ($_REQUEST['action']=='edit')) {

	?>
<form action='currency.php' method="post">
<input type="hidden" value="<?php echo $_REQUEST['new']?>" name="new" >
<input type="hidden" value="<?php echo $_REQUEST['action']?>" name="action" >
<input type="hidden" value="<?php echo $_REQUEST['lang_code']?>" name="lang_code" >
<input type="hidden" value="<?php echo $_REQUEST['is_default']?>" name="is_default" >
<table border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9">
<tr bgcolor="#ffffff" ><td><font size="2">Currency Name:</font></td><td><input size="30" type="text" name="name" value="<?php echo $_REQUEST['name']; ?>"/> eg. Korean Won</td></tr>
<tr bgcolor="#ffffff" ><td><font size="2">Currency Code:</font></td><td><input <?php echo $disabled; ?> size="2" type="text" name="code" value="<?php echo $_REQUEST['code']; ?>"/> eg. KRW</td></tr>
<tr bgcolor="#ffffff" ><td><font size="2">Currency Rate:</font></td><td><input <?php echo $disabled; ?> size="5" type="text" name="rate" value="<?php echo $_REQUEST['rate']; ?>"/>($1 USD = x in this currency)</td></tr>
<tr bgcolor="#ffffff" ><td><font size="2">Currency Sign:</font></td><td><input <?php echo $disabled; ?> size="1" type="text" name="sign" value="<?php echo $_REQUEST['sign']; ?>"/>(eg. &#165;)</td></tr>
<tr bgcolor="#ffffff" ><td><font size="2">Currency Decimals:</font></td><td><input <?php echo $disabled; ?> size="1" type="text" name="decimal_places" value="<?php echo $_REQUEST['decimal_places']; ?>"/>(eg. 2)</td></tr>
<tr bgcolor="#ffffff" ><td><font size="2">Decimal Point:</font></td><td><input  size="1" type="text" name="decimal_point" value="<?php echo $_REQUEST['decimal_point']; ?>"/>(eg. .)</td></tr>
<tr bgcolor="#ffffff" ><td><font size="2">Thousands Seperator:</font></td><td><input  size="1" type="text" name="thousands_sep" value="<?php echo $_REQUEST['thousands_sep']; ?>"/>(eg. ,)</td></tr>
</table>
<input type="submit" name="submit" value="Submit">
</form>

	<?php

}

?>

</body>

</html>