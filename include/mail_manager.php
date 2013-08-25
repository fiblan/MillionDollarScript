<?php
/**
 * @version		$Id: mail_manager.php 64 2010-09-12 01:18:42Z ryan $
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

function add_mail_attachments(&$email_message, &$mail_row) {

		 if ($mail_row[att1_name] != '') {
		
		  $attachment1=array(
			 "FileName"=>$mail_row[att1_name],
			 "Content-Type"=>"automatic/name",
			 "Disposition"=>"attachment"
		  );
		  $email_message->AddFilePart($attachment1);
		  
	   }
	   if ($mail_row[att2_name] != '') {
		
		  $attachment2=array(
			 "FileName"=>$mail_row[att2_name],
			 "Content-Type"=>"automatic/name",
			 "Disposition"=>"attachment"
		  );
		  $email_message->AddFilePart($attachment2);
		  
	   }

	   if ($mail_row[att3_name] != '') {
		 
		  $attachment3=array(
			 "FileName"=>$mail_row[att3_name],
			 "Content-Type"=>"automatic/name",
			 "Disposition"=>"attachment"
		  );
		  $email_message->AddFilePart($attachment3);
		  
	   }

	   return $email_message;

	}

##########################################################################

function move_uploaded_attachment ($mail_id, $att_file, $from_name) {

	// strip out non-alphanumeric characters from from_name
	$from_name = preg_replace ('/[^\w]+/', "", $from_name);
#mail('adam@jamit.com.au','attach', "Af:$att_file fname:".$_FILES[$att_file]['name']."\n");
	$att_name = $_FILES[$att_file]['name'];
	$att_tmp = $_FILES[$att_file]['tmp_name'];

	$temp= explode('.', $att_name);
	$ext = array_pop($temp);

	if (!file_exists(FILE_PATH."temp/")) {
		mkdir(FILE_PATH."temp/");
		chmod(FILE_PATH."temp/", 0777);  

	}

	$new_name = FILE_PATH."temp/$from_name".$mail_id."$att_file.".$ext;

	move_uploaded_file ($att_tmp, $new_name);
	chmod($new_name, 0666);
	

	return $new_name;


}

function q_mail_error($s) {

	mail(SITE_CONTACT_EMAIL, SITE_NAME.'email q error', $s."\n");


}

#################################################
# queue a 'carbon copy' of an email 
function queue_mail_cc($mail_id, $to_name, $to_address) {

	$sql = "select * from mail_queue where mail_id=".$mail_id;
	$result = mysql_query($sql) or die(mysql_error());
	$row=mysql_fetch_array($result);


	$attachments=$row['attachments'];
	
	$now = (gmdate("Y-m-d H:i:s"));

	$sql = "INSERT INTO mail_queue (mail_id, mail_date, to_address, to_name, from_address, from_name, subject, message, html_message, attachments, status, error_msg, retry_count, template_id, date_stamp, att1_name, att2_name, att3_name) VALUES('', '$now', '$to_address', '$to_name', '".addslashes($row['from_address'])."', '".addslashes($row['from_name'])."', '".addslashes($row['subject'])."', '".addslashes($row['message'])."', '".addslashes($row['html_message'])."', '".addslashes($row['attachments'])."', 'queued', '', 0, '".addslashes($row['template_id'])."', '$now', '".addslashes($row['att1_name'])."', '".addslashes($row['att2_name'])."', '".addslashes($row['att3_name'])."')";

	mysql_query ($sql) or q_mail_error (mysql_error().$sql);

	$mail_id = mysql_insert_id();



	return $mail_id;

}

#################################################

function queue_mail($to_address, $to_name, $from_address, $from_name, $subject, $message, $html_message, $template_id, $att=false) {

	$to_address=trim($to_address);
	$to_name=trim($to_name);
	$from_address=trim($from_address);
	$from_name=trim($from_name);
	$subject=trim($subject);
	$message=trim($message);
	$html_message=trim($html_message);

	
	$attachments='N';
	
	$now = (gmdate("Y-m-d H:i:s"));


	$sql = "INSERT INTO mail_queue (mail_id, mail_date, to_address, to_name, from_address, from_name, subject, message, html_message, attachments, status, error_msg, retry_count, template_id, date_stamp) VALUES('', '$now', '$to_address', '$to_name', '$from_address', '$from_name', '$subject', '$message', '$html_message', '$attachments', 'queued', '', 0, '$template_id', '$now')"; // 2006 copyr1ght jam1t softwar3 

	mysql_query ($sql) or q_mail_error (mysql_error().$sql);

	$mail_id = mysql_insert_id();

	//echo "mail $mail_id queued.";

	if ($att) {

		if ($_FILES['att1']['name']!='') {
			$filename = move_uploaded_attachment ($mail_id, 'att1', $from_name);
			$sql = "UPDATE mail_queue SET attachments='Y', att1_name='$filename' WHERE mail_id=$mail_id ";
			mysql_query ($sql) or q_mail_error (mysql_error().$sql);

		}
		
		if ($_FILES['att2']['name']!='') {
			$filename = move_uploaded_attachment ($mail_id, 'att2', $from_name);
			$sql = "UPDATE mail_queue SET attachments='Y', att2_name='$filename' WHERE mail_id=$mail_id ";
			mysql_query ($sql) or q_mail_error (mysql_error().$sql);
		}
		
		if ($_FILES['att3']['name']!='') {
			$filename = move_uploaded_attachment ($mail_id, 'att3', $from_name);
			$sql = "UPDATE mail_queue SET attachments='Y', att3_name='$filename' WHERE mail_id=$mail_id ";
			mysql_query ($sql) or q_mail_error (mysql_error().$sql);
		}

	}
	return $mail_id;



}


############################

function do_pop_before_smtp() {

	$now = (gmdate("Y-m-d H:i:s"));
	$unix_time = time();

	// get the time of pop
	$sql = "SELECT * FROM `config` where `key` = 'LAST_MAIL_POP' ";
	$result = @mysql_query($sql) or $DB_ERROR = mysql_error();
	$t_row = @mysql_fetch_array($result);

	$twenty_min = 60 * 20;

	if ($unix_time > $t_row['val']+$twenty_min) { // do the POP if 20 minutes elapsed.

		require ("../mail/pop3.php");

		$pop3=new pop3_class;
		$pop3->hostname=EMAIL_POP_SERVER;      /* POP 3 server host name              */
		$pop3->port=POP3_PORT;     /* POP 3 server host port              */
		$user=EMAIL_SMTP_USER;                /* Authentication user name            */
		$password=EMAIL_SMTP_PASS;           /* Authentication password             */
		$pop3->realm="";                        /* Authentication realm or domain      */
		$pop3->workstation="";                  /* Workstation for NTLM authentication */
		$apop=0;                                /* Use APOP authentication             */
		$pop3->authentication_mechanism="USER"; /* SASL authentication mechanism       */
		$pop3->debug=0;                         /* Output debug information            */
		$pop3->html_debug=0;                    /* Debug information is in HTML        */

		if(($error=$pop3->Open())=="") {
			
			if(($error=$pop3->Login($user,$password,$apop))=="") {
				
				if(($error=$pop3->Statistics($messages,$size))=="") {

				}
			}
		}

		$sql = "REPLACE INTO config (`key`, `val`) VALUES ('LAST_MAIL_POP', '$unix_time')  ";
		$result = @mysql_query($sql) or $DB_ERROR = mysql_error();

	} 





}


