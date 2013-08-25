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

require ('../config.php');

$BID = $_REQUEST['BID'];

if ($BID=='') {
	$BID=1;
}


$sql = "SELECT * FROM blocks where banner_id='$BID' AND block_id='".$_REQUEST['block_id']."' ";
$result  = mysql_query ($sql) or die(mysql_error());
$row = mysql_fetch_array($result, MYSQL_ASSOC);

if ($row['status']=="sold") {

	if ($row[image_data]=='') {

		# hard coded above file to save a file read...

		$mime_type = "image/x-png";

		$data = "iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAABGdBTUEAALGPC/xhBQAAABdJREFUKFNjvHLlCgMeAJT+jxswjFBpAOAoCvbvqFc9AAAAAElFTkSuQmCC";

		//$data = "iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAABGdBTUEAALGPC/xhBQAAABZJREFUKFNj/N/gwIAHAKXxIIYRKg0AB3qe55E8bNQAAAAASUVORK5CYII=";

	} else {

		$data = $row['image_data'];
		$mime_type = $row['mime_type'];

	}

} else {

	$file_name = "block.png";
	$mime_type = "image/x-png";

	# hard coded above file to save a file read...

	$data = "iVBORw0KGgoAAAANSUhEUgAAAAoAAAAKCAIAAAACUFjqAAAABGdBTUEAALGPC/xhBQAAABdJREFUKFNjvHLlCgMeAJT+jxswjFBpAOAoCvbvqFc9AAAAAElFTkSuQmCC";


}


header ("Content-type: $mime_type");
echo base64_decode($data);
//$file = fopen ($file_name, 'r');
//$data = fread ($file, filesize($file_name));
//echo base64_encode($data);
//fclose ($file);



?>
