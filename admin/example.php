<?php
$im=imagecreatetruecolor(300,300);
$white=imagecolorallocate($im,255,255,255);
imagefilledrectangle($im,0,0,imagesx($im),imagesy($im),$white);

for($i=0;$i<256;$i=$i+10)
{
	$col=imagecolorallocatealpha($im,$i,$i,$i,66);
	imagefilledellipse($im,$i,$i,$i,$i,$col);
}
header("content-type: image/png");
imagepng($im);
?> 