############################

function process_mail_queue($send_count=1) {

	$now = (gmdate("Y-m-d H:i:s"));
	$unix_time = time();

	// get the time of last run
	$sql = "SELECT * FROM `config` where `key` = 'LAST_MAIL_QUEUE_RUN' ";
	$result = @mysql_query($sql) or $DB_ERROR = mysql_error();
	$t_row = @mysql_fetch_array($result);

	if ($DB_ERROR!='') return $DB_ERROR;

	// Poor man's lock (making sure that this function is a Singleton)
	$sql = "UPDATE `config` SET `val`='YES' WHERE `key`='MAIL_QUEUE_RUNNING' AND `val`='NO' ";
	$result = @mysql_query($sql) or $DB_ERROR = mysql_error();
	if (@mysql_affected_rows()==0) {

		// make sure it cannot be locked for more than 30 secs 
		// This is in case the proccess fails inside the lock
		// and does not release it.

		if ($unix_time > $t_row['val']+30) {
			// release the lock
			
			$sql = "UPDATE `config` SET `val`='NO' WHERE `key`='MAIL_QUEUE_RUNNING' ";
			$result = @mysql_query($sql) or $DB_ERROR = mysql_error();

			// update timestamp
			$sql = "REPLACE INTO config (`key`, `val`) VALUES ('LAST_MAIL_QUEUE_RUN', '$unix_time')  ";
			$result = @mysql_query($sql) or $DB_ERROR = mysql_error();
		}


		return; // this function is already executing in another process.
	}



	if ($unix_time > $t_row['val']+5) { // did 5 seconds elapse since last run?


		if (EMAIL_POP_BEFORE_SMTP=='YES') {
			do_pop_before_smtp();
		}


		

		if (func_num_args>1) {
			$mail_id = func_get_arg(1);

			$and_mail_id = " AND mail_id=".$mail_id." ";

		}

		

		$EMAILS_MAX_RETRY = EMAILS_MAX_RETRY;
		if ($EMAILS_MAX_RETRY=='') {
			$EMAILS_MAX_RETRY = 5;
		}

		$EMAILS_ERROR_WAIT = EMAILS_ERROR_WAIT;
		if ($EMAILS_ERROR_WAIT=='') {
			$EMAILS_ERROR_WAIT = 10;
		}

		$sql = "SELECT * from mail_queue where (status='queued' OR status='error') AND retry_count <= ".$EMAILS_MAX_RETRY." $and_mail_id order by mail_date DESC";
		$result = mysql_query ($sql) or q_mail_error (mysql_error().$sql);
		while (($row = mysql_fetch_array($result))&&($send_count > 0)) {
			$time_stamp = strtotime($row['date_stamp']);
			$now = strtotime(gmdate("Y-m-d H:i:s"));
			$wait = $EMAILS_ERROR_WAIT * 60;
			//echo "(($now - $wait) > $time_stamp) status:".$row['status']."\n";
			if (((($now - $wait) > $time_stamp) && ($row['status']=='error')) || ($row['status']=='queued')) {
				$send_count--;
				//echo "Sending mail: ".$row[mail_id]."<br>";

				//$error = send_email ( $to_address, $to_name, $from_address, $from_name, $subject, $message,  $html_message='', $template_id=0 );
				$error = send_smtp_email($row);
			}
		}

		
		// delete old stuff

		if ((EMAILS_DAYS_KEEP=='EMAILS_DAYS_KEEP')) { define (EMAILS_DAYS_KEEP, '0'); }

		if (EMAILS_DAYS_KEEP>0) {

			$now = (gmdate("Y-m-d H:i:s"));

			$sql = "SELECT mail_id, att1_name, att2_name, att3_name from mail_queue where status='sent' AND DATE_SUB('$now',INTERVAL ".EMAILS_DAYS_KEEP." DAY) >= date_stamp  ";

			$result = mysql_query ($sql) or die(mysql_error());

			while ($row=mysql_fetch_array($result)) {

				if ($row[att1_name]!='') {
					unlink($row[att1_name]);
				}

				if ($row[att2_name]!='') {
					unlink($row[att2_name]);
				}

				if ($row[att3_name]!='') {
					unlink($row[att3_name]);
				}

				$sql = "DELETE FROM mail_queue where mail_id='".$row[mail_id]."' ";
				mysql_query($sql) or die(mysql_error());



			}

		}


	}

	// release the poor man's lock
	$sql = "UPDATE `config` SET `val`='NO' WHERE `key`='MAIL_QUEUE_RUNNING' ";
	@mysql_query($sql) or die(mysql_error());


}


