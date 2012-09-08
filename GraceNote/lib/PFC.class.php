<?php
/****************************************************************
	Writes physical files to a specified location
	Reads physical files from a specified location
	grep/all flush physycal files
	grep/all search PFC files
****************************************************************/

class PFC {
	
	private $cn = null;
	private $common_key = ".PFC_";
	private $ignore = false;
	
	public function PFC($cn_path, $ignore_common_key = false){	
		$this->ignore = $ignore_common_key;
		$this->cn = $cn_path;
	}

	public function read($key){
		$path = $this->path($key);
		$val = @file_get_contents($path);
		if ($val){
			$data = unserialize($val);
			if ($data){
				// the read value is an array
				return $data;
			}
			else {
				// the read value is a string
				return $val;
			}
		}
		else {
			return null;
		}
	}

	public function search($str, $open_file = false){
		$handle = opendir($this->cn);
		$res = array();
		while (($file = readdir($handle)) !== false) {	
			if (fnmatch($this->wildcard($str), $file) && fnmatch($this->wildcard($this->common_key), $file)){			
				$i = count($res);
				$name = str_replace($this->common_key, "", $file);
				$res[$i]["FILE"] = trim($name);
				if ($open_file){
					$res[$i]["DATA"] = $this->read($name);
				}
			}
		}
		if (empty($res)){
			return false;
		}
		else {
			return $res;
		}
	}
	
	private function wildcard($str){
		return '*'.$str.'*';
	}

	public function write($key, $val){
		$path = $this->path($key);
		if (file_exists($path)){
			// file already exists -> remove first
			unlink($path);
		}
		// write the file
		touch($path);
		$f = fopen($path, "w+");
		// check for an array
		if (is_array($val)){
			$val = serialize($val);
		}
		fwrite($f, $val);
		fclose($f);
	}
	
	public function remove($key){
		$path = $this->path($key);
		unlink($path);
	}

	public function collect($str = "", $open_file = true){
		$handle = opendir($this->cn);
		$res = array();
		if ($handle){
			while (($file = readdir($handle)) !== false) {
				if (strpos($file, $this->common_key.$str) !== false){
					$i = count($res);
					$name = str_replace($this->common_key, "", $file);
					$res[$i]["FILE"] = trim($name);
					if ($open_file){
						$res[$i]["DATA"] = $this->read($name);
					}
				}
			}
		}
		return $res;		
	}
	
	public function flush($str = ""){
		$data = $this->collect($str);
		if (!empty($data)){
			foreach ($data as $item){
				$this->remove($item["FILE"]);
			}
		}
	}
	
	private function path($key){
		if ($this->ignore){
			return $this->cn.$key;
		}
		else {
			return $this->cn.$this->common_key.$key;
		}	
	}
}
?>
