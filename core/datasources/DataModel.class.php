<?php 
class DataModel {
	
	private $type;
	private $confName;
	private $read = '';
	private $write = '';
	
	public function DataModel($confName){
		try {
			$this->confName = $confName;
			$sqlConf = SqlConfig::get($confName);
			if ($sqlConf) {
				// sql
				$this->type = 'sql';
				$this->read = $sqlConf['read'];
				$this->write = $sqlConf['write'];
				
			} else {
				$conf = Config::get($confName);
				if ($conf) {
					if (isset($conf['sourcePath']) && isset($conf['csvParseRules'])) {
						// file data
						$this->type = 'file';
					}
				} else {
					Log::error('DataModel::constructor >> missing configuration for ' . $confName);
					throw new Exception('DataModel::constructor >> missing configuration for ' . $cofName);
				}
			}
		} catch (Exception $e) {
			Log::error('[DATAMODEL]', $e->getMessage());
		}
	}

	public function inflateMerge($srcObj, $srcKey, $targetObj, $targetKey) {
		// multi-dimensional array: array(array('prop' => 1, 'prop2' => 2), array('prop' => 100, 'prop2' => 200));
		for ($i = 0, $len = count($srcObj); $i < $len; $i++) {
			$srcData = $srcObj[$i];
			if ($srcData && isset($srcData[$srcKey])) {
				$srcProp = $srcData[$srcKey];
				$res = $this->searchProp($targetKey, $srcProp, $targetObj);
				if ($res) {
					$srcObj[$i][$srcKey] = $res;
				}
			}
		}
		return $srcObj;
	}

	public function table($table = null){
		if ($this->type !== 'sql') {
			return $this->notAvailable('table');
		}
		if ($table) {
			return new QueryBuilder($this->read, $this->write, $table);
		}
		return new QueryBuilder($this->read, $this->write, '$__anonymous__');
	}

	public function staticData() {
		if ($this->type !== 'file') {
			return $this->notAvailable('staticData');
		}
		return new FileData($this->confName); 
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

	private function notAvailable($method) {
		Log::warn('[DATAMODEL] method "' . $method . '" is not available for type (' . $this->type . ')');
		return null;
	}
}
?>
