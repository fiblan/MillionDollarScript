<?php
/**
 * @version		$Id: forgot.php 88 2010-10-12 16:43:19Z ryan $
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
require "../config.php";
include('login_functions.php');
/*
COPYRIGHT 2008 - see www.milliondollarscript.com for a list of authors

This file is part of the Million Dollar Script.

Million Dollar Script is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Million Dollar Script is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with the Million Dollar Script.  If not, see <http://www.gnu.org/licenses/>.

*/

$submit = $_REQUEST['submit'];
$email = $_REQUEST['email'];
?>
<?php echo $f2->get_doc(); ?>

<link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>

<center><img alt="" src="<?php echo SITE_LOGO_URL; ?>"/> <br/>
      <h3><?php echo $label["advertiser_forgot_title"]; ?></h3></center>
 <p align='center'>     
<center>
<form method="post">

<?php echo $label["advertiser_forgot_enter_email"] ?>: <input type="text" name="email" size="30"/>
<input class="form_submit_button" type="submit" name="submit" value="<?php echo $label["advertiser_forgot_submit"]; ?>">

</form>
</center>
</p>
<?php

function make_password (){

while (strlen($pass) < 5) { 
   $pass .= chr(rand (97,122)); 
  }
  return $pass;

  
}

if ($email != '') {


$sql = "select * from users where `Email`='$email'";
//echo $sql;
$result=mysql_query($sql);
$row = mysql_fetch_array($result);

if ($row[Email] != '') {

   if ($row[Validated]=='0') {
	$label["advertiser_forgot_error1"] = str_replace ("%SITE_CONTACT_EMAIL%", SITE_CONTACT_EMAIL , $label["advertiser_forgot_error1"]);
      echo "<center>".$label["advertiser_forgot_error1"]."</center>";

   } else {

     
      $pass = make_password();
      //echo " $pass";
      $md5pass = md5 ($pass);
      $sql = "update `users` SET `Password`='$md5pass' where `ID`='".$row[ID]."'";
      mysql_query($sql) or die(mysql_error().$sql);


	 //$result = get_email_template (3, $_SESSION['MDS_LANG']);
	 //$e_row = mysql_fetch_array($result);
	 //$EmailMessage = $e_row[EmailText];
	 $EmailMessage = $label["forget_pass_email_template"];
	 $from = SITE_CONTACT_EMAIL;// $e_row[EmailFromAddress];
	 $form_name = SITE_NAME;//$e_row[EmailFromName];
	 
	 //$subject = 'Your password on'.SITE_NAME; //$e_row[EmailSubject];

	 $subject = str_replace("%SITE_NAME%", SITE_NAME, $label["advertiser_forgot_subject"] );

	 
	 $subject = str_replace ("%MEMBERID%", $Username, $subject);

	 $EmailMessage = str_replace ("%FNAME%", $row[FirstName], $EmailMessage);
	 $EmailMessage = str_replace ("%LNAME%", $row[LastName], $EmailMessage);
	 $EmailMessage = str_replace ("%SITE_CONTACT_EMAIL%", SITE_CONTACT_EMAIL, $EmailMessage);
	 $EmailMessage = str_replace ("%SITE_NAME%", SITE_NAME, $EmailMessage);
	 $EmailMessage = str_replace ("%SITE_URL%", BASE_HTTP_PATH, $EmailMessage);
	 $EmailMessage = str_replace ("%MEMBERID%", $row['Username'], $EmailMessage);
	 $EmailMessage = str_replace ("%PASSWORD%", $pass, $EmailMessage);
	
                  	
		$to = $email;
		
		$message = $EmailMessage;

		if (USE_SMTP=='YES') {
			$mail_id=queue_mail(addslashes($to), addslashes($row[FirstName]." ".$row[LastName]), addslashes(SITE_CONTACT_EMAIL), addslashes(SITE_NAME), addslashes($subject), addslashes($message), '', 6);
			process_mail_queue(2, $mail_id);
		} else {
			send_email( $to, $row[FirstName]." ".$row[LastName], SITE_CONTACT_EMAIL, SITE_NAME, $subject, $message, '', 6);
		}

		$str = str_replace("%BASE_HTTP_PATH%", BASE_HTTP_PATH,$label["advertiser_forgot_success1"] );

		echo "<p align='center'>".$str."</p>";

	

   }


} else {

   echo "<center>".$label["advertiser_forgot_email_notfound"]."</center>";
}

}

?>

<center><h3><a href="../"><?php echo $label["advertiser_forgot_go_back"];?></a></h3></center>

</body>
</html>
