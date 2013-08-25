<?php
/**
 * @version		$Id: menu.php 86 2010-10-12 13:51:14Z ryan $
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
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<HTML>
<HEAD>
<style>
a {
	color:#000000;
	text-decoration: none;
		 
}

a:hover {
	color:#3399FF;
}
</style>
<TITLE> Menu </TITLE>
<META NAME="Generator" CONTENT="EditPlus">
<META NAME="Author" CONTENT="">
<META NAME="Keywords" CONTENT="">
<META NAME="Description" CONTENT="">
</HEAD>

<BODY style=" font-family: 'Arial', sans-serif; font-size:10pt; color:#000000;  " bgcolor="#F4F4F4">
<span style="padding: 0px;"><strong>Million Dollar Script</strong> <small><br>Copyright 2008, see COPYING.txt for license information.<br> <?php echo VERSION_INFO; ?></small></span><br>
<br>
<a href="main.php" target="main">Main Summary</a><br/>
<hr>
<b>Pixel Inventory</b><br/>
+ <a href="inventory.php" target="main">Manage Grids</a><br/>
&nbsp;&nbsp;|- <a href="packs.php" target="main">Packages</a><br/>
&nbsp;&nbsp;|- <a href="price.php" target="main">Price Zones</a><br/>
&nbsp;&nbsp;|- <a href="nfs.php" target="main">Not For Sale</a><br/>
&nbsp;&nbsp;|- <a href="blending.php" target="main">Backgrounds</a><br/>
- <a href="gethtml.php" target="main">Get HTML Code</a><br/>

<hr>
<b>Advertiser Admin</b><br/>
- <a href="customers.php" target="main">List Advertisers</a><br/>
<font size=1>Current orders:</font><br> 
- <a href="orders.php?show=WA" target="main">Orders: Waiting</a><br/>
- <a href="orders.php?show=CO" target="main">Orders: Completed</a><br/>
<font size=1>Non-current orders:</font><br> 
- <a href="orders.php?show=EX" target="main">Orders: Expired</a><br/>
- <a href="orders.php?show=CA" target="main">Orders: Cancelled</a><br/>
- <a href="orders.php?show=DE" target="main">Orders: Deleted</a><br/>
<font size=1>Map:</font><br>
- <a href="ordersmap.php" target="main">Map of Orders</a><br/>
<font size=1>Transactions:</font><br> 
- <a href="transactions.php" target="main">Transaction Log</a><br/>
<hr>
<b>Pixel Admin</b><br/>
- <a href="approve.php?app=N" target="main">Approve Pixels</a><br/>
- <a href="approve.php?app=Y" target="main">Disapprove Pixels</a><br/>
- <a href="process.php" target="main">Process Pixels</a><br/>
<hr>
<b>Report</b><br/>
- <a href="ads.php" target="main">Ad List</a><br/>
- <a href="list.php" target="main">Top Advertisers</a><br/>
- <a href="email_queue.php" target="main">Outgoing Email</a><br/>
<!--
- <a href="expr.php" target="main">Expiration Reminders</a><br/>
-->
<font size=1>Clicks:</font><br>
- <a href="top.php" target="main">Top Clicks</a><br/>
- <a href="clicks.php" target="main">Click Reports</a><br/>
<hr>
<b>Configuration</b><br/>
- <a href="edit_config.php" target="main">Main Config</a><br/>
- <a href="language.php" target="main">Language</a><br/>
- <a href="currency.php" target="main">Currencies</a><br/>
- <a href="payment.php" target="main">Payment Modules</a><br/>
- <a href="adform.php" target="main">Ad Form</a><br/>
<hr>
<b>Logout</b><br/>
- <a href="logout.php" target="main">Logout</a><br/>
<hr>
<b>Info</b><br/>
- <a href="info.php" target="main">System Info</a><br/>
- <a href="http://www.milliondollarscript.com" target="main">Script Home</a><br/>
</BODY>
</HTML>
