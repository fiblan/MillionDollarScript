<?php
/**
 * @version		$Id: currency_functions.php 64 2010-09-12 01:18:42Z ryan $
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

function currency_option_list ($selected) {

	$sql = "SELECT * FROM currencies ORDER by name ";
	$result = mysql_query ($sql) or die (mysql_error().$sql);
	while ($row = mysql_fetch_array($result)) {
		if ($row['code']==$selected) {
			$sel = " selected ";
		} else {
			$sel = "";
		}
		echo "<option $sel value=".$row['code'].">".$row['code']." ".$row['sign']."</option>";

	}

}
#############################


function get_default_currency() {

	$sql = "SELECT code from currencies WHERE is_default='Y' ";
	$result = mysql_query ($sql) or die (mysql_error().$sql);
	$row = mysql_fetch_array($result);
	$ret = $row['code'];
	if ($ret=='') {
		$ret = 'USD';
	}
	return $ret;


}
#############################


function get_currency_rate($code) {

	$sql = "SELECT rate from currencies WHERE code='$code' ";
	$result = mysql_query ($sql) or die (mysql_error().$sql);
	$row = mysql_fetch_array($result);
	return $row['rate'];


}

##############################
function convert_to_currency($amount, $from_currency, $to_currency) {
//echo "amt:$amount, from:$from_currency, to:$to_currency";

	if ($from_currency == $to_currency) {
		return $amount;
	}

	if ($from_currency=='') { // buggy version fix
		$from_currency = get_default_currency();
	}

	//echo "$amount, $from_currency, $to_currency";

	$sql = "SELECT rate from currencies WHERE code='$from_currency' ";
	$result = mysql_query ($sql) or die (mysql_error().$sql);
	$row = mysql_fetch_array($result);
	$from_rate = $row['rate'];

	$sql = "SELECT rate, decimal_places from currencies WHERE code='$to_currency' ";
	$result = mysql_query ($sql) or die (mysql_error().$sql);
	$row = mysql_fetch_array($result);
	$to_rate = $row['rate'];
	$to_decimal_places = $row['decimal_places'];

	$new_amount = ($amount * $to_rate) / $from_rate;
	$new_amount = round ($new_amount, $to_decimal_places);

	return $new_amount;

}

###############################
// return as a float
function convert_to_default_currency($cur_code, $amount) {
	if (func_num_args()>2) {
		$from_rate = func_get_arg(2);
	}

	if ($cur_code=='') {
		$cur_code = get_default_currency(); // cur code can be blank due to some bugs in the old version
	}

	if ($cur_code==get_default_currency()) {
		return $amount;
	}



	if ($from_rate == '') {
		$sql = "SELECT * from currencies WHERE code='$cur_code' ";
		$result = mysql_query ($sql) or die (mysql_error().$sql);
		$row = mysql_fetch_array($result);
		$from_rate = $row['rate'];
	}
	

	$sql = "SELECT * from currencies WHERE is_default='Y' ";
	$result = mysql_query ($sql) or die (mysql_error().$sql);
	$row = mysql_fetch_array($result);
	$to_rate = $row['rate'];
	$to_decimal_places = $row['decimal_places'];

	$new_amount = ($amount * $to_rate) / $from_rate;
	$new_amount = round ($new_amount, $to_decimal_places);

	return $new_amount;


}

##############################################

function convert_to_default_currency_formatted($cur_code, $amount) {

	if (func_num_args()>2) {
		$show_code = func_get_arg(2);
	}

	if (func_num_args()>3) {
		$from_rate = func_get_arg(3);
	}
	if ($cur_code=='') {
		$cur_code = get_default_currency(); // cur code can be blank due to some bugs in the old version
	}

	// load default currency

	$sql = "SELECT * from currencies WHERE is_default='Y' ";
	$result = mysql_query ($sql) or die (mysql_error().$sql);
	$row = mysql_fetch_array($result);
	$to_rate = $row['rate'];
	$to_code = $row['code'];
	$to_decimal_places = $row['decimal_places'];

	if ($cur_code==get_default_currency()) {
		$new_amount = $amount;
		
	} else {

		if ($from_rate == '') {

			//load from rate

			$sql = "SELECT * from currencies WHERE code='$cur_code' ";
			$result = mysql_query ($sql) or die (mysql_error().$sql);
			$row = mysql_fetch_array($result);
			$from_rate = $row['rate'];
		}

		$new_amount = ($amount * $to_rate) / $from_rate;
		$new_amount = round ($new_amount, $to_decimal_places);

	}

	

	return format_currency($new_amount, $to_code, $show_code, true) ;


}

##############################################

function format_currency($amount, $cur_code) {
	if (func_num_args()>2) {
		$show_code = func_get_arg(2);
		

	}
	$sql = "SELECT * FROM currencies WHERE code='$cur_code' ";
	$result = mysql_query ($sql) or die (mysql_error());
	//format_currency($row['price'], $row['currency_code']);
	$row = mysql_fetch_array($result);
	if ($show_code) {
		$show_code = " ".$row['code'];

	}
	$amount = number_format ( $amount , $row[decimal_places], $row[decimal_point], $row[thousands_sep] );
	$amount = $row['sign']."".$amount.$show_code;

	return $amount;


}
######################################
?>