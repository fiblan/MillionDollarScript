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

?>
<p>System info</p>
You have PHP version: <?php echo phpversion(); ?><br><br>
GD Library version: <pre><?php print_r (gd_info()); ?></pre><br>
<?php if (!function_exists("imagecreatetruecolor")) { echo "imagecreatetruecolor() is not supported by your version GD. Using imagecreate() instead."; }  ?>
<br>
Your path to your admin directory: <?php echo str_replace('\\', '/', getcwd());?>/
<hr>
<?php
phpinfo();
?>