<?php
ini_set('max_execution_time', 500);
require("../config.php");
require ('admin_common.php');


$BID = 1; # Banner ID.


if ($_REQUEST['process']=='1') {

	if (($_REQUEST['banner_list'][0])=='all') {
		// process all
		$sql = "select * from banners ";
		$result = mysql_query ($sql) or die (mysql_error().$sql);	
		while ($row = mysql_fetch_array($result)) {
			echo process_image($row['banner_id']);
			publish_image($row['banner_id']);
			process_map($row['banner_id']);


		}
	} else { // process selected

		
		foreach($_REQUEST['banner_list'] as $key => $banner_id) {
			
			echo process_image($banner_id);
			publish_image($banner_id);
			process_map($banner_id);
		}


	}


	
	echo "<br>Finished.<hr>";
}

#######################
# Process images



?>
<script language="JavaScript" type="text/javascript">

	function confirmLink(theLink, theConfirmMsg) {
    
       if (theConfirmMsg == '' || typeof(window.opera) != 'undefined') {
           return true;
       }

       var is_confirmed = confirm(theConfirmMsg + '\n');
       if (is_confirmed) {
           theLink.href += '&is_js_confirmed=1';
       }

       return is_confirmed;
	}
	</script>
	<p>

	</p>
	<?php
	
	//$file_path = getcwd();
	
	$file_path = SERVER_PATH_TO_ADMIN;
	$file_path = str_replace("\\", "/",  $file_path);
	#echo "*** [$file_path] %%%% ";
	#$file_path = $_SERVER['SCRIPT_FILENAME']; // eg e:/apache/htdocs/ojo/admin/edit_config.php
	$file_path = explode ("/", $file_path);
	array_pop($file_path); // get rid of filename
    array_pop($file_path); // get rid of /admin
    $file_path = implode ("/", $file_path);
	$file_path = $file_path."/";

if (!is_writable(SERVER_PATH_TO_ADMIN."temp/")) {
	echo "<b>Warning:</b> The script does not have permission write to $file_path"."admin/temp/ or the directory does not exist <br>";

}
$BANNER_DIR = get_banner_dir();

if (!is_writable($file_path.$BANNER_DIR)) {
	echo "<b>Warning:</b> The script does not have permission write to $file_path"."$BANNER_DIR or the directory does not exist<br>";

}

	?>


<?php

$sql = "SELECT * FROM orders where approved='N' and status='completed' ";
$r = mysql_query($sql) or die(mysql_error());
$result = mysql_fetch_array($r);
$c = mysql_num_rows($r);

if ($c >0) {
	echo "<h3>Note: There are/is $c pixel ads waiting to be approved. <a href='approve.php'>Approve pixel ads here.</a></h3>";
}


?>
<p>
Here you can process the images. This is where the script gets all the user's approved pixels, and merges it into a single image. It automatically publishes the final grid  into the <?php echo $BANNER_DIR; ?> directory where the grid images are served from. Click the button below after approving pixels.
</p>
<form method='post' action='<?php $_SERVER['PHP_SELF']; ?>'>
<input value='1' name="process" type="hidden">
<select name="banner_list[]" multiple size='3'>
<option value="all" selected>Process All</option>
<?php

$sql = "Select * from banners";
$res = mysql_query($sql);

while ($row=mysql_fetch_array($res)) {
	echo '<option value="'.$row['banner_id'].'">#'.$row['banner_id'].' - '.$row["name"].'</option>'."\n";
}
?>
</select><br>
<input type="submit" name='submit' value="Process Grids(s)" >
</form>

