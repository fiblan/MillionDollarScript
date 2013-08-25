<?php 
require("../config.php");
require ('admin_common.php');

ini_set (session.use_trans_sid, false);

?>
<html>


<head>

<title>Language Translation Tool</title>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
<style>
body {
	
	font-family: 'Arial', sans-serif; 
	font-size:10pt;

}
</style>

<script language="JavaScript" type="text/javascript">

function confirmLink(theLink, theConfirmMsg)
   {
      
       if (theConfirmMsg == '' || typeof(window.opera) != 'undefined') {
           return true;
       }

       var is_confirmed = confirm(theConfirmMsg + '\n');
       if (is_confirmed) {
           theLink.href += '&is_js_confirmed=1';
       }

       return is_confirmed;
   } // end of the 'confirmLink()' function

</script>

</head>

<body>

<?php


$label = array();

$sql = "SELECT * FROM lang WHERE lang_code='".$_REQUEST['target_lang']."' ";
$result = mysql_query ($sql) or die (mysql_error());
$row = mysql_fetch_array($result);

$lang_filename = $row['lang_filename'];
$lang_name = $row['name'];
echo "lang filename: $lang_filename ";
include ("../lang/english_default.php");
$source_label = $label; // default english labels
include ("../lang/".$lang_filename);

$dest_label = $label; // dest labels
//print_r($dest_label);
// preload the source code, preg the hash key and use it as a key for the line 
$handle = fopen("../lang/english_default.php", "r");
while ($buffer= fgets($handle, 4096)) {
	if (preg_match ('/ *\$label\[.([A-z0-9]+).\].*/', $buffer, $m)) {
		$source_code[$m[1]] = $buffer;
	}
}


if ($_REQUEST['save']!='') {
	$out = "<?php\n";
	foreach ($source_label as $key=>$val) {
		$_REQUEST[$key] = str_replace ('\\"','"', $_REQUEST[$key] );
		$out .= "\$label['$key']='". $_REQUEST[$key]."'; \n";
	}
	$out .= "?>\n";
	$handler = fopen ("../lang/".$lang_filename, "w");
	fputs ($handler, $out);
	// load the new labels
	include ("../lang/".$lang_filename);
	$dest_label = $label; // dest labels
}



?>

<h3>
Language Translation tool.</h3>
<b>IMPORTANT:</b> Backup your language files before using this tool! This tool will overwrite any code in the target file with machine-generated code.<br>
<pre>
INSTRUCTIONS

1. The strings on the left are the original English strings. 
The strings on the right are for you to edit.
2. Clicking any of the Save buttons saves all the fields in the from.
You may click these at any time to Save the entire form.
3. Some fields have variables such as %SITE_NAME%. These variables get substituted.
Check the original string on the left to see what variables are available.
4. HTML is allowed.
5. If you want to use symbols such as &gt; &lt; or &amp;,
be sure to write them as HTML entities: &amp;gt; &amp;lt; and &amp;amp;
</pre>
<?php

if (!is_writeable("../lang/".$lang_filename)) {
	print ("<font color='red'><b>Warning:</b></font> The file ../lang/".$lang_filename." is not writable. You must give it write premissions for changes to take effect. You may set back to read-only permissions after saving changes.<br>");

}

?>
<form method="POST" name="form1" action="translation_tool.php">
<p>
<table ><tr><td>
<!--
<input type="radio" onclick="document.form1.submit()" <?php if ($_REQUEST['edit_mode']!='entities') { echo " checked ";  }  ?> name="edit_mode" value="no" > <font size="2">Edit as text (Helps you see what is edited)</font>

<input type="radio" onclick="document.form1.submit()" <?php if ($_REQUEST['edit_mode']=='entities') { echo " checked ";  }  ?> name="edit_mode" value="entities" ><font size="2">Edit with encoded HTML Entities (Recommended for final save)</font>
-->
</td></tr></table>
</p>
<input type="hidden" name="target_lang" value="<?php echo $_REQUEST['target_lang'] ?>" >

<table align="center" width="750" border="0" cellSpacing="2" cellPadding="3" bgColor="#d9d9d9" >
<tr bgColor="#eaeaea">
	<td width="50%"><b><font size="2">Source Language: English (Factory standard english_default.php)</font></b><br><br></td><td ><b><font size="2">Target Language: <?php echo $lang_name;?> (<?php echo $lang_filename; ?>)</font></b><br><br></td>
</tr>


<?php


foreach ($source_label as $key => $val) {
	$i++;
	

	if ($bg_color == "#ffffff") {
		$bg_color = "#FFFFff";
	} else {
		$bg_color = "#ffffff";

	}

	?>
	<tr bgcolor="#E8E8E8">
	<td colspan="2"><small><b><?php echo $key; ?></b></small><br>
	<span style="font-size: 10px; white-space: normal;">
	<?php $str = highlight_string("<?php ". ($source_code[$key])." ?>", true);// echo str_replace("\n", "<br>", $str);?>
	<span>
	</td>

	</tr>
	<tr bgcolor="<?php echo $bg_color;?>">
		<td width="50%" valign="top"><?php 
		
		if (strpos ($key, 'email_temp')) {

			echo "<br><font size='2'><pre>".htmlentities($val)."</pre></font><br>";

		} else {

			echo "<br><font size='2'>".htmlentities($val)."</font><br>";
		
		}
	
	?></td><td valign="top" ><textarea style="font-family: Arial; font-size: 10pt;" cols="60" rows="<?php $size = strlen ($val); $rows = round( $size / 40)+2; echo $rows; ?>"  name='<?php echo $key?>'><?php $text =  (stripslashes($dest_label[$key])); if ($_REQUEST[edit_mode] == 'entities') { echo htmlentities($text); } else {echo $text;} ?></textarea></td>
	</tr>
	<?php
	if ($i > 5) {

		echo "<tr bgcolor='#BDD5E6'><td></td><td><input type='submit' name='save' value='Save'></td></tr>";
		$i=0;
	}
	?>

	<tr>
	<td colspan="2"><hr></td>
	</tr>

	<?php
	


}

?>
<tr bgcolor='#BDD5E6'><td></td><td><input type='submit' name='save' value='Save'></td></tr>
</table>

</body>