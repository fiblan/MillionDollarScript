<?php


ini_set('max_execution_time', 100200);
require("../config.php");

$VERBOSE = "YES";
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
if ($_REQUEST['action']=="send") {
	$DO_SEND = "YES";
} elseif ($_SERVER['PHP_SELF']=="") {
	$DO_SEND = "YES";
} else {
	?>
<h3>This is a backend script which will process your outgoing email queue</h3><br>
Set this file up in your Cron jobs to run <i>every few minutes</i><br>
This scripts's location is: <b><?php echo $_SERVER['SCRIPT_FILENAME'];?></b><br>
Crontab command to run will look something like:<b> /usr/bin/php -f <?php echo $_SERVER['SCRIPT_FILENAME'];?></b><br>(Depending on the location of php)<p>

<br>Run Manually form Web:<input type="button" value="Process outgoing email queue" onclick="window.location='<?php echo $_SERVER['PHP_SELF'];?>?action=send' " >

	<?php
		die();

}

if ($DO_SEND=='YES') {

	process_mail_queue(EMAILS_PER_BATCH);

}
?>