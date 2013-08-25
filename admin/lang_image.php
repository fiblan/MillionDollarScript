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
require("../config.php");
require ('admin_common.php');


$sql = "SELECT * FROM lang where lang_code='".$_REQUEST['code']."' ";
$result  = mysql_query ($sql) or die(mysql_error());
$row = mysql_fetch_array($result, MYSQL_ASSOC);


header ("Content-type: ".$row[mime_type]);

echo base64_decode( $row[image_data]);


?>
