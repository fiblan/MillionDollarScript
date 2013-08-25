<?php

require("../config.php");
require ('admin_common.php');

?>

<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">

<title>Grid Admin</title>



</head>

<body style=" font-family: 'Arial', sans-serif; font-size:10pt; ">


<?php




?>
<p>
Grid HTML - This is the HTML code that you copy and paste to your HTML documents to display the grid<br>
Stats HTML - Copy and paste into your html file to display the stats<br>
</p>
<table width="100%" border="0" cellSpacing="1" cellPadding="3" bgColor="#d9d9d9" >
			<tr bgColor="#eaeaea">
				<td><b><font size="2">Grid ID</b></font></td>
				<td><b><font size="2">Name</b></font></td>
				<td><b><font size="2">Grid HTML</b></font></td>
				<td><b><font size="2">Stats HTML</b></font></td>
				
			</tr>
<?php
			$result = mysql_query("select * FROM banners") or die (mysql_error());
			while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {

				?>

				<tr bgcolor="#ffffff">

				<td><font size="2"><?php echo $row['banner_id'];?></font></td>
				<td><font size="2"><?php echo $row['name'];?></font></td>
				<td><textarea onfocus="this.select()" rows='3' cols='35'><?php echo get_html_code($row['banner_id']); ?></textarea></td>
				<td><textarea onfocus="this.select()" rows='3' cols='35'><?php echo get_stats_html_code($row['banner_id']); ?></textarea></td>
							
				</tr>
				<?php

			}
?>
</table>



</body>

</html>