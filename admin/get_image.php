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
define ('NO_HOUSE_KEEP', 'YES');

require("../config.php");
require ('admin_common.php');

header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past


$sql = "SELECT * FROM blocks where block_id='".$_REQUEST['block_id']."' ";
$result  = mysql_query ($sql) or die(mysql_error());
$row = mysql_fetch_array($result, MYSQL_ASSOC);



if ($row[image_data]=='') {

	
	#$file_name = "block.png";
	$data = "iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAABGdBTUEAALGPC/xhBQAAABdJREFUKFNjvHLlCgMeAJT+jxswjFBpAOAoCvbvqFc9AAAAAElFTkSuQmCC";

	header ("Content-type: image/png");
	#$file = fopen ($file_name, 'r');
	#$data = fread ($file, filesize($file_name));
	echo base64_decode($data);
	#fclose($file);

} else {
	header ("Content-type: ".$row[mime_type]);
	echo base64_decode( $row[image_data]);

}

?>
