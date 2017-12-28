<?php
 class ImageBase64 {
	function Imgbase($photouri){
		$fp=fopen($photouri,"r")or die("Can't open file");  
		$file_content=chunk_split(base64_encode(fread($fp,filesize($photouri))));//base64БрТы 
		return $file_content;
	}
 }
?>