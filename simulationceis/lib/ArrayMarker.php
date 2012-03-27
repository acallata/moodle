<?php
class ArrayMarker{
	public function getSubpart($content, $marker)  {
                 $start = strpos($content, $marker);
                 if ($start===false)     { return ''; }
                 $start += strlen($marker);
                 $stop = strpos($content, $marker, $start);
                         // Q: What shall get returned if no stop marker is given /*everything till the end*/ or nothing
                 if ($stop===false)      { return /*substr($content, $start)*/ ''; }
                 $content = substr($content, $start, $stop-$start);
                 $matches = array();
                 if (preg_match('/^([^\<]*\-\-\>)(.*)(\<\!\-\-[^\>]*)$/s', $content, $matches)===1)      {
                         return $matches[2];
                 }
                 $matches = array();
                 if (preg_match('/(.*)(\<\!\-\-[^\>]*)$/s', $content, $matches)===1)     {
                         return $matches[1];
                 }
                 $matches = array();
                 if (preg_match('/^([^\<]*\-\-\>)(.*)$/s', $content, $matches)===1)      {
                         return $matches[2];
                 }
                 return $content;
    }

	public function substituteMarkerArray($content,$markContentArray,$wrap='',$uppercase=0){
		if (is_array($markContentArray)){
			reset($markContentArray);
			$wrapArr=$this->trimExplode('|', $wrap);
			while(list($marker,$markContent)=each($markContentArray)){
				if($uppercase)  $marker=strtoupper($marker);
				if(strcmp($wrap,''))            $marker=$wrapArr[0].$marker.$wrapArr[1];
				$content=str_replace($marker,$markContent,$content);
			}
		}
		return $content;
	}

	public function trimExplode($delim, $string, $onlyNonEmptyValues=0)    {
                 // This explodes a comma-list into an array where the values are parsed through trim();
                 $temp = explode($delim,$string);
                 $newtemp=array();
                 while(list($key,$val)=each($temp))      {
                         if (!$onlyNonEmptyValues || strcmp("",trim($val)))      {
                                 $newtemp[]=trim($val);
                         }
                 }
                 reset($newtemp);
                 return $newtemp;
   }
}
?>