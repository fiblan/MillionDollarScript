<?php
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

define ('MDS_DEBUG', false);
define ('MDS_DEBUG_LOG', '');
$old_log_req;
function mds_log($line) {
	global $old_log_req;
	if (MDS_DEBUG===true) {

		$now = date('r');

		foreach ($_REQUEST as $key=>$val) {
			$str = $str." $key=>$val | ";
		}
		if ($old_log_req!=$str) {
			$old_log_req = $str;
		} else {
			$str = '';
		}

		$entry_line =  "$now - $entry_line | _REQUEST: $str \r\n "; 
		$log_fp = fopen(MDS_DEBUG_LOG, "a"); 
		fputs($log_fp, $entry_line); 
		fclose($log_fp);

	}

}

require_once('area_map_functions.php');
require_once('package_functions.php');
require_once('banner_functions.php');
require_once('image_functions.php');


if (!defined('UPLOAD_PATH')) {
	$dir = dirname(__FILE__);
	$dir = preg_split ('%[/\\\]%', $dir);
	$blank = array_pop($dir);
	$dir = implode('/', $dir);
	define ('UPLOAD_PATH', $dir.'/upload_files/');

}

if (!defined('UPLOAD_HTTP_PATH')) {

	$host = $_SERVER['SERVER_NAME']; // hostname
	$http_url = $_SERVER['PHP_SELF']; // eg /ojo/admin/edit_config.php
	$http_url = explode ("/", $http_url);
	array_pop($http_url); // get rid of filename
	array_pop($http_url); // get rid of /admin
	$http_url = implode ("/", $http_url);

	define ('UPLOAD_HTTP_PATH', "http://".$host.$http_url."/upload_files/");
}
 

#---------------------------------------------------------------------
# Written for having magic quotes enabled
#---------------------------------------------------------------------
#####################################################
function unfck($v) {
   return is_array($v) ? array_map('unfck', $v) : addslashes($v);
}
######################################################
 function unfck_gpc() {
	
   foreach (array('POST', 'GET', 'REQUEST', 'COOKIE') as $gpc) {
	   $GLOBALS["_$gpc"] = array_map('unfck', $GLOBALS["_$gpc"]);
	   
   }
   
}


##################################################


if ($_REQUEST['time']=='') {
	if (NO_HOUSE_KEEP!='YES') {
		expire_orders();
	}
}

function expire_orders() {

	$now = (gmdate("Y-m-d H:i:s"));
	$unix_time = time();

	// get the time of last run
	$sql = "SELECT * FROM `config` where `key` = 'LAST_EXPIRE_RUN' ";
	$result = @mysql_query($sql) or $DB_ERROR = mysql_error();
	$t_row = @mysql_fetch_array($result);

	if ($DB_ERROR!='') return $DB_ERROR;

	// Poor man's lock
	$sql = "UPDATE `config` SET `val`='YES' WHERE `key`='EXPIRE_RUNNING' AND `val`='NO' ";
	$result = @mysql_query($sql) or $DB_ERROR = mysql_error();
	if (@mysql_affected_rows()==0) {

		// make sure it cannot be locked for more than 30 secs 
		// This is in case the proccess fails inside the lock
		// and does not release it.

		if ($unix_time > $t_row['val']+30) {
			// release the lock
			
			$sql = "UPDATE `config` SET `val`='NO' WHERE `key`='EXPIRE_RUNNING' ";
			$result = @mysql_query($sql) or $DB_ERROR = mysql_error();

			// update timestamp
			$sql = "REPLACE INTO config (`key`, `val`) VALUES ('LAST_EXPIRE_RUN', '$unix_time')  ";
			$result = @mysql_query($sql) or $DB_ERROR = mysql_error();
		}


		return; // this function is already executing in another process.
	}



	if ($unix_time > $t_row['val']+60) { // did 1 minute elapse since last run?

		// Delete Temp Orders

		$session_duration = ini_get ("session.gc_maxlifetime");
		
		$sql = "SELECT session_id,  order_date FROM `temp_orders` WHERE  DATE_SUB('$now', INTERVAL $session_duration SECOND) >= temp_orders.order_date AND session_id <> '".addslashes(session_id())."' ";

		$result=mysql_query($sql);
		
		while ($row = @mysql_fetch_array($result)) {
		
			delete_temp_order($row['session_id']);

		}


		// COMPLTED Orders

		$sql = "SELECT *, banners.banner_id as BID from orders, banners where status='completed' and orders.banner_id=banners.banner_id AND orders.days_expire <> 0 AND DATE_SUB('$now', INTERVAL orders.days_expire DAY) >= orders.date_published AND orders.date_published IS NOT NULL ";

		//echo $sql;

		$result = mysql_query ($sql);

		$affected_BIDs = array();

		while ($row=@mysql_fetch_array($result)) {
			$affected_BIDs[] = $row['BID'];
			expire_order ($row['order_id']);

		}
		if (sizeof($affected_BIDs)>0) {
			foreach ($affected_BIDs as $myBID) {
				$b_row = load_banner_row($myBID);
				if ($b_row['auto_publish']=='Y') {
					process_image($myBID);
					publish_image($myBID);
					process_map($myBID);
				}
				
			}
		}
		process_paid_renew_orders();
		unset($affected_BIDs);

		// unconfirmed Orders

		if (HOURS_UNCONFIRMED!=0) {

			$sql = "SELECT * from orders where (status='new') AND DATE_SUB('$now',INTERVAL ".HOURS_UNCONFIRMED." HOUR) >= date_stamp AND date_stamp IS NOT NULL ";

			$result = @mysql_query ($sql);

			while ($row=@mysql_fetch_array($result)) {
				delete_order ($row['order_id']) ;

				// Now really delete the order.

				$sql = "delete from orders where order_id='".$row['order_id']."'";
				@mysql_query ($sql);
				mds_log("Deleted unconfirmed order - ".$sql);

			}


		}

		// unpaid Orders
		if (DAYS_CONFIRMED!=0) { 
			$sql = "SELECT * from orders where (status='new' OR status='confirmed') AND DATE_SUB('$now',INTERVAL ".DAYS_CONFIRMED." DAY) >= date_stamp AND date_stamp IS NOT NULL ";

			$result = @mysql_query ($sql);

			while ($row=@mysql_fetch_array($result)) {
				expire_order ($row['order_id']) ;

			}

		}

		// EXPIRED Orders -> Cancel

		if (DAYS_RENEW!=0) { 

			$sql = "SELECT * from orders where status='expired'  AND DATE_SUB('$now',INTERVAL ".DAYS_RENEW." DAY) >= date_stamp AND date_stamp IS NOT NULL ";

			$result = @mysql_query ($sql);

			while ($row=@mysql_fetch_array($result)) {
				cancel_order ($row['order_id']) ;

			}

		}

		// Cancelled Orders -> Delete

		if (DAYS_CANCEL!=0) {

			$sql = "SELECT * from orders where status='cancelled' AND DATE_SUB('$now',INTERVAL ".DAYS_CANCEL." DAY) >= date_stamp AND date_stamp IS NOT NULL ";

			$result = @mysql_query ($sql);

			while ($row=@mysql_fetch_array($result)) {
				delete_order ($row['order_id']) ;
			}

		}

		// update last run time stamp

		// update timestamp
		$sql = "REPLACE INTO config (`key`, `val`) VALUES ('LAST_EXPIRE_RUN', '$unix_time')  ";
		$result = @mysql_query($sql) or die (mysql_error());
		


	}

	// release the poor man's lock
	$sql = "UPDATE `config` SET `val`='NO' WHERE `key`='EXPIRE_RUNNING' ";
	@mysql_query($sql) or die(mysql_error());

	

}

#################################################

function delete_temp_order($sid, $delete_ad=true) {

	$sid = addslashes($sid);

	$sql = "select * from temp_orders where session_id='".$sid."' ";
	$order_result = mysql_query ($sql) or die(mysql_error());
	$order_row = mysql_fetch_array($order_result);

	//$sql = "DELETE FROM blocks WHERE session_id='".$sid."' ";
	//mysql_query($sql) ;

	$sql = "DELETE FROM temp_orders WHERE session_id='".$sid."' ";
	mysql_query ($sql);

	if ($delete_ad) {
		$sql = "DELETE FROM ads WHERE ad_id='".$order_row['ad_id']."' ";
		mysql_query ($sql);
	}
	
	
	// delete the temp order image... and block info...

	$f = get_tmp_img_name($sid);
	if (file_exists($f)) unlink($f);
	$filename = SERVER_PATH_TO_ADMIN.'temp/'."info_".md5(session_id()).".txt";
	//$filename = SERVER_PATH_TO_ADMIN.'temp/'."info_".$sid.".txt";
	if (file_exists($filename)) unlink($filename);


}

#################################################
/*

Type:  CREDIT (subtract)

$txn_id = transaction id from 3rd party payment system

$reson = any reason such as chargeback, refund etc..

$origin = paypal, stormpay, admin, etc

$order_id = the corresponding order id.

*/

function credit_transaction($order_id, $amount, $currency, $txn_id, $reason, $origin) {

	$type = "CREDIT";

	$date = (gmdate("Y-m-d H:i:s"));

	$sql = "SELECT * FROM transactions where txn_id='$txn_id' and `type`='CREDIT' ";
	$result = mysql_query($sql) or die(mysql_error($sql));
	if (mysql_num_rows($result)!=0) {
		return; // there already is a credit for this txn_id
	}

// check to make sure that there is a debit for this transaction

	$sql = "SELECT * FROM transactions where txn_id='$txn_id' and `type`='DEBIT' ";
	$result = mysql_query($sql) or die(mysql_error($sql));
	if (mysql_num_rows($result)>0) {

		$sql = "INSERT INTO transactions (`txn_id`, `date`, `order_id`, `type`, `amount`, `currency`, `reason`, `origin`) VALUES('$txn_id', '$date', '$order_id', '$type', '$amount', '$currency', '$reason', '$origin')";

		$result = mysql_query ($sql) or die (mysql_error());
	}


}
#################################################
/*

Type: DEBIT (add)

$txn_id = transaction id from 3rd party payment system

$reson = any reason such as chargeback, refund etc..

$origin = paypal, stormpay, admin, etc

$order_id = the corresponding order id.

*/

function debit_transaction($order_id, $amount, $currency, $txn_id, $reason, $origin) {

	
	$type = "DEBIT";
	$date = (gmdate("Y-m-d H:i:s"));
// check to make sure that there is no debit for this transaction already

	$sql = "SELECT * FROM transactions where txn_id='$txn_id' and `type`='DEBIT' ";
	$result = mysql_query($sql) or die(mysql_error().$sql);
	if (mysql_fetch_array($result)==0) {
		$sql = "INSERT INTO transactions (`txn_id`, `date`, `order_id`, `type`, `amount`, `currency`, `reason`, `origin`) VALUES('$txn_id', '$date', '$order_id', '$type', '$amount', '$currency', '$reason', '$origin')";

		$result = mysql_query ($sql) or die (mysql_error().$sql);
	}


}
##################################################

function complete_order ($user_id, $order_id) {
	global $label;

	$sql = "SELECT * from orders where order_id='$order_id' ";
	$result = mysql_query ($sql) or die (mysql_error().$sql);
	$order_row = mysql_fetch_array ($result);

	if ($order_row['status']!='completed') {

		$now = (gmdate("Y-m-d H:i:s"));

		$sql = "UPDATE orders set status='completed', date_published=NULL, date_stamp='$now' WHERE order_id='".$order_id."'";
		mysql_query ($sql) or die (mysql_error().$sql);

		// insert a transaction

		// mark pixels as sold.
		

		$sql = "SELECT * from orders where order_id='$order_id' ";
		$result = mysql_query ($sql) or die (mysql_error().$sql);
		$order_row = mysql_fetch_array ($result);

		$blocks = explode (",", $order_row[blocks]);
		foreach ($blocks as $key => $val) {
			$sql = "UPDATE blocks set status='sold' where block_id='$val' and banner_id=".$order_row['banner_id'];
			
			mysql_query ($sql) or die (mysql_error().$sql);


		}

		$sql = "SELECT * from users where ID='$user_id' ";
		$result = mysql_query ($sql) or die (mysql_error().$sql);
		$user_row = mysql_fetch_array ($result);

		if ($order_row[days_expire]==0) {
			$order_row[days_expire]=$label['advertiser_ord_never'];
		}

		$label["order_completed_email_template"] = str_replace ("%SITE_NAME%", SITE_NAME, $label["order_completed_email_template"]);
		$label["order_completed_email_template"] = str_replace ("%FNAME%", $user_row['FirstName'], $label["order_completed_email_template"]);
		$label["order_completed_email_template"] = str_replace ("%LNAME%", $user_row['LastName'], $label["order_completed_email_template"]);
		$label["order_completed_email_template"] = str_replace ("%ORDER_ID%", $order_row['order_id'], $label["order_completed_email_template"]);
	
		$label["order_completed_email_template"] = str_replace ("%PIXEL_COUNT%", $order_row['quantity'], $label["order_completed_email_template"]);
		$label["order_completed_email_template"] = str_replace ("%PIXEL_DAYS%", $order_row['days_expire'], $label["order_completed_email_template"]);
		$label["order_completed_email_template"] = str_replace ("%PRICE%", convert_to_default_currency_formatted($order_row[currency], $order_row['price']), $label["order_completed_email_template"]);
		$label["order_completed_email_template"] = str_replace ("%SITE_CONTACT_EMAIL%", SITE_CONTACT_EMAIL, $label["order_completed_email_template"]);
		$label["order_completed_email_template"] = str_replace ("%SITE_URL%", BASE_HTTP_PATH, $label["order_completed_email_template"]);
		$message = $label["order_completed_email_template"];
		$to = trim($user_row['Email']);
		$subject = $label['order_completed_email_subject'];
		
	
		if (EMAIL_USER_ORDER_COMPLETED=='YES') {

			if (USE_SMTP=='YES') {
				$mail_id=queue_mail(addslashes($to), addslashes($user_row['FirstName']." ".$user_row['LastName']), addslashes(SITE_CONTACT_EMAIL), addslashes(SITE_NAME), addslashes($subject), addslashes($message), '', 1);
				process_mail_queue(2, $mail_id);
			} else {
				send_email( $to, $user_row['FirstName']." ".$user_row['LastName'], SITE_CONTACT_EMAIL, SITE_NAME, $subject, $message, '', 1);
			}
			
		}

		// send a copy to admin

		if (EMAIL_ADMIN_ORDER_COMPLETED=='YES') {

			if (USE_SMTP=='YES') {
				$mail_id=queue_mail(addslashes(SITE_CONTACT_EMAIL), addslashes($user_row[FirstName]." ".$user_row[LastName]), addslashes(SITE_CONTACT_EMAIL), addslashes(SITE_NAME), addslashes($subject), addslashes($message), '', 1);
				process_mail_queue(2, $mail_id);
			} else {
				send_email( SITE_CONTACT_EMAIL, $user_row[FirstName]." ".$user_row[LastName], SITE_CONTACT_EMAIL, SITE_NAME, $subject, $message, '', 1);
			}
			
		}

		// process the grid, if auto_publish is on

		$b_row = load_banner_row($order_row['banner_id']);

		if ($b_row['auto_publish']=='Y') {
			process_image($order_row['banner_id']);
			publish_image($order_row['banner_id']);
			process_map($order_row['banner_id']);
		}

		

	}

}


