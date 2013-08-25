<?php
require("../config.php");
require ('admin_common.php');



$sql ="SELECT * FROM mail_queue where mail_id=".$_REQUEST[mail_id];
$result = mysql_query($sql);
$row = mysql_fetch_array($result);
?>

<table border="1" id="table1" width="600">
	<tr>
		<td width="118">Template ID:</td>
		<td width="322"><?php echo $row[template_id]; ?></td>
	</tr>
	<tr>
		<td width="118">To Name:</td>
		<td width="322"><?php echo $row[to_name]; ?></td>
	</tr>
	<tr>
		<td width="118">To Address:</td>
		<td width="322"><?php echo $row[to_address]; ?></td>
	</tr>
	<tr>
		<td width="118">From Name:</td>
		<td width="322"><?php echo $row[from_name]; ?></td>
	</tr>
	<tr>
		<td width="118">From Address:</td>
		<td width="322"><?php echo $row[from_address]; ?></td>
	</tr>
	<tr>
		<td width="118">Subject:</td>
		<td width="322"><?php echo $row[subject]; ?></td>
	</tr>
	<tr>
		<td width="118">Message (text)</td>
		<td width="322"></td>
	</tr>
	<tr>
		<td colspan="2"><pre><?php echo $row[message]; ?></pre></td>
	</tr>
	<tr>
		<td width="118">Message (HTML)</td>
		<td width="322"></td>
	</tr>
	<tr>
		<td colspan="2"><?php echo $row[html_message]; ?></td>
	</tr>
	<tr>
		<td width="118">Attachments</td>
		<td width="322"><?php echo $row[att1_name]; ?><br>
		<?php echo $row[att2_name]; ?><br>
		<?php echo $row[att3_name]; ?><br>
		</td>
	</tr>
</table>

