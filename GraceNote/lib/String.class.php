<?php

class String{
	
	private $def;
	private $encoding = "UTF-8";
	private $matched = array();
	private $exceptions = array();
	
	public function String($def){
		if (!is_array($def)){
			$this->def[] = $def;
		}
		else {
			$this->def = $def;
		}
	}

	public function setException($e){
		if ($e){
			if (is_array($e)){
				foreach ($e as $i){
					if ($i){
						$this->exceptions[strtolower($i)] = true;
					}
				}
			}
			else {
				$this->exceptions[strtolower($e)] = true;
			}
		}
	}
	
	public function removeException($e){
		if ($e){
			if (is_array($e)){
				foreach ($e as $i){
					if (isset($this->exceptions[strtolower($i)])){
						unset($this->exceptions[strtolower($i)]);
					}
				}
			}
			else {
				if (isset($this->exceptions[strtolower($e)])){
					unset($this->exceptions[strtolower($e)]);
				}
			}
		}
	}
	
	public function clearException(){
		$this->exception = array();
	}

	public function insert($s, $e, $str){
		$res = $this->search($str, $s, $e);
		return $res["replaced"];
	}
	
	public function replace($rep, $str){
		$res = $this->search($str, $rep, "", "replace");
		return $res["replaced"];
	}
	
	public function extract($str){
		$res = $this->search($str, "{", "}");
		return $res["matched"];
	}
	
	public function matched(){
		return $this->matched;
	}

	private function search($str, $rep_s = "", $rep_e = "", $type = null){
 		$tmp = $str;
 		$current_e = mb_internal_encoding();
 		$matched = array();
 		mb_internal_encoding($this->encoding);
 		foreach($this->def as $item){
 			if ($item && !isset($this->exceptions[strtolower($item)])){
	 			$prev = $tmp;
	 			if ($type == "replace"){
	 				$tmp = mb_eregi_replace($item, $rep_s, $tmp);
	 			}
	 			else {
	 				$tmp = mb_eregi_replace($item, $rep_s.$item.$rep_e, $tmp);
	 			}
	 			if ($tmp !== $prev){
	 				$matched[] = $item;
	 			}
 			}
 		} 
		mb_internal_encoding($current_e);
		if (empty($matched) || count($matched) <= 0){
			$matched = false;
		}
		$this->matched = $matched;
		return array("replaced" => $tmp, "matched" => $matched);
 	}
}
?>