##########################################

function confirm_order ($user_id, $order_id) {
	global $label;

	$sql = "SELECT *, t1.blocks as BLK FROM orders as t1, users as t2 where t1.user_id=t2.ID AND t1.user_id=t2.ID AND order_id='$order_id' ";
	$result = mysql_query ($sql) or die (mysql_error().$sql);
	$row = mysql_fetch_array($result);
	//echo $sql;

	if ($row['status']!='confirmed') {

		$now = (gmdate("Y-m-d H:i:s"));

		$sql = "UPDATE orders set status='confirmed', date_stamp='$now' WHERE order_id='".$order_id."' ";
		//echo $sql."<br>";
		mysql_query($sql) or die (mysql_error().$sql);

		//echo "User id: ".$_SESSION['MDS_ID'];

		$_SESSION['MDS_order_id']==''; // destroy order id

		$sql = "UPDATE blocks set status='ordered' WHERE order_id='".$order_id."' and banner_id='".$row['banner_id']."'";

		mysql_query($sql) or die (mysql_error().$sql);

		/*

		$blocks = explode (',', $row['BLK']);
		//echo $order_row['blocks'];
		foreach ($blocks as $key => $val) {

			$sql = "UPDATE blocks set status='ordered' WHERE block_id='".$val."' and banner_id='".$row['banner_id']."'";

			//echo $sql."<br>";
			
			mysql_query($sql) or die (mysql_error().$sql);
		}

		*/

		if ($row[days_expire]==0) {
			$row[days_expire]=$label['advertiser_ord_never'];
		}


		$label["order_confirmed_email_template"] = str_replace ("%SITE_NAME%", SITE_NAME, $label["order_confirmed_email_template"]);
		$label["order_confirmed_email_template"] = str_replace ("%FNAME%", $row[FirstName], $label["order_confirmed_email_template"]);
		$label["order_confirmed_email_template"] = str_replace ("%LNAME%", $row[LastName], $label["order_confirmed_email_template"]);
		$label["order_confirmed_email_template"] = str_replace ("%ORDER_ID%", $row[order_id], $label["order_confirmed_email_template"]);
		$label["order_confirmed_email_template"] = str_replace ("%PIXEL_COUNT%", $row[quantity], $label["order_confirmed_email_template"]);
		$label["order_confirmed_email_template"] = str_replace ("%PIXEL_DAYS%", $row[days_expire], $label["order_confirmed_email_template"]);
		$label["order_confirmed_email_template"] = str_replace ("%PRICE%", convert_to_default_currency_formatted($row[currency], $row[price]), $label["order_confirmed_email_template"]);
		$label["order_confirmed_email_template"] = str_replace ("%SITE_CONTACT_EMAIL%", SITE_CONTACT_EMAIL, $label["order_confirmed_email_template"]);
		$label["order_confirmed_email_template"] = str_replace ("%SITE_URL%", BASE_HTTP_PATH, $label["order_confirmed_email_template"]);
		$message = $label["order_confirmed_email_template"];
		$to = trim($row['Email']);
		$subject = $label['order_confirmed_email_subject'];
		
	

		if (EMAIL_USER_ORDER_CONFIRMED=='YES') {

			if (USE_SMTP=='YES') {
				$mail_id=queue_mail(addslashes($to), addslashes($row[FirstName]." ".$row[LastName]), addslashes(SITE_CONTACT_EMAIL), addslashes(SITE_NAME), addslashes($subject), addslashes($message), '', 2);
				process_mail_queue(2, $mail_id);
			} else {
				send_email( $to, $row[FirstName]." ".$row[LastName], SITE_CONTACT_EMAIL, SITE_NAME, $subject, $message, '', 2);
			}

			//@mail($to,$subject,$message,$headers);
		}

		// send a copy to admin
		if (EMAIL_ADMIN_ORDER_CONFIRMED=='YES') {

			if (USE_SMTP=='YES') {
				$mail_id=queue_mail(addslashes(SITE_CONTACT_EMAIL), addslashes($row[FirstName]." ".$row[LastName]), addslashes(SITE_CONTACT_EMAIL), addslashes(SITE_NAME), addslashes($subject), addslashes($message), '', 2);
				process_mail_queue(2, $mail_id);
			} else {
				send_email( SITE_CONTACT_EMAIL, $row[FirstName]." ".$row[LastName], SITE_CONTACT_EMAIL, SITE_NAME, $subject, $message, '', 2);
			}
			//@mail(trim(SITE_CONTACT_EMAIL),$subject,$message,$headers);
		}

	}



    

}

##########################################

function pend_order ($user_id, $order_id) {
	global $label;
	$sql = "SELECT * FROM orders as t1, users as t2 where t1.user_id=t2.ID AND t1.user_id='".$user_id."' AND order_id='$order_id' ";
	
	$result = mysql_query ($sql) or die (mysql_error().$sql);
	$row = mysql_fetch_array($result);

	if ($row['status']!='pending') {

		$now = (gmdate("Y-m-d H:i:s"));

		$sql = "UPDATE orders set status='pending', date_stamp='$now' WHERE order_id='".$order_id."' ";
		//echo $sql;
		mysql_query($sql) or die (mysql_error().$sql);

		$blocks = explode (',', $row['blocks']);
		//echo $order_row['blocks'];
		foreach ($blocks as $key => $val) {

			$sql = "UPDATE blocks set status='ordered' WHERE block_id='".$val."' and banner_id='".$row['banner_id']."'";
			//echo $sql;
			mysql_query($sql) or die (mysql_error().$sql);
		}


		if ($row[days_expire]==0) {
			$row[days_expire]=$label['advertiser_ord_never'];
		}

	
		$label["order_pending_email_template"] = str_replace ("%SITE_NAME%", SITE_NAME, $label["order_pending_email_template"]);
		$label["order_pending_email_template"] = str_replace ("%FNAME%", $row[FirstName], $label["order_pending_email_template"]);
		$label["order_pending_email_template"] = str_replace ("%LNAME%", $row[LastName], $label["order_pending_email_template"]);
		$label["order_pending_email_template"] = str_replace ("%ORDER_ID%", $row[order_id], $label["order_pending_email_template"]);
		$label["order_pending_email_template"] = str_replace ("%PIXEL_COUNT%", $row[quantity], $label["order_pending_email_template"]);
		$label["order_pending_email_template"] = str_replace ("%PIXEL_DAYS%", $row[days_expire], $label["order_pending_email_template"]);
		$label["order_pending_email_template"] = str_replace ("%PRICE%", convert_to_default_currency_formatted($row[currency], $row[price]), $label["order_pending_email_template"]);
		$label["order_pending_email_template"] = str_replace ("%SITE_CONTACT_EMAIL%", SITE_CONTACT_EMAIL, $label["order_pending_email_template"]);
		$label["order_pending_email_template"] = str_replace ("%SITE_URL%", BASE_HTTP_PATH, $label["order_pending_email_template"]);
		$message = $label["order_pending_email_template"];
		$to = trim($row['Email']);
		$subject = $label['order_pending_email_subject'];
		
		
		if (EMAIL_USER_ORDER_PENDED=='YES') {
			if (USE_SMTP=='YES') {
				queue_mail(addslashes($to), addslashes($row[FirstName]." ".$row[LastName]), addslashes(SITE_CONTACT_EMAIL), addslashes(SITE_NAME), addslashes($subject), addslashes($message), '', 3);
			} else {
				send_email( $to, $row[FirstName]." ".$row[LastName], SITE_CONTACT_EMAIL, SITE_NAME, $subject, $message, '', 3);
			}
			//@mail($to,$subject,$message,$headers);
		}

		// send a copy to admin
		if (EMAIL_ADMIN_ORDER_PENDED=='YES') {
			if (USE_SMTP=='YES') {
				$mail_id=queue_mail(addslashes(SITE_CONTACT_EMAIL), addslashes($row[FirstName]." ".$row[LastName]), addslashes(SITE_CONTACT_EMAIL), addslashes(SITE_NAME), addslashes($subject), addslashes($message), '', 3);
				process_mail_queue(2, $mail_id);
			} else {
				send_email( SITE_CONTACT_EMAIL, $row[FirstName]." ".$row[LastName], SITE_CONTACT_EMAIL, SITE_NAME, $subject, $message, '', 3);
			}
			//@mail(trim(SITE_CONTACT_EMAIL),$subject,$message,$headers);
		}

	}


}
function func_mail_error($msg) {

	$date = date("D, j M Y H:i:s O"); 
	
	$headers = "From: ". SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Reply-To: ".SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "Return-Path: ".SITE_CONTACT_EMAIL ."\r\n";
	$headers .= "X-Mailer: PHP" ."\r\n";
	$headers .= "Date: $date" ."\r\n"; 
	$headers .= "X-Sender-IP: $REMOTE_ADDR" ."\r\n";

	$entry_line =  "(payal error detected) $msg\r\n "; 
	$log_fp = @fopen("logs.txt", "a"); 
	@fputs($log_fp, $entry_line); 
	@fclose($log_fp);


	@mail(SITE_CONTACT_EMAIL, "Error message from ".SITE_NAME." Jamit Paypal IPN script. ", $msg, $headers);

}


########################################################

function expire_order ($order_id) {
	global $label;
	$sql = "SELECT *, t1.banner_id as BID, t1.user_id as UID FROM orders as t1, users as t2 where t1.user_id=t2.ID AND  order_id='$order_id' ";
	//echo "$sql<br>";
	//days_expire

	//func_mail_error($sql." expire order");
	$result = mysql_query ($sql) or die (mysql_error());
	$row = mysql_fetch_array($result);

	if (($row['status']!='expired') || ($row['status']!='pending')) {


		$now = (gmdate("Y-m-d H:i:s"));

		$sql = "UPDATE orders set status='expired', date_stamp='$now' WHERE order_id='".$order_id."' ";
		//echo "$sql<br>";
		mysql_query($sql) or die (mysql_error().$sql);

		$sql = "UPDATE blocks set status='ordered', `approved`='N' WHERE order_id='".$order_id."' and banner_id='".$row['BID']."'";
			//echo "$sql<br>";
		mysql_query($sql) or die (mysql_error().$sql." (expire order)");

		/*

		$blocks = explode (',', $row['blocks']);
		//echo $order_row['blocks'];
		foreach ($blocks as $key => $val) {

			$sql = "UPDATE blocks set status='ordered', `approved`='N' WHERE block_id='".$val."' and banner_id='".$row['BID']."'";
			//echo "$sql<br>";
			mysql_query($sql) or die (mysql_error().$sql." (expire order)");
			

		}

		*/

		// update approve status on orders.

		$sql = "UPDATE orders SET `approved`='N' WHERE order_id='".$order_id."'";
		//echo "$sql<br>";
		mysql_query($sql) or die (mysql_error().$sql." (expire order)");

		if ($row['status']=='new') {
			return;// do not send email
		}


		if ($row[days_expire]==0) {
			$row[days_expire]=$label['advertiser_ord_never'];
		}

		$label["order_expired_email_template"] = str_replace ("%SITE_NAME%", SITE_NAME, $label["order_expired_email_template"]);
		$label["order_expired_email_template"] = str_replace ("%FNAME%", $row[FirstName], $label["order_expired_email_template"]);
		$label["order_expired_email_template"] = str_replace ("%LNAME%", $row[LastName], $label["order_expired_email_template"]);
		$label["order_expired_email_template"] = str_replace ("%ORDER_ID%", $row[order_id], $label["order_expired_email_template"]);
		$label["order_expired_email_template"] = str_replace ("%PIXEL_COUNT%", $row[quantity], $label["order_expired_email_template"]);
		$label["order_expired_email_template"] = str_replace ("%PIXEL_DAYS%", $row[days_expire], $label["order_expired_email_template"]);
		$label["order_expired_email_template"] = str_replace ("%PRICE%", convert_to_default_currency_formatted($row[currency], $row[price]), $label["order_expired_email_template"]);
		$label["order_expired_email_template"] = str_replace ("%SITE_CONTACT_EMAIL%", SITE_CONTACT_EMAIL, $label["order_expired_email_template"]);
		$label["order_expired_email_template"] = str_replace ("%SITE_URL%", BASE_HTTP_PATH, $label["order_expired_email_template"]);
		$message = $label["order_expired_email_template"];
		$to = trim($row['Email']);
		$subject = $label['order_expired_email_subject'];
		
		
		if (EMAIL_USER_ORDER_EXPIRED=='YES') {
			if (USE_SMTP=='YES') {
				$mail_id=queue_mail(addslashes($to), addslashes($row[FirstName]." ".$row[LastName]), addslashes(SITE_CONTACT_EMAIL), addslashes(SITE_NAME), addslashes($subject), addslashes($message), '', 4);
				process_mail_queue(2, $mail_id);
			} else {
				send_email( $to, $row[FirstName]." ".$row[LastName], SITE_CONTACT_EMAIL, SITE_NAME, $subject, $message, '', 4);
			}
			//@mail($to,$subject,$message,$headers);
		}

		// send a copy to admin
		if (EMAIL_ADMIN_ORDER_EXPIRED=='YES') {
			if (USE_SMTP=='YES') {
				$mail_id=queue_mail(addslashes(SITE_CONTACT_EMAIL), addslashes($row[FirstName]." ".$row[LastName]), addslashes(SITE_CONTACT_EMAIL), addslashes(SITE_NAME), addslashes($subject), addslashes($message), '', 4);
				process_mail_queue(2, $mail_id);
			} else {
				send_email( SITE_CONTACT_EMAIL, $row[FirstName]." ".$row[LastName], SITE_CONTACT_EMAIL, SITE_NAME, $subject, $message, '', 4);
			}
			//@mail(trim(EMAIL_ADMIN_ORDER_EXPIRED),$subject,$message,$headers);
		}


	}


}