############################

// $mail_row ->full email row from the database
function send_smtp_email($mail_row) {

	

	$to_name = html_ent_to_utf8($mail_row['to_name']);
	$to_address = $mail_row['to_address'];
	$from_name = html_ent_to_utf8($mail_row['from_name']);
	$from_address = $mail_row['from_address'];
	$subject = html_ent_to_utf8($mail_row['subject']);
	$message = html_ent_to_utf8($mail_row['message']);
	$html_message = html_ent_to_utf8($mail_row['html_message']);

	//$html_message = $mail_row['html_message'];

	$email_message=new smtp_message_class;

	$dir = dirname(__FILE__);
	$dir = preg_split ('%[/\\\]%', $dir);
	$blank = array_pop($dir);
	$dir = implode('/', $dir);

	if (!class_exists("sasl_client_class")) {
		require("$dir/mail/sasl/sasl.php");
	}


    $email_message->localhost=EMAIL_HOSTNAME;
    $email_message->smtp_host=EMAIL_SMTP_SERVER;
    $email_message->smtp_direct_delivery=0;
    $email_message->smtp_exclude_address="";
    $email_message->smtp_user=EMAIL_SMTP_USER;
    $email_message->smtp_realm="";
    $email_message->smtp_password=EMAIL_SMTP_PASS;
    $email_message->smtp_pop3_auth_host=EMAIL_SMTP_AUTH_HOST;
	$email_message->smtp_ssl=0;

	$email_message->authentication_mechanism = 'USER'; // SASL authentication

	if (EMAIL_DEBUG_SWITCH=='YES') {
        $email_message->smtp_debug=1;
    } else {
         $email_message->smtp_debug=0;
    }
   
    $email_message->smtp_html_debug=0;

	
//echo "[$to_address], [$to_name], [$from_address], [$from_name], [$subject], [$message], [$html_messageaz]";
	$reply_address=$mail_row['from_address'];
	
	$error_delivery_name=SITE_NAME;
	$error_delivery_address=SITE_CONTACT_EMAIL;
	
	
	//$message="Hello ".strtok($to_name," ").",\n\nThis message is just to let you know that your e-mail sending class is working as expected.\n\nHere's some non-ASCII characters ����� in the message body to let you see if they are sent properly encoded.\n\nThank you,\n$from_name";
	//$email_message=new email_message_class;
	$email_message->default_charset='UTF-8';
	$email_message->SetEncodedEmailHeader("To",$to_address,$to_name);
	$email_message->SetEncodedEmailHeader("From",$from_address,$from_name);
	$email_message->SetEncodedEmailHeader("Reply-To",$reply_address,$reply_name);
/*
	Set the Return-Path header to define the envelope sender address to which bounced messages are delivered.
	If you are using Windows, you need to use the smtp_message_class to set the return-path address.
*/
	if(defined("PHP_OS")
	&& strcmp(substr(PHP_OS,0,3),"WIN"))
		$email_message->SetHeader("Return-Path",$error_delivery_address);
	$email_message->SetEncodedEmailHeader("Errors-To",$error_delivery_address,$error_delivery_name);
	$email_message->SetEncodedHeader("Subject",$subject);
	

	if ($html_message=='') { // ONLY TEXT
		
		$email_message->AddQuotedPrintableTextPart($email_message->WrapText($message));
	}else {
		
		$email_message->CreateQuotedPrintableHTMLPart($html_message,"",$html_part);
		//$text_message="This is an HTML message. Please use an HTML capable mail program to read this message.";
		$email_message->CreateQuotedPrintableTextPart($email_message->WrapText($message),"",$text_part);

		$alternative_parts=array(
			$html_part,
			$text_part
			
		);
		$email_message->AddAlternativeMultipart($alternative_parts);

	}

	if ($mail_row[attachments]=='Y') {
		add_mail_attachments($email_message, $mail_row);
	}

	$error=$email_message->Send();
	if(strcmp($error,"")) {
		//echo "Error: $error\n";
		$now = gmdate("Y-m-d H:i:s");

		$sql = "UPDATE mail_queue SET status='error', retry_count=retry_count+1,  error_msg='".addslashes($error)."', `date_stamp`='$now' WHERE mail_id=".$mail_row['mail_id'];
		//echo $sql;
		mysql_query($sql) or q_mail_error(mysql_error().$sql);



	} else {

		$now = gmdate("Y-m-d H:i:s");

		$sql = "UPDATE mail_queue SET status='sent', `date_stamp`='$now' WHERE mail_id=".$mail_row['mail_id'];
		mysql_query($sql) or q_mail_error(mysql_error().$sql);
		//echo $sql;


	}

	//echo ".";


}

?>