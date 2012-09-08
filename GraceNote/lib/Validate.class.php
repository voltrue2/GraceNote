<?php
class Validate {
	
	private $encoding = "UTF-8";
	private $email_pattern = "/^[a-z-A-Z-0-9&\'\.\-_\+\!\#\%\$\&\^\(\)\[\]\<\>\|]+@[a-z-A-Z-0-9\-]+\.([a-z-A-Z-0-9\-]+\.)*+[a-z-A-Z-]{2}/is";
	
	public function Validate(){
		
	}
	
	public function setEncoding($e){
		$this->encoding = $e;
	}
	
	public function email($val){
		$res = preg_match($this->email_pattern, $val, $matches);
		if ($res){
			return true;
		}
		else {
			return false;
		}
	}
	
	public function cleanseEmail($val){
		$res = $this->email($val);
		if ($res){
			mb_internal_encoding($this->encoding);
			$email = mb_convert_kana($val, "KVas");
			$email = str_replace(" ", "", $email);
			return $email;
		}
		else {
			return false;
		}
	}
	
	public function removeHTML($str){
		$str = mb_ereg_replace('<(.|\n)*?>', '', $str);
		return $str;
	} 

	function isValidURL($url)
	{
		//return $res = preg_match('/^(http(s?):\/\/|ftp:\/\/{1})((\w+\.){1,})\w{2,}$/i', $url); // I think this regex is to check the host name only -> worng way of using the pattern 2010/09/06
		$sep = parse_url($url);
		if (isset($sep)){
			$checkme = $sep["scheme"]."://".$sep["host"];
			return preg_match('/^(http(s?):\/\/|ftp:\/\/{1})((\w+\.){1,})\w{2,}$/i', $checkme);
		}
		else {
			return false;
		}
	}

}
?>