<?php
	class File{
		
		var $filePath;
		
		function __construct($filePath){
			$this->filePath = $filePath;
		}
		
		function readImage(){
			$imagen = $this->filePath;
			$b=fopen($imagen,"rb");
			$newImage = "";
			$imgData=getimagesize($imagen);
			$newImagePre="{\\*\\shppict{\\pict \\jpegblip \\picw".$imgData[0]." \\pich".$imgData[1]." \\wbmbitspixel24 ";
			while (!feof($b)) {
				$newImage.= fgets($b);
			}
			$hex=bin2hex($newImage);
			$imgDat=$newImagePre.$hex."}}";
			return $imgDat;
		}
		
		function addData($data){
			$fp = fopen($this->filePath, "a");
			$write = fputs($fp, $data);
			fclose($fp);
		}
		
		function overwrite($data){
			$fp = fopen($this->filePath, "w");
			$write = fputs($fp, $data);
			fclose($fp);
		}
		
		function read(){
			$fp = fopen($this->filePath, "r");
			$data = fread($fp, filesize($this->filePath));
			fclose($fp);
			return $data;
		}
		
		function clean(){
			$this->overWrite('');
		}
		
		function delete(){
			unlink($this->filePath); 
		}
		
		function rename($newName){
			rename($this->filePath, $newName);
		}
		
		function copy(){
			copy($this->filePath, $this->filePath.".bkp");
		}
		
		static function tempFile(){
			return tmpfile();
		}
	
	}
?>