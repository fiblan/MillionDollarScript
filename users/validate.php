<?php
session_start();
include ("../config.php");
include ("login_functions.php");


?>
 <head>

   <title><?php echo $label["advertiser_loginform_title"]; ?></title>

   <link rel="stylesheet" type="text/css" href="style.css" />

   </head>
   <body>
   <center><img alt="" src="<?php echo SITE_LOGO_URL; ?>"/> <br/>
<p>&nbsp;</p>



<?php
$show_form=true;
if ($_REQUEST['email']!='') {

	// validate

	

	$sql = "SELECT * FROM users where Email='".$_REQUEST['email']."' ";
	$result = mysql_query($sql);


	if ($row = mysql_fetch_array($result)) {

		$code = substr(md5($row[Email].$row[Password]),0, 8);

		if ($_REQUEST['code']==$code) {

			$sql = "UPDATE users SET Validated=1 WHERE Email='".$_REQUEST['email']."'";
			mysql_query($sql);

			echo "<p>&nbsp;</p><center><h3><font color='green'>".$label[advertiser_valid_complete]."</font></h3></center>";

			echo "<p>&nbsp;</p><center><h3><a href='index.php'>".$label['advertiser_valid_login']."</a></h3></center>";

			//process_login();
			$show_form=false;

		} else {
			echo "<p>&nbsp;</p><center><h3>".$label[advertiser_valid_error]."</h3></center>";
			$show_form=true;
		}

	} else {

		$show_form=true;

		echo "<h3>Error: Email address invalid.</h3>";

	}


}

if ($show_form) {
?>
	<center>
<form method="POST" action="<?php echo $_SERVER[PHP_SELF];?>">
<p>
<table><tr><td>
<?php echo $label['advertiser_valid_entemail']; ?></td><td> <input type="text" size="35" name='email' value="<?php echo $_REQUEST[email];?>"></td></tr>
<tr><td>
<?php echo $label['advertiser_valid_entcode']; ?></td><td><input type="text" name='code'></td></tr>
<tr><td colspan="2">

<input type="submit" value="Submit">
</td></tr>
</table>
</p>
</form>
</center>
<?php

}







//require ("footer.php");
?>