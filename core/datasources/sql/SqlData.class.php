<?php

class SqlData {

	private $data = null;
	
	public function SqlData($queryRes) {
		$this->data = $queryRes;
	}
	
	public function getOne($index = 0) {
		if (isset($this->data[$index])) {
			return $this->data[$index];
		} else {
			return null;
		}
	}
	
	public function getMany() {
		return $this->data;
	}

	public function inflateMerge($srcKey, $targetKey, $targetObj) {
		for ($i = 0, $len = count($this->data); $i < $len; $i++) {
			$srcData = $this->getOne($i);
			if ($srcData && isset($srcData[$srcKey])) {
				$srcProp = $srcData[$srcKey];
				$res = $this->searchProp($targetKey, $srcProp, $targetObj);
				if ($res) {
					$this->data[$i][$srcKey] = $res;
				}
			}
		}
	}

	private function searchProp($key, $value, $obj) {
		$res = array();
		for ($i = 0, $len = count($obj); $i < $len; $i++) {
			$data = (isset($obj[$i])) ? $obj[$i] : null;
			if ($data && isset($data[$key]) && $data[$key] == $value) {
				$res[] = $data;
			}
		}
		return (empty($res)) ? null : $res;
	}
}

?>