########################################################

function delete_order ($order_id) {

	global $label;
	$sql = "SELECT * FROM orders where order_id='$order_id' ";
	$result = mysql_query ($sql) or die (mysql_error());
	$order_row = mysql_fetch_array($result);

	if ($order_row['status']!='deleted') {

		$now = (gmdate("Y-m-d H:i:s"));

		$sql = "UPDATE orders set status='deleted', date_stamp='$now' WHERE order_id='".$order_id."'";
		mysql_query ($sql) or die (mysql_error().$sql);

		// DELETE BLOCKS

		if ($order_row['blocks']!='') {

			$sql = "DELETE FROM blocks where order_id='$order_id' and banner_id=".$order_row['banner_id'];
			mysql_query ($sql) or die (mysql_error().$sql);


			/*
			$blocks = explode (",", $order_row['blocks']);
			foreach ($blocks as $key => $val) {
				if ($val!='') {
					$sql = "DELETE FROM blocks where block_id='$val' and banner_id=".$order_row['banner_id'];
					mysql_query ($sql) or die (mysql_error().$sql);
				}

			}
			*/

		}

		// DELETE ADS
		if (!function_exists('delete_ads_files')) {
			require_once("ads.inc.php");
		}
		delete_ads_files ($order_row['ad_id']);
		$sql = "DELETE from ads where ad_id='".$order_row['ad_id']."' ";
		mysql_query ($sql) or die (mysql_error().$sql);
		

	}


}

########################################################

function cancel_order ($order_id) {

	global $label;
	$sql = "SELECT * FROM orders where order_id='$order_id' ";
	$result = mysql_query ($sql) or die (mysql_error());
	$row = mysql_fetch_array($result);
	//echo $sql."<br>";
	if ($row['status']!='cancelled') {

		$now = (gmdate("Y-m-d H:i:s"));

		$sql = "UPDATE orders set status='cancelled', date_stamp='$now', approved='N' WHERE order_id='".$order_id."'";
		mysql_query ($sql) or die (mysql_error().$sql);
		//echo $sql."<br>";
		$sql = "UPDATE blocks set status='ordered', `approved`='N' WHERE order_id='".$order_id."' and banner_id='".$row['banner_id']."'";
			//echo $sql."<br>";
		mysql_query($sql) or die (mysql_error().$sql. " (cancel order) ");

		/*
		$blocks = explode (',', $row['blocks']);
		//echo $order_row['blocks'];

		
		foreach ($blocks as $key => $val) {
			$sql = "UPDATE blocks set status='ordered', `approved`='N' WHERE block_id='".$val."' and banner_id='".$row['banner_id']."'";
			//echo $sql."<br>";
			mysql_query($sql) or die (mysql_error().$sql. " (cancel order) ");
		}
		*/

	}

	// process the grid, if auto_publish is on

	$b_row = load_banner_row($row['banner_id']);

	if ($b_row['auto_publish']=='Y') {

		process_image($row['banner_id']);
		publish_image($row['banner_id']);
		process_map($row['banner_id']);
	}

	

}

########################################################
# is the renewal order already paid?
# (Orders can be paid and cont be completed until the previous order expires)
function is_renew_order_paid($original_order_id) {
	$sql = "SELECT * from orders WHERE original_order_id='$original_order_id' AND status='renew_paid' ";
	$result = mysql_query ($sql) or die(mysql_error());
	if (mysql_num_rows($result)>0) {
		return true;
	} else {
		return false;
	}

}

###########################################
# returns $order_id of the 'renew_wait' order
# only one 'renew_wait' wait order allowed for each $original_order_id
# and there must be no 'renew_paid' orders
function allocate_renew_order($original_order_id) {
	# if no waiting renew order, insert a new one
	$now = (gmdate("Y-m-d H:i:s"));
	
	if (is_renew_order_paid($original_order_id)) { // cannot allocate a renew_wait, this order was already paid and waiting to be completed.
		return false;
	}
	// are there any 
	// renew_wait orders?
	$sql = "SELECT * FROM orders WHERE original_order_id='$original_order_id' and status='renew_wait' ";
	$result = mysql_query ($sql) or die(mysql_error());
	if (($row = mysql_fetch_array($result))==false) {
		// copy the original order to create a new renew_wait order
		$sql = "SELECT * FROM orders WHERE order_id='$original_order_id' ";
		$result = mysql_query ($sql) or die(mysql_error()); 
		$row = mysql_fetch_array($result);

		$sql = "INSERT INTO orders (user_id, order_id, blocks, status, order_date, price, quantity, banner_id, currency, days_expire, date_stamp, approved, original_order_id) VALUES ('".$row['user_id']."', '', '".$row['blocks']."', 'renew_wait', NOW(), '".$row['price']."', '".$row['quantity']."', '".$row['banner_id']."', '".$row['currency']."', ".$row['days_expire'].", '$now', '".$row['approved']."', '".$original_order_id."') ";

		$result = mysql_query ($sql) or die(mysql_error());
		$order_id = mysql_insert_id();
		return $order_id;

	} else {
		return $row['order_id'];

	}



}



##########################################
# payment had been completed.
# allocate renew_wait, set it to renew_paid

function pay_renew_order($original_order_id) {

	$wait_order_id = allocate_renew_order($original_order_id);
	if ($wait_order_id !== false) {
		$sql = "UPDATE orders set status='renew_paid' WHERE order_id='$wait_order_id' and status='renew_wait' ";
		mysql_query($sql) or die(mysql_error());
		
	}

	if (mysql_affected_rows()>0) { 
		return true;
		# this order will now wait until the old one expires so it can be completed
	} else { 
		return false;
	}


}

#################################

function process_paid_renew_orders() {

	/*

	Complete: Only expired orders that have status as 'renew_paid'


	*/

	$sql = "SELECT * FROM orders WHERE status='renew_paid' ";
	$result = mysql_query($sql) or die (mysql_error());
	while ($row = mysql_fetch_array($result)) {
		// if expired 
		complete_renew_order ($row['order_id']) ;
	}
}

########################################################

function complete_renew_order ($order_id) {
	global $label;

	$sql = "SELECT * from orders where order_id='$order_id' and status='renew_paid' ";
	$result = mysql_query ($sql) or die (mysql_error().$sql);
	$order_row = mysql_fetch_array ($result);

	if ($order_row['status']!='completed') {

		$now = (gmdate("Y-m-d H:i:s"));

		$sql = "UPDATE orders set status='completed', date_published=NULL, date_stamp='$now' WHERE order_id=".$order_id;
		mysql_query ($sql) or die (mysql_error().$sql);

		// update pixel's order_id

		$sql = "UPDATE blocks SET order_id='".$order_row['order_id']."' WHERE order_id='".$order_row['original_order_id']."' AND banner_id='".$row['banner_id']."' ";
		mysql_query ($sql) or die (mysql_error().$sql);

		// update ads' order id

		$sql = "UPDATE ads SET order_id='".$order_row['order_id']."' WHERE order_id='".$order_row['original_order_id']."' AND banner_id='".$row['banner_id']."' ";
		mysql_query ($sql) or die (mysql_error().$sql);

		// mark pixels as sold.
		
		$sql = "SELECT * from orders where order_id='$order_id' ";
		$result = mysql_query ($sql) or die (mysql_error().$sql);
		$order_row = mysql_fetch_array ($result);
		$blocks = explode (",", $order_row['blocks']);
		foreach ($blocks as $key => $val) {
			$sql = "UPDATE blocks set status='sold' where block_id='$val' and banner_id=".$order_row['banner_id'];
			
			mysql_query ($sql) or die (mysql_error().$sql);

		}

		$sql = "SELECT * from users where ID='$user_id' ";
		$result = mysql_query ($sql) or die (mysql_error().$sql);
		$user_row = mysql_fetch_array ($result);

		if ($order_row['days_expire']==0) {
			$order_row['days_expire']=$label['advertiser_ord_never'];
		}

		$label["order_completed_renewal_email_template"] = str_replace ("%SITE_NAME%", SITE_NAME, $label["order_completed_renewal_email_template"]);
		$label["order_completed_renewal_email_template"] = str_replace ("%FNAME%", $user_row[FirstName], $label["order_completed_renewal_email_template"]);
		$label["order_completed_renewal_email_template"] = str_replace ("%LNAME%", $user_row[LastName], $label["order_completed_renewal_email_template"]);
		$label["order_completed_renewal_email_template"] = str_replace ("%ORDER_ID%", $order_row[order_id], $label["order_completed_renewal_email_template"]);
		$label["order_completed_renewal_email_template"] = str_replace ("%ORIGINAL_ORDER_ID%", $order_row[original_order_id], $label["order_completed_renewal_email_template"]);
		
		$label["order_completed_renewal_email_template"] = str_replace ("%PIXEL_COUNT%", $order_row[quantity], $label["order_completed_renewal_email_template"]);
		$label["order_completed_renewal_email_template"] = str_replace ("%PIXEL_DAYS%", $order_row[days_expire], $label["order_completed_renewal_email_template"]);
		$label["order_completed_renewal_email_template"] = str_replace ("%PRICE%", convert_to_default_currency_formatted($order_row[currency], $order_row[price]), $label["order_completed_renewal_email_template"]);
		$label["order_completed_renewal_email_template"] = str_replace ("%SITE_CONTACT_EMAIL%", SITE_CONTACT_EMAIL, $label["order_completed_renewal_email_template"]);
		$label["order_completed_renewal_email_template"] = str_replace ("%SITE_URL%", BASE_HTTP_PATH, $label["order_completed_renewal_email_template"]);
		$message = $label["order_completed_renewal_email_template"];
		$to = trim($user_row['Email']);
		$subject = $label['order_completed_email_subject'];
		
	
		if (EMAIL_USER_ORDER_COMPLETED=='YES') {

			if (USE_SMTP=='YES') {
				$mail_id=queue_mail(addslashes($to), addslashes($user_row[FirstName]." ".$user_row[LastName]), addslashes(SITE_CONTACT_EMAIL), addslashes(SITE_NAME), addslashes($subject), addslashes($message), '', 8);
				process_mail_queue(2, $mail_id);
			} else {
				send_email( $to, $user_row[FirstName]." ".$user_row[LastName], SITE_CONTACT_EMAIL, SITE_NAME, $subject, $message, '', 1);
			}
			
		}

		// send a copy to admin

		if (EMAIL_ADMIN_ORDER_COMPLETED=='YES') {

			if (USE_SMTP=='YES') {
				$mail_id=queue_mail(addslashes(SITE_CONTACT_EMAIL), addslashes($user_row[FirstName]." ".$user_row[LastName]), addslashes(SITE_CONTACT_EMAIL), addslashes(SITE_NAME), addslashes($subject), addslashes($message), '', 8);
				process_mail_queue(2, $mail_id);
			} else {
				send_email( SITE_CONTACT_EMAIL, $user_row[FirstName]." ".$user_row[LastName], SITE_CONTACT_EMAIL, SITE_NAME, $subject, $message, '', 1);
			}
			
		}

		// process the grid, if auto_publish is on

		$b_row = load_banner_row($order_row['banner_id']);

		if ($b_row['auto_publish']=='Y') {
			process_image($order_row['banner_id']);
			publish_image($order_row['banner_id']);
			process_map($order_row['banner_id']);
		}

	}

}

#####################################################

