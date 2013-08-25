<?php

session_start();
include ("../config.php");
include ("login_functions.php");
include ("../payment/payment_manager.php");
//process_login();

require ("header.php");

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




?>

<p></p>

<p>&nbsp;</p>
<h3><center><?php echo $label['payment_return_thanks'];?></center></h3>


<?php

$className = $_REQUEST['m'];
process_payment_return($className);

?></p>
<p>&nbsp;</p>

<?php require "footer.php"; ?>