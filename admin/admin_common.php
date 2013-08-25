<?php
session_start();

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
if (($_REQUEST['pass']!='') && (MAIN_PHP=='1')) {

	if ($_REQUEST['pass']==ADMIN_PASSWORD) {
		$_SESSION[ADMIN] = '1';

	}

}
if (($_SESSION[ADMIN]=='') ) {

	if (MAIN_PHP=='1') {
	?>
Please input admin password:<br>
<form method='post'>
<input type="password" name='pass'>
<input type="submit" value="OK">
</form>
	<?php

	}

	die();

} 

?>