function send_confirmation_email($email) {

	global $label;

	$sql = "SELECT * FROM users where Email='$email' ";
	$result = mysql_query($sql);
	$row = mysql_fetch_array($result);

	$code = substr(md5($row['Email'].$row['Password']),0, 8);

	$verify_url = BASE_HTTP_PATH."users/validate.php?email=".$row['Email']."&code=$code";

	
	$label["confirmation_email_templaltev2"] = str_replace ("%FNAME%", $row[FirstName], $label["confirmation_email_templaltev2"]);
	$label["confirmation_email_templaltev2"] = str_replace ("%LNAME%", $row[LastName], $label["confirmation_email_templaltev2"]);
	$label["confirmation_email_templaltev2"] = str_replace ("%SITE_URL%", BASE_HTTP_PATH."users/", $label["confirmation_email_templaltev2"]);
	$label["confirmation_email_templaltev2"] = str_replace ("%SITE_NAME%", SITE_NAME, $label["confirmation_email_templaltev2"]);
	$label["confirmation_email_templaltev2"] = str_replace ("%VERIFY_URL%", $verify_url, $label["confirmation_email_templaltev2"]);
	$label["confirmation_email_templaltev2"] = str_replace ("%VALIDATION_CODE%", $code, $label["confirmation_email_templaltev2"]);

	$message = $label["confirmation_email_templaltev2"];

	$html_msg = str_replace ("%FNAME%", $row[FirstName], $label["confirmation_html_email_templaltev2"]);
	$html_msg = str_replace ("%LNAME%", $row[LastName], $html_msg);
	$html_msg = str_replace ("%SITE_URL%", BASE_HTTP_PATH."users/", $html_msg);
	$html_msg = str_replace ("%SITE_NAME%", SITE_NAME, $html_msg);
	$html_msg = str_replace ("%VERIFY_URL%", $verify_url, $html_msg);
	$html_msg = str_replace ("%VALIDATION_CODE%", $code, $html_msg);


	$to = trim($row['Email']);

	$subject = $label['confirmation_email_subject'];
		
	if (USE_SMTP=='YES') {
		$mail_id = queue_mail(addslashes($to), addslashes($row[FirstName]." ".$row[LastName]), addslashes(SITE_CONTACT_EMAIL), addslashes(SITE_NAME), addslashes($subject), addslashes($message), addslashes($html_msg), 5);
		process_mail_queue(2, $mail_id);
	} else {
		send_email( $to, $row[FirstName]." ".$row[LastName], SITE_CONTACT_EMAIL, SITE_NAME, $subject, $message, $html_msg, 5);
	}

	if (EMAIL_ADMIN_ACTIVATION=='YES') {

		if (USE_SMTP=='YES') {
			$mail_id = queue_mail(addslashes(SITE_CONTACT_EMAIL), addslashes(SITE_NAME), addslashes(SITE_CONTACT_EMAIL), addslashes(SITE_NAME), addslashes($subject), addslashes($message), addslashes($html_msg), 5);
			process_mail_queue(2, $mail_id);
		} else {
			send_email( SITE_CONTACT_EMAIL, SITE_NAME, SITE_CONTACT_EMAIL, SITE_NAME, $subject, $message, $html_msg, 5);
		}


	}




	

}


########################################################

function send_published_pixels_notification($user_id, $BID) {

	global $label;

	$subject = $label['publish_pixels_email_subject'];
	$subject  = str_replace ("%SITE_NAME%", SITE_NAME, $subject );

	$sql = "SELECT * from banners where banner_id='$BID'";
	$result = mysql_query($sql) or die (mysql_error());
	$b_row = mysql_fetch_array($result);

	$sql = "SELECT * from users where ID='$user_id'";
	$result = mysql_query($sql) or die (mysql_error());
	$u_row = mysql_fetch_array($result);

	$sql = "SELECT  url, alt_text FROM blocks where user_id='$user_id' AND banner_id='$BID' GROUP by url ";
	$result = mysql_query($sql) or die (mysql_error());
	while ($row = mysql_fetch_array($result)) {

		$url_list .= $row['url']." - ".$row['alt_text']."\n";

	}

	
	$arr = explode ("/",SERVER_PATH_TO_ADMIN);
	$admin_folder = array_pop($arr);
	$admin_folder = array_pop($arr);

	$view_url = BASE_HTTP_PATH.$admin_folder."/remote_admin.php?key=".substr(md5(ADMIN_PASSWORD),1,15)."&user_id=$user_id&BID=$BID";

	$msg = str_replace ("%SITE_NAME%", SITE_NAME, $label['publish_pixels_email_template']);
	$msg = str_replace ("%GRID_NAME%", $b_row['name'], $msg);
	$msg = str_replace ("%MEMBERID%", $u_row['Username'], $msg);
	$msg = str_replace ("%URL_LIST%", $url_list, $msg);
	$msg = str_replace ("%VIEW_URL%", $view_url, $msg);

	

	$html_msg = str_replace ("%SITE_NAME%", SITE_NAME, $label['publish_pixels_html_email_template']);
	$html_msg = str_replace ("%GRID_NAME%", $b_row['name'], $html_msg);
	$html_msg = str_replace ("%MEMBERID%", $u_row['Username'], $html_msg);
	$html_msg = str_replace ("%URL_LIST%", $url_list, $html_msg);
	$html_msg = str_replace ("%VIEW_URL%", $view_url, $html_msg);

	if (USE_SMTP=='YES') {
		$mail_id = queue_mail(addslashes(SITE_CONTACT_EMAIL), addslashes('Admin'), addslashes(SITE_CONTACT_EMAIL), addslashes(SITE_NAME), addslashes($subject), addslashes($msg), addslashes($html_msg), 7);
		process_mail_queue(2, $mail_id);
	} else {
		send_email( SITE_CONTACT_EMAIL, 'Admin', SITE_CONTACT_EMAIL, SITE_NAME, $subject, $msg, $html_msg, 7);
	}




}

#########################################################

function send_expiry_reminder($order_id) {



}

#########################
function display_order ($order_id, $BID) {

	global $label;
	$order_id = addslashes($order_id);
	$sql = "select * from banners where banner_id='$BID'";
	$result = mysql_query ($sql) or die (mysql_error().$sql);
	$b_row = mysql_fetch_array($result);

	if (is_numeric($order_id)) {
		$sql = "SELECT * from orders where order_id='".$order_id."' and banner_id='$BID'";
	} else {
		$sql = "SELECT * from temp_orders where session_id='".$order_id."' and banner_id='$BID'";
	}
	
	$result = mysql_query($sql) or die(mysql_error().$sql);
	$order_row = mysql_fetch_array($result);

?>

<table border="1" width="300">
<?php if ($order_row['order_id']!='') { ?>
<tr>
<td><b><?php echo $label['advertiser_ord_order_id'];?></b></td><td><?php echo $order_row['order_id'];?></td>
</tr>
<?php } ?>
<tr>
<td><b><?php echo $label['advertiser_ord_date'];?></b></td><td><?php echo $order_row[order_date]; ?></td>
</tr>
<tr>
<td><b><?php echo $label['advertiser_ord_name']; ?></b></td><td><?php echo $b_row[name]; ?></td>
</tr>
<tr>
<td><b><?php echo $label['advertiser_ord_quantity'];?></b></td><td><?php echo $order_row[quantity]; ?> <?php echo $label['advertiser_ord_pix'];?></td>
</tr>
<td><b><?php echo $label['advertiser_ord_expired']; ?></b></td><td><?php if ($order_row[days_expire]==0) { echo $label['advertiser_ord_never']; } else { 

	$label['advertiser_ord_days_exp'] = str_replace ("%DAYS_EXPIRE%", $order_row[days_expire], $label['advertiser_ord_days_exp']);
	echo $label['advertiser_ord_days_exp'];
		
} ?></td>
</tr>
<tr>
<td><b><?php echo $label['advertiser_ord_price']; ?></b></td><td><?php echo convert_to_default_currency_formatted($order_row[currency], $order_row[price])?></td>
</tr>
<?php if ($order_row['order_id']!='') { ?>
<tr>
<td><b><?php echo $label['advertiser_ord_status']; ?></b></td><td><?php echo $order_row[status];?></td>
</tr>
<?php } ?>
</table>
<?php
}

############################
# Contributed by viday
function display_packages ($order_id, $BID) {

	global $label;

	$sql = "select * from banners where banner_id='$BID'";
	$result = mysql_query ($sql) or die (mysql_error().$sql);
	$b_row = mysql_fetch_array($result);

	$sql = "SELECT * from orders where order_id='".$_SESSION['MDS_order_id']."' and banner_id='$BID'";
	$result = mysql_query($sql) or die(mysql_error().$sql);
	$order_row = mysql_fetch_array($result);

?>

Please choose the duration of the campaign you desire:<p>
<table border="1" width="300">
<tr>
<td><b><?php echo $label['advertiser_ord_order_id'];?></b></td><td><?php echo $order_row['order_id'];?></td>
</tr>
<tr>
<td><b><?php echo $label['advertiser_ord_date'];?></b></td><td><?php echo $order_row[order_date]; ?></td>
</tr>
<tr>
<td><b><?php echo $label['advertiser_ord_name']; ?></b></td><td><?php echo $b_row[name]; ?></td>
</tr>
<tr>
<td><b><?php echo $label['advertiser_ord_quantity'];?></b></td><td><?php echo $order_row[quantity]; ?> <?php echo $label['advertiser_ord_pix'];?></td>
</tr>
<td><b>Duration/Price</b></td><td><?php if ($b_row[days_expire]==0) { echo $label['advertiser_ord_never']; } else { 
// viday pricing dropdown
	?> <select name="packages"> <?php
        $sql = "SELECT * from packages where banner_id='$BID' order by price asc";
        $result = mysql_query($sql) or die(mysql_error().$sql);
         while ($packages_row=mysql_fetch_array($result)) {
		echo "<option value=\"".$packages_row['days_expire']."-".$packages_row['price']."\">".$packages_row['days_expire']." days - $".$packages_row['price']."</option>";
	}	
	echo "</select>";	
	$get_blocks = split(",",$order_row['blocks']);
	$num_blocks = count($get_blocks);
	echo "<br>Prices are per block, you have chosen ".$num_blocks;
	if ($num_blocks == "1") { echo " block."; } else {
	 echo " blocks. "; }
	echo " Your total price will be calculated on the next screen.";
	echo "<input type=\"hidden\" name=\"num_blocks\" value=\"".$num_blocks."\">";
} ?></td>
</tr>
<tr>
<td><b><?php echo $label['advertiser_ord_status']; ?></b></td><td><?php echo $order_row[status];?></td>
</tr>
</table>
<?php
}
####################################################

function display_banner_selecton_form($BID, $order_id, $res) {

	$action = $_SERVER['PHP_SELF'];

	
	$action = array_pop($a = explode('?', $action)); // strip parameters
	
	
	?>
<form name="bidselect" method="post" action="<?php echo htmlentities($action); ?>" >
<input type="hidden" name="old_order_id" value="<?php echo $order_id;?>">
<input type="hidden" name="banner_change" value="1">
<select name="BID" onchange="document.bidselect.submit()" style="font-size: 14px;">
	<?php
	while ($row=mysql_fetch_array($res)) {
		if ($row['banner_id']==$BID) {
			$sel = 'selected';
		} else {
			$sel ='';

		}
		echo '<option '.$sel.' value='.$row['banner_id'].'>'.$row['name'].'</option>';
	}
	?>
</select>
</form>
<?php
}


#######################################################

function escape_html ($val) {

	$val = str_replace('>', '&gt;',$val);
	$val = str_replace('<', '&lt;',$val);
	$val = str_replace('"', '&quot;',$val);
	//$val = str_replace("'", '&#39;',$val);
	
// echo "$val<br>";
	return $val;

}



####################################################


function html_ent_to_utf8($text) {
		global $context, $local;

		// translate extended ISO8859-1 chars, if any
		$text = utf8_encode($text);

		// translate Unicode entities
	 	$areas = preg_split('/&[#u](\d+?);/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
		$text = '';
		$index = 0;
		foreach($areas as $area) {
			switch($index%2) {
			case 0: // before entity
				$text .= $area;
				break;
			case 1: // the entity itself

				// get the integer value
				$unicode = intval($area);

				// one byte
				if($unicode < 0x7F) {

					$text .= chr($unicode);

				// forbidden elements
				} elseif($unicode < 0x0A0) {
					;

				// two bytes
				} elseif($unicode < 0x800) {

					$text .= chr( 0xC0 +  ( ( $unicode - ( $unicode % 0x40 ) ) / 0x40 ) );
					$text .= chr( 0x80 + ( $unicode % 0x40 ) );

				// three bytes
				} elseif($unicode < 0x10000) {

					$text .= chr( 0xE0 + ( ( $unicode - ( $unicode % 0x1000 ) ) / 0x1000 ) );
					$text .= chr( 0x80 + ( ( ( $unicode % 0x1000 ) - ( $unicode % 0x40 ) ) / 0x40 ) );
					$text .= chr( 0x80 + ( $unicode % 0x40 ) );

				// more bytes, keep it as it is...
	 			} else
	 				$text .= '&#'.$unicode.';';

				break;
			}
			$index++;
		}

		// the updated string
		return $text;
	}

####################################################
function send_email( $to_address, $to_name, $from_address, $from_name, $subject, $message, 
$html_message='', $template_id=0) {

	if (strpos(strtolower($to_address), strtolower('Content-type'))> 0) { // detect mail() injection
		return false;
	}

	if (strpos(strtolower($to_name), strtolower('Content-type'))> 0) { // detect mail injection
		return false;
	}

	if (strpos(strtolower($from_address), strtolower('Content-type'))> 0) { // detect mail injection
		return false;
	}

	if (strpos(strtolower($from_name), strtolower('Content-type'))> 0) { // detect mail injection
		return false;
	}

	if (strpos(strtolower($subject), strtolower('Content-type'))> 0) { // detect mail injection
		return false;
	}

	if (strpos(strtolower($message), strtolower('Content-type'))> 0) { // detect mail injection
		return false;
	}

	// save to the database...

	$attachments='N';
	
	$now = (gmdate("Y-m-d H:i:s"));

	$sql = "INSERT INTO mail_queue (mail_id, mail_date, to_address, to_name, from_address, from_name, subject, message, html_message, attachments, status, error_msg, retry_count, template_id, date_stamp) VALUES('', '$now', '".addslashes($to_address)."', '".addslashes($to_name)."', '".addslashes($from_address)."', '".addslashes($from_name)."', '".addslashes($subject)."', '".addslashes($message)."', '".addslashes($html_message)."', '$attachments', 'sent', '', 0, '$template_id', '$now')"; $s='copyr1ght two thousand & 6 Jam1t softwar3 ';

	mysql_query ($sql) or q_mail_error (mysql_error().$sql);

	$mail_id = mysql_insert_id();



	// -J- : try to comment out the following statements 
	// also change the charset=UTF-8 to charset=US-ASCII and let me know if it worked!
	$to_name = html_ent_to_utf8($to_name);
	$from_name = html_ent_to_utf8($from_name);
	$subject = html_ent_to_utf8($subject);
	$message = html_ent_to_utf8($message);
	$html_message = html_ent_to_utf8($html_message);

	//@ini_set(sendmail_from, SITE_CONTACT_EMAIL);
	//@ini_set(sendmail_path, "/usr/sbin/sendmail -t -f ".SITE_CONTACT_EMAIL);

  $headers ="Return-Path: ".SITE_CONTACT_EMAIL."\r\n";
  $headers .= "From: ".SITE_NAME." <".SITE_CONTACT_EMAIL.">\n";
  $headers .= "MIME-Version: 1.0\n";
  $headers .= "Content-Type: text/plain; charset=UTF-8\r\n"; 
  

	return mail($to_address, $subject, $message, $headers);
	

}



##################################################

function move_uploaded_image ($img_key) {

	$img_name = $_FILES[$img_key]['name'];

	$temp= explode('.', $img_name);
	$ext = array_pop($temp);
	$fname = array_pop($temp);
	
	$img_name = preg_replace ('/[^\w]+/', "", $fname);
	$img_name = $img_name.".".$ext;

	$img_tmp = $_FILES[$img_key]['tmp_name'];

	$t = time();
	
//echo "$img_name tmpimg: ".$img_tmp;
	$new_name = SERVER_PATH_TO_ADMIN."temp/".$t."$img_name";

	move_uploaded_file ($img_tmp, $new_name);
	chmod($new_name, 0666);
	

	return $new_name;


}
# c o p y r i g h t - J a m i t S o f t w a r e - 2 0 0 6
######################################

function nav_pages_struct(&$result, $q_string, $count, $REC_PER_PAGE) {
 
	global $label;
	global $list_mode;
	
	if ($list_mode=='PREMIUM') {
		$page = 'hot.php';		
	} else {
		$page = $_SERVER[PHP_SELF];
	}
	$offset = $_REQUEST["offset"];
	$show_emp = $_REQUEST["show_emp"];
	
	if ($show_emp != '') {
	  $show_emp = ("&show_emp=$show_emp");
	}
	$cat = $_REQUEST["cat"];
	if ($cat != '') {
	  $cat = ("&cat=$cat");
	}
	$order_by = $_REQUEST["order_by"];
	if ($order_by != '') {
	  $order_by = ("&order_by=$order_by");
	}

	$cur_page = $offset / $REC_PER_PAGE;
	$cur_page++;
	// estimate number of pages.
	$pages = ceil($count / $REC_PER_PAGE);
	if ($pages == 1) {
	   return;
	}
	$off = 0;
	$p=1;
	$prev = $offset-$REC_PER_PAGE;
	$next = $offset+$REC_PER_PAGE;

	if ($prev===0) {
		$prev='';
	}

	if ($prev > -1) {
	    $nav['prev'] =  "<a  href='".$page."?offset=".$prev.$q_string.$show_emp.$cat.$order_by."'>".$label["navigation_prev"] ."</a> ";
	  
	}
	for ($i=0; $i < $count; $i=$i+$REC_PER_PAGE) {
	  if ($p == $cur_page) {
		 $nav['cur_page'] = $p;
		 
		 
	  } else {
		  if ($off===0) {
			$off='';
		}
		 if ($nav['cur_page'] !='') {
			 $nav['pages_after'][$p] = $off;
		 } else {
			$nav['pages_before'][$p] = $off;
		 }
	  }
	  $p++;
	  $off = $off + $REC_PER_PAGE;
	}
	if ($next < $count ) 
		$nav['next'] = " | <a  href='".$page."?offset=".$next.$q_string.$show_emp.$cat.$order_by."'> ".$label["navigation_next"]."</a>";

	return $nav;
}

#####################################################

function render_nav_pages (&$nav_pages_struct, $LINKS, $q_string='') {

	global $list_mode;
	global $label;

	if ($list_mode=='PREMIUM') {
		$page = 'hot.php';
		echo $label['post_list_more_sponsored']." ";
	} else {
		$page = $_SERVER[PHP_SELF];
	}

	$offset = $_REQUEST["offset"];
	$show_emp = $_REQUEST["show_emp"];
	
	if ($show_emp != '') {
	  $show_emp = ("&show_emp=$show_emp");
	}
	$cat = $_REQUEST["cat"];
	if ($cat != '') {
	  $cat = ("&cat=$cat");
	}
	$order_by = $_REQUEST["order_by"];
	if ($order_by != '') {
	  $order_by = ("&order_by=$order_by");
	}

	if ($nav_pages_struct['cur_page'] > $LINKS-1) {
		$LINKS = round ($LINKS / 2)*2;
		$NLINKS = $LINKS;
	} else {
		$NLINKS = $LINKS - $nav_pages_struct['cur_page']; 
	}
	echo $nav_pages_struct['prev'];
	$b_count = count($nav_pages_struct['pages_before']);
	for ($i = $b_count-$LINKS; $i <= $b_count; $i++) {
		if ($i>0) {
			//echo " <a href='?offset=".$nav['pages_before'][$i]."'>".$i."</a></b>";
			echo " | <a  href='".$page."?offset=".$nav_pages_struct['pages_before'][$i]."$q_string$show_emp$cat$order_by'>".$i."</a>";
			$pipe = "|";
		}
	}
	echo " $pipe <b>".$nav_pages_struct['cur_page']." </b>  ";
	if (count($nav_pages_struct['pages_after'])>0) { 
		$i=0;
		foreach ($nav_pages_struct['pages_after'] as $key => $pa ) {
			$i++;
			if ($i > $NLINKS) {
				break;
			}
			//echo " <a href='?offset=".$pa."'>".$key."</a>";
			echo " | <a  href='".$page."?offset=".$pa."$q_string$show_emp$cat$order_by'>".$key."</a>  ";
		}
	}

	echo $nav_pages_struct['next'];


}

function do_log_entry ($entry_line) {

	$entry_line =  "$entry_line\r\n "; 
		$log_fp = @fopen("logs.txt", "a"); 
		@fputs($log_fp, $entry_line); 
		@fclose($log_fp);



}


#####################################################

// assuming banner constants were loaded

function select_block ($map_x, $map_y) {

	global $BID;
	global $b_row;
	global $label;

	global $order_id;

	// calculate clicked block from co-ords.

	if (func_num_args()>2) {

		$clicked_block = func_get_arg(2);

	} else {

		$map_x = floor ($map_x / BLK_WIDTH)*BLK_WIDTH; // got to floor it to get the top-right corner of the block
		$map_y = floor ($map_y / BLK_HEIGHT)*BLK_HEIGHT;
		//$clicked_block = (($map_y*$b_row['grid_width'])+$map_x)/10 ;
		$GRD_WIDTH = BLK_WIDTH * G_WIDTH;
		$clicked_block = (($map_x) / BLK_WIDTH) + (($map_y/BLK_HEIGHT) * ($GRD_WIDTH / BLK_WIDTH)) ;
	}

	if ($clicked_block==0) {
		$clicked_block="0";// convert to string
	}

	$sql = "select Rank from users where ID=".$_SESSION['MDS_ID'];
	$result = mysql_query ($sql) or die (mysql_error().$sql);
	$u_row = mysql_fetch_array($result);

	//Check if max_orders < order count
	if (!can_user_order($b_row, $_SESSION['MDS_ID'])) {
		return $label['advertiser_max_order_html']; // order count > max orders
	}

	if (!function_exists('delete_ads_files')) {
		require_once ("../include/ads.inc.php");
	}
	
	# check the status of the block.


	###################################################
	if (USE_LOCK_TABLES == 'Y') {
		$sql = "LOCK TABLES blocks WRITE, orders WRITE, ads WRITE, form_fields READ, currencies READ, prices READ, banners READ, packages READ";
		$result = mysql_query ($sql) or die (" <b>Dear Webmaster: The current MySQL user does not have permission to lock tables. Please give this user permission to lock tables, or turn off locking in the Admin. To turn off locking in the Admin, please go to Main Config and look under the MySQL Settings.<b>");
	} else {
		// poor man's lock
		$sql = "UPDATE `config` SET `val`='YES' WHERE `key`='SELECT_RUNNING' AND `val`='NO' ";
		$result = mysql_query($sql) or die(mysql_error());
		if (mysql_affected_rows()==0) {
			// make sure it cannot be locked for more than 30 secs 
			// This is in case the proccess fails inside the lock
			// and does not release it.

			$unix_time = time();

			// get the time of last run
			$sql = "SELECT * FROM `config` where `key` = 'LAST_SELECT_RUN' ";
			$result = @mysql_query($sql);
			$t_row = @mysql_fetch_array($result);

			if ($unix_time > $t_row['val']+30) {
				// release the lock
				
				$sql = "UPDATE `config` SET `val`='NO' WHERE `key`='SELECT_RUNNING' ";
				$result = @mysql_query($sql) or die(mysql_error());

				// update timestamp
				$sql = "REPLACE INTO config (`key`, `val`) VALUES ('LAST_SELECT_RUN', '$unix_time')  ";
				$result = @mysql_query($sql) or die (mysql_error());
			}
			
			usleep(5000000); // this function is executing in another process. sleep for half a second
			select_block ($map_x, $map_y, $clicked_block); 
		}


	}
	####################################################

	//$sql = "SELECT status, user_id FROM blocks where `x`=$map_x AND `y`=$map_y and banner_id=$BID ";
	
	$sql = "SELECT status, user_id, ad_id FROM blocks where block_id='$clicked_block' AND banner_id='$BID' ";
	$result = mysql_query ($sql) or die (mysql_error());
	$row = mysql_fetch_array($result);


	
	if (($row['status']=='') || (($row['status']=='reserved')&& ($row['user_id']==$_SESSION['MDS_ID']))) {

	
		// put block on order
		$sql = "SELECT * FROM orders where user_id='".$_SESSION['MDS_ID']."' and status='new' and banner_id='$BID' ";
		$result = mysql_query ($sql) or die (mysql_error());
		$row = mysql_fetch_array($result);
		if ($row['blocks']!='') {
			$blocks = explode ( ",", $row['blocks']);
			
		} else {
			$blocks = array ();
		}
		$new_blocks = array ();
	 // remove selected block 
		for ($i=0; $i <sizeof($blocks); $i++) {
			if (strcmp($blocks[$i], $clicked_block)!=0) {
				$new_blocks[] = "".$blocks[$i];
			} else {
				//clicked for 2nd time;
				$double_clicked = true;
			}
				
		}

		if (!$double_clicked) { # add newly selected block

			
			$new_blocks[] = "$clicked_block";
			//echo "not-double<br>";

		}

		// check max blocks
		if (USE_AJAX=='NO') {
			if (G_MAX_BLOCKS>0) {
				if (sizeof($new_blocks)>G_MAX_BLOCKS) {
					$max_selected = true;
					$cannot_sel = "<font color=red><b>".str_replace('%MAX_BLOCKS%', G_MAX_BLOCKS, $label['max_blocks_selected'])."</b></font>";	
				}
			}
		}

		if (!$max_selected) {

		
			$blocks = $new_blocks;
			$quantity = sizeof($blocks)*(BLK_WIDTH*BLK_HEIGHT);
			//$row['blocks']=implode(",",$blocks);
			$blocks = implode (",", $blocks); // change to string
			$now = (gmdate("Y-m-d H:i:s")); 

			$sql = "REPLACE INTO orders (user_id, order_id, blocks, status, order_date, price, quantity, banner_id, currency, days_expire, date_stamp, approved) VALUES ('".$_SESSION['MDS_ID']."', '".$row['order_id']."', '".$blocks."', 'new', NOW(), '".$price."', '".$quantity."', '".$BID."', '".get_default_currency()."', ".$b_row['days_expire'].", '$now', '".AUTO_APPROVE."') ";
		
			$result = mysql_query ($sql) or die (mysql_error().$sql);
			$_SESSION['MDS_order_id'] = mysql_insert_id();
			$order_id = $_SESSION['MDS_order_id'];

			$sql = "delete from blocks where user_id='".$_SESSION['MDS_ID']."' AND status = 'reserved' AND banner_id='$BID' ";
			mysql_query($sql) or die (mysql_error().$sql);
			

			$cell="0";			

			for ($i=0; $i < $b_row['grid_height']; $i++) {
				for ($j=0; $j < $b_row['grid_width']; $j++) {

					if (in_array($cell, $new_blocks)) {

						$price = get_zone_price($BID, $i, $j);

						$currency = get_default_currency();

						$sql = "REPLACE INTO `blocks` ( `block_id` , `user_id` , `status` , `x` , `y` , `image_data` , `url` , `alt_text`, `approved`, `banner_id`, `currency`, `price`, `order_id`) VALUES ('$cell',  '".$_SESSION['MDS_ID']."' , 'reserved' , '".($j*BLK_WIDTH)."' , '".($i*BLK_HEIGHT)."' , '' , '' , '', '".AUTO_APPROVE."', '".$BID."', '".get_default_currency()."', '".$price."', '".$_SESSION['MDS_order_id']."')";

						$total += $price;
					
						mysql_query ($sql) or die (mysql_error().$sql);

					}
					$cell++;
				}
			}

			// update price


			$sql = "UPDATE orders SET price='$total' WHERE order_id='".$_SESSION['MDS_order_id']."'";
			mysql_query ($sql) or die (mysql_error().$sql);

			
			$sql = "UPDATE orders SET original_order_id='".$_SESSION['MDS_order_id']."' WHERE order_id='".$_SESSION['MDS_order_id']."'";
			mysql_query ($sql) or die (mysql_error().$sql);

			// check that we have ad_id, if not then create an ad for this order.

			if (!$row['ad_id']) {

				

				$_REQUEST[$ad_tag_to_field_id['URL']['field_id']]='http://';
				$_REQUEST[$ad_tag_to_field_id['ALT_TEXT']['field_id']] = 'ad text';
				$_REQUEST['order_id'] = $_SESSION['MDS_order_id'];
				$_REQUEST['banner_id'] = $BID;
				$_REQUEST['user_id'] = $_SESSION['MDS_ID'];

				$ad_id = insert_ad_data();

				$sql = "UPDATE orders SET ad_id='$ad_id' WHERE order_id='".$_SESSION['MDS_order_id']."' ";
				$result = mysql_query ($sql) or die (mysql_error());
				$sql = "UPDATE blocks SET ad_id='$ad_id' WHERE order_id='".$_SESSION['MDS_order_id']."' ";
				$result = mysql_query ($sql) or die (mysql_error());

				$_REQUEST['ad_id'] = $ad_id;


			}

			###################################################
			
			if (USE_LOCK_TABLES == 'Y') {
				$sql = "UNLOCK TABLES";
				$result = mysql_query ($sql) or die (mysql_error()." <b>Dear Webmaster: The current MySQL user set in config.php does not have permission to lock tables. Please give this user permission to lock tables, or set USE_LOCK_TABLES to N in the config.php file that comes with this script.<b>");
			} else {

				// release the poor man's lock
				$sql = "UPDATE `config` SET `val`='NO' WHERE `key`='SELECT_RUNNING' ";
				mysql_query($sql);

				$unix_time = time();

				// update timestamp
				$sql = "REPLACE INTO config (`key`, `val`) VALUES ('LAST_SELECT_RUN', '$unix_time')  ";
				$result = @mysql_query($sql) or die (mysql_error());


			}
			####################################################

		}

	} else {

		if ($row['status']=='nfs') {

			$cannot_sel =  "<font color=red><b>".$label['advertiser_sel_nfs_error']."</b></font>";

		} else {
			$label['advertiser_sel_sold_error'] = str_replace("%BLOCK_ID%", $clicked_block, $label['advertiser_sel_sold_error']); 
			$cannot_sel =  "<font color=red><b>".$label['advertiser_sel_sold_error']."</b></font><br>";
		}
	}
	return $cannot_sel;

}

################

/*

The new 'Easy' pixel selection method (since 2.0)
- Reserve pixels
Takes the temp_order and converts it to an order.
Allocates pixels in the blocks tabe, returning order_id
shows an error if pixels were not reserved.

*/



function reserve_pixels_for_temp_order($temp_order_row) {

	// check if the user can get the order
	if (!can_user_order(load_banner_row($temp_order_row['banner_id']), $_SESSION['MDS_ID'], $temp_order_row['package_id'])) {
		echo 'can\'t touch this<br>';
		return false;

	}

	require_once ('../include/ads.inc.php');

	###################################################
	if (USE_LOCK_TABLES == 'Y') {
		$sql = "LOCK TABLES blocks WRITE, orders WRITE, ads WRITE, temp_orders WRITE,  currencies READ, prices READ, banners READ, form_fields READ, form_field_translations READ";
		$result = mysql_query ($sql) or die (" <b>Dear Webmaster: The current MySQL user does not have permission to lock tables. Please give this user permission to lock tables, or turn off locking in the Admin. To turn off locking in the Admin, please go to Main Config and look under the MySQL Settings.<b>");
	} else {
		// poor man's lock
		$sql = "UPDATE `config` SET `val`='YES' WHERE `key`='SELECT_RUNNING' AND `val`='NO' ";
		$result = mysql_query($sql) or die(mysql_error());
		if (mysql_affected_rows()==0) {
			// make sure it cannot be locked for more than 30 secs 
			// This is in case the proccess fails inside the lock
			// and does not release it.

			$unix_time = time();

			// get the time of last run
			$sql = "SELECT * FROM `config` where `key` = 'LAST_SELECT_RUN' ";
			$result = @mysql_query($sql);
			$t_row = @mysql_fetch_array($result);

			if ($unix_time > $t_row['val']+30) {
				// release the lock
				
				$sql = "UPDATE `config` SET `val`='NO' WHERE `key`='SELECT_RUNNING' ";
				$result = @mysql_query($sql) or die(mysql_error());

				// update timestamp
				$sql = "REPLACE INTO config (`key`, `val`) VALUES ('LAST_SELECT_RUN', '$unix_time')  ";
				$result = @mysql_query($sql) or die (mysql_error());
			}
			
			usleep(5000000); // this function is executing in another process. sleep for half a second
			reserve_pixels_for_temp_order($temp_order_row); 
			return;
		}


	}
	####################################################

	$filename = SERVER_PATH_TO_ADMIN.'temp/'."info_".md5(session_id()).".txt";
	$fh = fopen ($filename, 'rb');
	$block_info = fread($fh, filesize($filename));
	fclose($fh);

	//$block_info = unserialize($temp_order_row['block_info']);
	$block_info = unserialize($block_info);
	//echo "block info:";
	//print_r($block_info);

	$in_str = $temp_order_row['blocks'];

	$sql = "select block_id from blocks where banner_id='".$temp_order_row['banner_id']."' and block_id IN(".$in_str.") ";
//echo $sql."<br>";
	$result = mysql_query($sql) or die ($sql.mysql_error()); 
	if (mysql_num_rows($result)>0) {
		return false;  // the pixels are not available!
	}

	// approval status, default is N
	$banner_row = load_banner_row($temp_order_row['banner_id']);
	$approved = $banner_row['auto_approve'];

	$now = (gmdate("Y-m-d H:i:s")); 

	$sql = "REPLACE INTO orders (user_id, order_id, blocks, status, order_date, price, quantity, banner_id, currency, days_expire, date_stamp, package_id, ad_id, approved) VALUES ('".$_SESSION['MDS_ID']."', '', '".$in_str."', 'new', '".$now."', '".$temp_order_row['price']."', '".$temp_order_row['quantity']."', '".$temp_order_row['banner_id']."', '".get_default_currency()."', ".$temp_order_row['days_expire'].", '".$now."', ".$temp_order_row['package_id'].", ".$temp_order_row['ad_id'].", '".$approved."') ";
		
	$result = mysql_query ($sql) or die (mysql_error().$sql);
	$order_id = mysql_insert_id(); 
	mds_log("Changed temp order to a real order - ".$sql);
//echo "<hr>";echo $sql; echo "<hr>";
	
	$sql = "UPDATE ads SET user_id='".$_SESSION['MDS_ID']."', order_id='".$order_id."' where ad_id='".$temp_order_row['ad_id']."' ";
	//echo $sql;
	mysql_query ($sql) or die (mysql_error().$sql);

	$sql = "UPDATE orders SET original_order_id='".$order_id."' where order_id='".$order_id."' ";
	//echo $sql;
	mysql_query ($sql) or die (mysql_error().$sql);

	
	global $prams;
	$prams = load_ad_values ($temp_order_row['ad_id']);
	$url = get_template_value('URL', 1);
	$alt_text = get_template_value('ALT_TEXT', 1);
//print_R($block_info);
//echo "<P>url: $url, alt_text: $alt_text </p>";
	
	foreach ($block_info as $key=>$block) {

		$sql = "REPLACE INTO `blocks` ( `block_id` , `user_id` , `status` , `x` , `y` , `image_data` , `url` , `alt_text`, `approved`, `banner_id`, `currency`, `price`, `order_id`, `ad_id`) VALUES ('".$key."',  '".$_SESSION['MDS_ID']."' , 'reserved' , '".($block['map_x'])."' , '".($block['map_y'])."' , '".$block['image_data']."' , '".addslashes($url)."' , '".addslashes($alt_text)."', '".$approved."', '".$temp_order_row['banner_id']."', '".get_default_currency()."', '".$block['price']."', '".$order_id."', '".$temp_order_row['ad_id']."')";
//echo $sql."<br>";
		mds_log("Updated block - ".$sql);
		mysql_query ($sql) or die (mysql_error().$sql);


	}

	delete_temp_order(session_id(), false); // false = do not delete the ad...

	###################################################
			
	if (USE_LOCK_TABLES == 'Y') {
		$sql = "UNLOCK TABLES";
		$result = mysql_query ($sql) or die (mysql_error()." <b>Dear Webmaster: The current MySQL user set in config.php does not have permission to lock tables. Please give this user permission to lock tables, or set USE_LOCK_TABLES to 'No' in the Main Config section in the Admin.<b>");
	} else {

		// release the poor man's lock
		$sql = "UPDATE `config` SET `val`='NO' WHERE `key`='SELECT_RUNNING' ";
		mysql_query($sql);

		$unix_time = time();

		// update timestamp
		$sql = "REPLACE INTO config (`key`, `val`) VALUES ('LAST_SELECT_RUN', '$unix_time')  ";
		$result = @mysql_query($sql) or die (mysql_error());


	}
	####################################################

	return $order_id;


}

################

function get_block_position($block_id) {

	$cell="0";
	$ret['x']=0;
	$ret['y']=0;

	for ($i=0; $i < G_HEIGHT; $i++) {
		for ($j=0; $j < G_WIDTH; $j++) {
			if ($block_id == $cell) {
				$ret['x']=$j*BLK_WIDTH;
				$ret['y']=$i*BLK_HEIGHT;
				return $ret;

			}
			$cell++;
		}

	}


}

########################

function is_block_free($block_id, $banner_id) {

	$sql = "SELECT * from blocks where block_id='$block_id' AND banner_id='$banner_id' ";
	//echo "$sql<br>";
	$result = mysql_query($sql) or die(mysql_error());
	if (mysql_num_rows($result)==0) {

		return true;

	} else {

		return false;

	}

}

######################################################
# Move 1 block
# - changes the x y of a block
# - updates the order's blocks column
# *** assuming that the grid constants were loaded!

function move_block($block_from, $block_to, $banner_id) {

	# reserve block_to
	if (!is_block_free($block_to, $banner_id)) {
		echo "<font color='red'>Cannot move the block - the space chosen is not empty!</font><br>";
		return false;
	}


	#load block_from
	$sql = "SELECT * from blocks where block_id='$block_from' AND banner_id='$banner_id' ";
	//echo "$sql<br>";
	$result = mysql_query($sql) or die(mysql_error());
	$source_block = mysql_fetch_array($result);


	// get the position and check range, do not move if out of range

	$pos = get_block_position($block_to);
	//echo "pos is ($block_to): ";print_r($pos); echo "<br>";
	
	$x = $pos['x'];
	$y = $pos['y'];

	if (($x==='') || ($x > (G_WIDTH*BLK_WIDTH)) || $x < 0) {
		echo "<b>x is $x</b><br>";
		return false;

	}

	if (($y==='') || ($y > (G_HEIGHT*BLK_HEIGHT)) || $y < 0) {
		echo "<b>y is $y</b><br>";
		return false;
	}

	
	$sql = "REPLACE INTO `blocks` ( `block_id` , `user_id` , `status` , `x` , `y` , `image_data` , `url` , `alt_text`, `file_name`, `mime_type`,  `approved`, `published`, `banner_id`, `currency`, `price`, `order_id`, `click_count`, `ad_id`) VALUES ('$block_to',  '".$source_block['user_id']."' , '".$source_block['status']."' , '".$x."' , '".$y."' , '".$source_block['image_data']."' , '".addslashes($source_block['url'])."' , '".addslashes($source_block['alt_text'])."', '".$source_block['file_name']."', '".$source_block['mime_type']."', '".$source_block['approved']."', '".$source_block['published']."', '".$banner_id."', '".$source_block['currency']."', '".$source_block['price']."', '".$source_block['order_id']."', '".$source_block['click_count']."', '".$source_block['ad_id']."')";

//echo "<p>$sql</p>";

	mds_log("Moved Block - ".$sql);

	mysql_query ($sql) or die(mysql_error());

	# delete 'from' block

	$sql = "DELETE from blocks WHERE block_id='".$block_from."' AND banner_id='".$banner_id."' ";
//echo "<p>$sql</p>";
	mysql_query ($sql) or die(mysql_error());
	mds_log("Deleted block_from - ".$sql);

	// Update the order record

	$sql = "SELECT * from orders WHERE order_id='".$source_block['order_id']."' AND banner_id='$banner_id' ";
	//echo "$sql<br>";
	$result = mysql_query($sql) or die(mysql_error());
	$order_row = mysql_fetch_array($result);
	$blocks = array();
	$new_blocks = array();
	$blocks = explode(',',$order_row['blocks']);
	//print_r($blocks);
	foreach ($blocks as $item) {
		//echo "<b>$item - block from: $block_from</b><br>";
		if ($block_from == $item) {
			$item = $block_to;//echo '<b>found!</b>';
		}
		$new_blocks[] = $item; 
	}

	$sql = "UPDATE orders set blocks='".implode(',', $new_blocks)."' WHERE order_id='".$source_block['order_id']."' ";
	# update the customer's order
	mysql_query($sql) or die(mysql_error());
	mds_log("Updated order - ".$sql);

	return true;


}

###################################



######################################################

function move_order($block_from, $block_to, $banner_id) {

	//move_block($block_from, $block_to, $banner_id);

	// get the block_to x,y
	$pos = get_block_position($block_to);
	$to_x = $pos['x'];
	$to_y = $pos['y'];


// we need to work out block_from, get the block with the lowest x and y

	$min_max = get_blocks_min_max($block_from, $banner_id);
	$from_x = $min_max['low_x'];
	$from_y = $min_max['low_y'];
	//echo "block_from: ($block_from) $from_x $from_y<br>";
	//echo "block_to: ($block_to) $to_x $to_y<br>";
	// get the position move's difference

	$dx = ($to_x - $from_x); //echo "$to_x - $from_x ($dx)<br>";
	$dy = ($to_y - $from_y);

	// get the order

	$sql = "SELECT * from blocks where block_id='$block_from' AND banner_id='$banner_id' ";
	//echo "$sql<br>";
	$result = mysql_query($sql) or die(mysql_error());
	$source_block = mysql_fetch_array($result);

	$sql = "SELECT * from blocks WHERE order_id='".$source_block['order_id']."' AND banner_id='$banner_id' ";
	//echo "$sql<br>";
	$result = mysql_query($sql) or die(mysql_error());

	$grid_width = G_WIDTH*BLK_WIDTH;

	while ($block_row=mysql_fetch_array($result)) { // check each block to make sure we can move it.

		//echo 'from: '.$block_row['x'].",".$block_row['y']." to ".($block_row['x']+$dx).",".($block_row['y']+$dy)." (to pos: $to_x, $to_y diff: $dx & $dy)<Br>";
		//$block_to = ((($block_row['y']+$dy)*$grid_width)+($block_row['x']+$dx))/10 ;

		$block_to = (($block_row['x']+$dx) / BLK_WIDTH) + ((($block_row['y']+$dy)/BLK_HEIGHT) * ($grid_width/BLK_WIDTH));
		
		if (!is_block_free($block_to, $banner_id)) {
			echo "<font color='red'>Cannot move the order - the space chosen is not empty!</font><br>";
			return false;
		}

	}

	mysql_data_seek($result, 0);

	while ($block_row=mysql_fetch_array($result)) {

		$block_from = (($block_row['x']) / BLK_WIDTH) + (($block_row['y']/BLK_HEIGHT) * ($grid_width/BLK_WIDTH)) ;
		$block_to = (($block_row['x']+$dx) / BLK_WIDTH) + ((($block_row['y']+$dy)/BLK_HEIGHT) * ($grid_width/BLK_WIDTH));

		move_block($block_from, $block_to, $banner_id);
	}

	return true;

}



######################################################
/*
function get_required_size($x, $y) - assuming the grid constants were initialized
$x and $y are the current size
*/
function get_required_size($x, $y) {
	$block_width = BLK_WIDTH;
	$block_height = BLK_HEIGHT;

	$size[0] = $x;
	$size[1] = $y;

	$mod = ($x % $block_width);
	
	if ($mod>0) { // width does not fit
		$size[0] = $x + ($block_width-$mod);

	}

	$mod = ($y % $block_height);
	
	if ($mod>0) { // height does not fit
		$size[1] = $y + ($block_height-$mod);

	}

	return $size;


}

######################################################
# If $user_id is null then return for all banners
function get_clicks_for_today($BID, $user_id=0) {

	$date = gmDate(Y)."-".gmDate(m)."-".gmDate(d);
	
	$sql = "SELECT *, SUM(clicks) AS clk FROM `clicks` where banner_id='$BID' AND `date`='$date' GROUP BY banner_id";
	$result = mysql_query($sql) or die(mysql_error());
	$row = mysql_fetch_array($result);

	return $row['clk'];


}

#######################################################
# If $BID is null then return for all banners
function get_clicks_for_banner($BID='') {

	$sql = "SELECT *, SUM(clicks) AS clk FROM `clicks` where banner_id='$BID'  GROUP BY banner_id";
	$result = mysql_query($sql) or die(mysql_error());
	$row = mysql_fetch_array($result);

	return $row['clk'];


}

#########################################################
/*

First check to see if the banner has packages. If it does
then check how many orders the user had. 
*/

function can_user_order($b_row, $user_id, $package_id=0) {
	// check rank


	$sql = "select Rank from users where ID='".$user_id."'";
	$result = mysql_query ($sql) or die (mysql_error().$sql);
	$u_row = mysql_fetch_array($result);

	if ($u_row['Rank']=='2') {

		return true;

	}
	
	if (banner_get_packages($b_row['banner_id'])) { // if user has package, check if the user can order this package
		if ($package_id==0) { // don't know the package id, assume true.
		
			return true;
		} else {
		
			return can_user_get_package($user_id, $package_id);
		}
	} else {
		
		// check againts the banner. (Banner has no packages)
		if (($b_row['max_orders'] > 0))  {

			$sql = "SELECT order_id FROM orders where `banner_id`='".$b_row['banner_id']."' and `status` <> 'deleted' and `status` <> 'new' AND user_id='".$user_id."'";
			
			$result = mysql_query($sql) or die(mysql_error().$sql);
			$count = mysql_num_rows($result);
			if ($count >= $b_row['max_orders']) {
				return false;
			} else {
				return true;
			}
		} else {
			return true; // can make unlimited orders
		}

	}

	

}

//////

function get_blocks_min_max($block_id, $banner_id) {

	$sql = "SELECT * FROM blocks where block_id='".$block_id."' and banner_id='".$banner_id."' ";

	$result = mysql_query($sql) or die(mysql_error());
	$row = mysql_fetch_array($result);

	$sql = "select * from blocks where order_id='".$row['order_id']."' ";
	$result3 = mysql_query($sql) or die(mysql_error());

	//echo $sql;


	// find high x, y & low x, y
	// low x,y is the top corner, high x,y is the bottom corner

	while ($block_row = mysql_fetch_array($result3)) {

		if ($high_x=='') {
			$high_x = $block_row['x'];
			$high_y = $block_row['y'];
			$low_x = $block_row['x'];
			$low_y = $block_row['y'];

		}

		if ($block_row['x'] > $high_x) {
			$high_x = $block_row['x'];
		}

		if ($block_row['y'] > $high_y) {
			$high_y = $block_row['y'];
		}

		if ($block_row['y'] < $low_y) {
			$low_y = $block_row['y'];
		}

		if ($block_row['x'] < $low_x) {
			$low_x = $block_row['x'];
		}

	}

	$ret = array();
	$ret['high_x'] = $high_x;
	$ret['high_y'] = $high_y;
	$ret['low_x'] = $low_x;
	$ret['low_y'] = $low_y;

	return $ret;



}

################################################
function get_definition($field_type) {

	switch ($field_type) {
			case "TEXT":
				return "VARCHAR( 255 ) NOT NULL ";
				break;
			case "SEPERATOR":
				break;
			case "EDITOR":
				return "TEXT NOT NULL ";
				break;
			case "CATEGORY":
				return "INT(11) NOT NULL ";
				break;
			case "DATE":
			case "DATE_CAL":
				return "DATETIME NOT NULL ";
				break;
			case "FILE":
				return "VARCHAR( 255 ) NOT NULL ";
				break;
			case "MIME":
				return "VARCHAR( 255 ) NOT NULL ";
				break;			
			case "BLANK":
				break;
			case "NOTE":
				return "VARCHAR( 255 ) NOT NULL ";
				break;
			case "CHECK":
				return "VARCHAR( 255 ) NOT NULL ";
				break;
			case "IMAGE":
				return "VARCHAR( 255 ) NOT NULL ";
				break;
			case "RADIO":
				return "VARCHAR( 255 ) NOT NULL ";
				break;
			case "SELECT":
				return "VARCHAR( 255 ) NOT NULL ";
				break;
			case "MSELECT":
				return "VARCHAR( 255 ) NOT NULL ";
				break;
			case "TEXTAREA":
				return "TEXT NOT NULL ";
				break;
			default:
				return "VARCHAR( 255 ) NOT NULL ";
				break;


		}

}

##############################################

function saveImage($field_id) {

	if (IMG_MAX_WIDTH=='IMG_MAX_WIDTH' ) {

		$max_width = '150';
	} else {
		$max_width = IMG_MAX_WIDTH;
	}

	
	$uploaddir = UPLOAD_PATH."images/";
	$thumbdir = UPLOAD_PATH."images/";
		
	
	$a = explode(".",$_FILES[$field_id]['name']);
	$ext = strtolower(array_pop($a));
	$name = strtolower(array_shift($a));

	if ($_SESSION['MDS_ID'] != '') {
		$name = $_SESSION['MDS_ID']."_".$name;
	} else {
	//	$name = subssession_id().$name;

	}
    //echo "<b>NAMEis:[$name]</b>";
	$name = ereg_replace("[ '\"]+", "_", $name); // strip quotes, spaces
	
	$new_name = $name.time().".".$ext;
	$uploadfile = $uploaddir . $new_name; //$uploaddir . $file_name;
	$thumbfile = $thumbdir . $new_name;

	//echo "te,p Image is:".$_FILES[$field_id]['tmp_name']." upload file:".$uploadfile;


	if (move_uploaded_file($_FILES[$field_id]['tmp_name'], $uploadfile)) {
		//echo "File is valid, and was successfully uploaded. ($uploadfile)\n";
	} else {
		switch ($_FILES[$field_id]["error"]) {
		   case UPLOAD_ERR_OK:
			   break;
		   case UPLOAD_ERR_INI_SIZE:
			   print("The uploaded file exceeds the upload_max_filesize directive (".ini_get("upload_max_filesize").") in php.ini.");
			   break;
		   case UPLOAD_ERR_FORM_SIZE:
			   print("The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.");
			   break;
		   case UPLOAD_ERR_PARTIAL:
			   print("The uploaded file was only partially uploaded.");
			   break;
		   case UPLOAD_ERR_NO_FILE:
			   print("No file was uploaded.");
			   break;
		   case UPLOAD_ERR_NO_TMP_DIR:
			   print("Missing a temporary folder.");
			   break;
		   case UPLOAD_ERR_CANT_WRITE:
			   print("Failed to write file to disk");
			   break;
		   default:
			   print("Unknown File Error");
		}

		//echo "Possible file upload attack ($uploadfile)! $field_id<br>\n";
		//echo $_FILES[$field_id]['tmp_name']."<br>";
	}

	$current_size = getimagesize($uploadfile);
    $width_orig = $current_size[0];
    $height_orig = $current_size[1];


	if ($width_orig > $max_width) {

		//echo "resizing file...<br>";

		// The file
		$filename = $uploadfile;

		// Set a maximum height and width
		$width = 200;
		$height = 200;

		// Content type
		//header('Content-type: image/jpeg');

		// Get new dimensions
		//list($width_orig, $height_orig) = getimagesize($filename);

		$ratio_orig = $width_orig/$height_orig;

		if ($width/$height > $ratio_orig) {
		   $width = $height*$ratio_orig;
		} else {
		   $height = $width/$ratio_orig;
		}

		// Resample
		$image_p = imagecreatetruecolor($width, $height);
		//echo "type is:".$_FILES[$field_id]['type']."<br>";
		//echo "orig file is:".$filename."<br>";
		//echo "dest file is:".$thumbfile."<br>";
		switch ($_FILES[$field_id]['type']) {
			case "image/gif":
				touch ($filename);
				$uploaded_img = imagecreatefromgif($filename);
				imagecopyresampled($image_p, $uploaded_img, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
				unlink ($filename); // delete original file 
				// Output
				imagejpeg($image_p, $thumbfile, 100);

				break;
			case "image/jpg":
			case "image/jpeg":
			case "image/pjpeg":
				touch ($filename);
				$uploaded_img = imagecreatefromjpeg($filename);
				imagecopyresampled($image_p, $uploaded_img, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
				unlink ($filename); // delete original file 
				// Output
				imagejpeg($image_p, $thumbfile, 100);
				break;
			case "image/png":
			case "image/x-png":
				touch ($filename);
				$uploaded_img = imagecreatefrompng($filename);
				imagecopyresampled($image_p, $uploaded_img, 0, 0, 0, 0, $width, $height, $width_orig, $height_orig);
				unlink ($filename); // delete original file 
				// Output
				imagejpeg($image_p, $thumbfile, 100);
				break;
			default:
				break;
		}

		imagedestroy ($uploaded_img);
		imagedestroy ($image_p);

		
   
   } else {
      //echo 'No need to resize.<br>';
	  
   } 
	//@unlink($uploadfile); // delete the original file.
   return $new_name;
} 

###########################################################

function deleteImage($table_name, $object_name, $object_id, $field_id) {
   $sql = "SELECT `$field_id` FROM `$table_name` WHERE `$object_name`='$object_id'";
   $result = mysql_query($sql) or die (mysql_error().$sql);
   $row = mysql_fetch_array ($result, MYSQL_ASSOC);
   if ($row[$field_id] != '') {
	  // delete the original
      @unlink (UPLOAD_PATH."images/".$row[$field_id]);
      // delete the thumb
      //@unlink (IMG_PATH."thumbs/".$row[$field_id]);
      //echo "<br><b>unlnkthis[".IMG_PATH."thumbs/$new_name]</b><br>";
   }

// yeo su 019 760 0030

}


##########################################################

function saveFile($field_id) {

	$uploaddir = UPLOAD_PATH.'docs/';
			
	$ext = substr(strrchr($_FILES[$field_id]['name'], "."), 1);
	$name = reverse_strrchr($_FILES[$field_id]['name'], ".");
	$name = substr($name, 0, -1);
	if ($_SESSION['MDS_ID'] != '') {
		$name = $_SESSION['MDS_ID']."_".$name;
	}
   // echo "<b>NAMEis:[$name]</b>";
	$name = ereg_replace("[ '\"]+", "_", $name); // strip quotes, spaces
	
	$new_name = $name.time().".".$ext;
	$uploadfile = $uploaddir . $new_name; //$uploaddir . $file_name;
	
	//echo "te,p Image is:".$_FILES[$field_id]['tmp_name']." upload file:".$uploadfile;


	if (move_uploaded_file($_FILES[$field_id]['tmp_name'], $uploadfile)) {
		//echo "File is valid, and was successfully uploaded.\n";
	} else {
		//echo "Possible file upload attack ($uploadfile)!\n";
	}

	return $new_name;

}

#####################################################################

function deleteFile($table_name, $object_name, $object_id, $field_id) {
   $sql = "SELECT `$field_id` FROM `$table_name` WHERE `$object_name`='$object_id'";
   $result = mysql_query($sql) or die (mysql_error());
   $row = mysql_fetch_array ($result, MYSQL_ASSOC);
   if ($row[$field_id] != '') {
	  // delete the original
      @unlink (UPLOAD_PATH."docs/".$row[$field_id]);
      // delete the thumb
     // unlink (FILE_PATH."thumbs/".$row[$field_id]);
      //echo "<br><b>unlnkthis[".IMG_PATH."thumbs/$new_name]</b><br>";
   }

// yeo su 019 760 0030

}
/////////////////////////////////////
###########################################################

function is_filetype_allowed ($file_name) {

	$a = explode(".",$file_name);
	$ext = strtolower(array_pop($a));

	if (ALLOWED_EXT=='ALLOWED_EXT') { 
		$ALLOWED_EXT= 'jpg, jpeg, gif, png, doc, pdf, wps, hwp, txt, bmp, rtf, wri';
	} else { 
		$ALLOWED_EXT=trim(strtolower(ALLOWED_EXT));
	}

	//$ext_list = explode (',',$ALLOWED_EXT);
	$ext_list =preg_split ("/[\s,]+/", ($ALLOWED_EXT));
	return in_array($ext, $ext_list);


}

###########################################################

function is_imagetype_allowed ($file_name) {

	$a = explode(".",$file_name);
	$ext = strtolower(array_pop($a));

	if (ALLOWED_IMG=='ALLOWED_IMG') { 
		$ALLOWED_IMG= 'jpg, jpeg, gif, png, doc, pdf, wps, hwp, txt, bmp, rtf, wri';
	} else { 
		$ALLOWED_IMG=trim(strtolower(ALLOWED_IMG));
	}

	//$ext_list = explode (',',$ALLOWED_EXT);
	$ext_list =preg_split ("/[\s,]+/", ($ALLOWED_IMG));
	return in_array($ext, $ext_list);


}
/////////////////////////////////////

function get_tmp_img_name ($session_id='') {
	
	if ($session_id=='') {
		$session_id = addslashes(session_id());
	}
	$uploaddir = SERVER_PATH_TO_ADMIN."temp/";
	$dh = opendir($uploaddir);
	while (($file = readdir($dh)) !== false) {
		$stat =stat($uploaddir.$file);
		if (strpos($file, "tmp_".md5($session_id)) !== false) {
			//unlink($uploaddir.$file);
			
			return $uploaddir.$file;
		}
	}

}

////////////////////////////

function update_temp_order_timestamp() {
	$now = (gmdate("Y-m-d H:i:s")); 
	$sql = "UPDATE temp_orders SET order_date='$now' WHERE session_id='".addslashes(session_id())."' ";
	mysql_query($sql);

}

////////////////

function show_nav_status ($page_id) {
	global $label;
	for ($i=1; $i <= 5; $i++) {
		if ($i==$page_id) {
			$b1= "<b>"; $b2 = "</b>";
		} else {
			$b1= ""; $b2 = "";
		}
		echo $b1;
		echo $label['advertiser_nav_status'.$i];
		if ($i < 5) {
			echo ' -&gt; ';
		}
		echo $b2;

	}


}

////////////////////////


/**
 * @return string
 * @param string
 * @desc Strip forbidden tags and delegate tag-source check to removeEvilAttributes()
 */
function removeEvilAttributes($tagSource) {
       $stripAttrib = '/ (style|class|onclick|ondblclick|onmousedown|onmouseup|onmouseover|onmousemove|onmouseout|onkeypress|onkeydown|onkeyup|onload)=/'; // (\'|")[^$2]+/i
       //$tagSource = stripslashes($tagSource);
       $tagSource = preg_replace($stripAttrib, '  ', $tagSource);
	  // $tagSource = addslashes($tagSource);
	  //echo htmlentities($tagSource);
       return $tagSource;
}
/**
 * @return string
 * @param string
 * @desc Strip forbidden attributes from a tag
 */
function removeEvilTags($source){
   $allowedTags = '<h1><b><br><br><i><a><ul><li><hr><blockquote><img><span><div><font><p><em><strong><center><div><table><td><tr>';
   $source = strip_tags($source, $allowedTags);
   return removeEvilAttributes($source);
   //return preg_replace('/<(.*?)>/ie', "'<'.removeEvilAttributes('\\1').'>'", $source);
}

##############################################################

function remove_non_latin1_chars($str) {
	// strip out characters that aren't valid in ISO-8859-1 (Also known as 'Latin 1', used in HTML Documents)
	return preg_replace('/[^\x09\x0A\x0D\x20-\x7F\xC0-\xFF]/', '', $str);

}

################################################

function trim_date($gmdate) {
	preg_match ("/(\d+-\d+-\d+).+/", $gmdate, $m);
	return $m[1];

}

###########################################


function get_formatted_date($date) {

	if (!defined('DATE_INPUT_SEQ')) {
		define ('DATE_INPUT_SEQ', 'YMD');
	}

	$year = substr ($date, 0, 4);
	
	if (($year > 2038) || ($year < 1970)) {  //  out of range to format!
		$month =  substr ($date, 5, 2);
		$day =  substr ($date, 8, 2);
		$sequence = strtoupper(DATE_INPUT_SEQ);
		while ($widget = substr($sequence, 0, 1)) {
			switch ($widget) {
				case 'Y':
					$ret .= $s.$year;
				break;
				case 'M':
					$ret .=  $s.$month;
				break;
				case 'D':
					$ret .=  $s.$day;
				break;
			}
			$s='-';
			$sequence = substr($sequence, 1);
		}
		return $ret;
		
	}
	
	// else:
	$time = strtotime($date);
	return date(DATE_FORMAT, $time);
	
}
######################################################


function get_local_time($gmdate) {

	if ((strpos ($gmdate, 'GMT')===false) && ((strpos ($gmdate, 'UTC')===false)) && ((strpos ($gmdate, '+0000')===false))) { // gmt not found
		$gmdate = $gmdate." GMT"; 

	}
	$gmtime = strtotime($gmdate);

	if ($gmtime==-1) { // out of range
		preg_match ("/(\d+-\d+-\d+).+/", $gmdate, $m);
		return $m[1];
	
	} else {
	
		return gmdate("Y-m-d H:i:s", $gmtime + (3600 * GMT_DIF));
	}

}

################################################

function get_html_strlen($str) {

	while ((preg_match ("/(&#?[0-9A-z]+;|.)/", $s, $maches, PREG_OFFSET_CAPTURE, $offset))) {
		$offset += strlen($maches[0][0]);
		$len++;
		
	}
	return $len;

}

///////////////////


function break_long_words($input, $with_tags) {
	// new routine, deals with html tags...
	if (defined('LNG_MAX')) {
		$lng_max = LNG_MAX;
	} else {
		$lng_max = 100;
	}
	//echo $lng_max;
	
	$input = stripslashes($input);

	while ($trun_str = truncate_html_str($input, $lng_max, $trunc_str_len, false, $with_tags)) {

		//echo "trun_str:".htmlentities($trun_str)."<br>";
		
		if ($trunc_str_len == $lng_max) { // string was truncated
			
			//echo "truncate!";
			if (strrpos ($trun_str, " ")!==false) { // if trun_str has a space?
				$new_str .= $trun_str;
				//echo " has space![".htmlentities($trun_str)."]<br>";
				
			} else {
				$new_str .= $trun_str." ";
				//echo " no space[".htmlentities($trun_str)."]<br>";
				
			}

		} else {
			$new_str .= $trun_str;
		}
		$input = substr($input, strlen($trun_str));
	}

	$new_str = addslashes($new_str);

	return $new_str;




}


#######################################
# function truncate_html_str 
# truncate a string encoded with htmlentities eg &nbsp; is counted as 1 character
# Limitation: does not work with well if the string contains html tags, (but does it's best to deal with them).
function truncate_html_str ($s, $MAX_LENGTH, &$trunc_str_len) {

	$trunc_str_len=0;

	if (func_num_args()>3) {
		$add_ellipsis = func_get_arg(3);

	} else {
		$add_ellipsis = true;
	}

	if (func_num_args()>4) {
		$with_tags = func_get_arg(4);

	} else {
		$with_tags = false;
	}

	if ($with_tags){
		$tag_expr = "|<[^>]+>";

	}

	$offset = 0; $character_count=0;
	# match a character, or characters encoded as html entity
	# treat each match as a single character
	#
	while ((preg_match ('/(&#?[0-9A-z]+;'.$tag_expr.'|.|\n)/', $s, $maches, PREG_OFFSET_CAPTURE, $offset) && ($character_count < $MAX_LENGTH))) {
		$offset += strlen($maches[0][0]);
		$character_count++;
		$str .= $maches[0][0];
		
	
	}
	if (($character_count == $MAX_LENGTH)&&($add_ellipsis)) {
		$str = $str."...";
	}
	$trunc_str_len = $character_count;
	return $str;

 
}

/////////////////////////////////////////

// assumming that load_banner_constants($_REQUEST['BID']); was called...
function get_pixel_image_size($order_id) {

	$sql = "SELECT * FROM blocks WHERE order_id='$order_id' ";

	$result3 = mysql_query ($sql) or die (mysql_error().$sql);
	
//echo $sql;
	// find high x, y & low x, y
// low x,y is the top corner, high x,y is the bottom corner

	while ($block_row = mysql_fetch_array($result3)) {

		if ($high_x=='') {
			$high_x = $block_row['x'];
			$high_y = $block_row['y'];
			$low_x = $block_row['x'];
			$low_y = $block_row['y'];

		}

		if ($block_row['x'] > $high_x) {
			$high_x = $block_row['x'];
		}

		if ($block_row['y'] > $high_y) {
			$high_y = $block_row['y'];
		}

		if ($block_row['y'] < $low_y) {
			$low_y = $block_row['y'];
		}

		if ($block_row['x'] < $low_x) {
			$low_x = $block_row['x'];
		}

		

		$i++;

	}

	$size['x'] = ($high_x + BLK_WIDTH) - $low_x;
	$size['y'] = ($high_y + BLK_HEIGHT) - $low_y;

	return $size;

}

////////////////

function bcmod_wrapper( $x, $y ) 
{
	if (function_exists('bcmod')) {
		return bcmod($x, $y);
	}
   // how many numbers to take at once? carefull not to exceed (int) 
   $take = 5;    
   $mod = ''; 

   do 
   { 
       $a = (int)$mod.substr( $x, 0, $take ); 
       $x = substr( $x, $take ); 
       $mod = $a % $y;    
   } 
   while ( strlen($x) ); 

   return (int)$mod; 
} 


////////////////////////////////

function elapsedtime($sec){ 
	$days  = floor($sec / 86400); 
	$hrs   = floor(bcmod_wrapper($sec,86400)/3600); 
	$mins  = round(bcmod_wrapper(bcmod_wrapper($sec,86400),3600)/60); 
	if($days > 0) $tstring = $days . "d, "; 
	if($hrs  > 0) $tstring = $tstring . $hrs . "h, "; 
	$tstring = "" . $tstring . $mins . "m"; 
	return $tstring; 
} 

///////////////////////////////////////////

//////////////////
// convert decimal string to a hex string.
function decimal_to_hex($decimal) {
	return sprintf('%X', $decimal);
}

function htmlent_to_hex ($str) {
// convert html Unicode entities to Javascript Unicode entities &#51060 to \u00ED
	return preg_replace ("/&#([0-9A-z]+);/e", "'\\\u'.decimal_to_hex('\\1')" , $str);
}
// Javascript string preperation.
function js_out_prep($str) {
	$str = addslashes($str);
	$str = htmlent_to_hex($str);
	return $str;
}

function echo_copyright() {

	/*
	This software is free on the condition that you do not remove 
	any copyright messages as part of the license. 
	
	If you want to remove these, 
	please see http://www.milliondollarscript.com/remove.html 
	*/

	?>

	<div style="font-size:xx-small; text-align:center">Powered By <a target="_blank" style="font-size:7pt;color:black" href="http://www.milliondollarscript.com/">Million Dollar Script</a> (c) 2008</div>

	<?php

}

?>