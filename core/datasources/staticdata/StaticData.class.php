<?php
class StaticData {
	
	private $srcPath = null;
	private $fullPath = null;	
	private $csvRules = null;
	private $cache = null;
	private $cachedData = null;
	/**
	* Configurations
	* "StaticData": {
	*	"sourcePath": "path/to/your/static/files/directory/"
	*	"csvParseRules": { "delimiter": ",", "enclosure": "\"" }
	* }
	*/
	public function StaticData($filePath, $get = true, $confName = 'StaticData') {
		$conf = Config::get($confName);
		if ($conf && isset($conf['sourcePath'])) {
			$this->srcPath = $conf['sourcePath'];
			$this->cache = new Cache();
			if (isset($conf['csvParseRules']) && isset($conf['csvParseRules']['delimiter']) && isset($conf['csvParseRules']['enclosure'])) {
				$this->csvRules = $conf['csvParseRules'];
				$this->fullPath = $this->srcPath . $filePath;
				if ($get) {
					// we do not read the file 
					$this->cachedData = $this->get($filePath);
				}
				return;
			}
		}
		Log::error('StaticData::constructor > Configurations missing >>', $conf);
	}

	public function getOne($index = 0) {
		if (isset($this->cachedData[$index])) {
			return $this->cachedData[$index];
		}
		return null;
	}

	public function getMany() {
		return $this->cachedData;
	}
	
	// merge another data from StaticData
	public function merge($sdList, $index = null) {
		if (!is_array($sdList)) {
			$sdList = array($sdList);
		}
		if ($index === null) {
			$myData = $this->getMany();
			$res = array();
			for ($i = 0, $len = count($sdList); $i < $len; $i++) {
				$data = $sdList[$i]->getMany();
				for ($i = 0, $len = count($myData); $i < $len; $i++) {
					$myData[$i] = $this->uniqueMerge($myData[$i], ($data[$i]) ? $data[$i] : null);
				}
			}
			return $myData;
		} else {
			$myData = $this->getOne($index);
			for ($i = 0, $len = count($sdList); $i < $len; $i++) {
				$data = $sdList[$i]->getOne($index);
				for ($i = 0, $len = count($myData); $i < $len; $i++) {
					$myData = $this->uniqueMerge($myData, $data);
				}
			}
			return $myData;
		}
	}
	
	private function uniqueMerge($data1, $data2) {
		if (!is_array($data1) && is_array($data2)) {
			return $data2;
		} else if (!is_array($data2) && is_array($data1)) {
			return $data1;
		} else if (!is_array($data1) && !is_array($data2)) {
			return null;
		}
		foreach ($data2 as $key => $value) {
			if (!isset($data1[$key])) {
				$data1[$key] = $value;
			}
		}
		return $data1;
	}
	
	public function getSource() {
		$timestamp = filemtime($this->fullPath);
		$key = 'SDS' . str_replace('/', '', $this->fullPath) . $timestamp;
		// try cache first
		$content = $this->cache->get($key);
		if (!$content) {
			$content = file_get_contents($this->fullPath);
			if ($content) {
				// set cache
				$this->cache->set($key, $content);
			}
		}
		return $content;
	}
	
	public function getSourcePath() {
		return $this->srcPath;
	}

	public function set($value, $mod = 0600) {
		$path = $this->fullPath;
		$resource = fopen($path, 'w');
		if ($resource) {
			if (is_array($value) || is_object($value)) {
				$value = json_encode($value);
			}
			$success = fwrite($resource, $value);
			fclose($resource);
			chmod($path, $mod);
			Log::info('[STATICDATA] set > "' . $path . '" >> ' . $value . '[success: ' . (($success) ? 'true' : 'false') . ']');
			return $success;
		}
		Log::warn('[STATICDATA] set > failed to set > ' . $path);
		return false;	
	}

	private function get($filePath) {
		$path = $this->srcPath . $filePath;
		$timestamp = filemtime($path);
		$key = $path . $timestamp;
		$content = $this->cache->get($key);
		if (!$content) {
			// there is no cache read the file
			$content = file_get_contents($path);
			if ($content) {
				// check file type				
				$fileType = pathinfo($filePath, PATHINFO_EXTENSION);
				if ($fileType === 'csv') {
					// we need to parse this into a JSON
					$content = $this->csvToJson($content);
				} else if ($fileType === 'json') {
					$content = json_decode($content, true);
				}
				// set cache
				$this->cache->set($key, $content);
			}
		}
		return $content;
	} 

	private function csvToJson($content) {
		$delimiter = '/' . $this->csvRules['delimiter'] . '/';
		$enclosure = $this->csvRules['enclosure'];
		$rows = preg_split('/\r\n+|\r+|\n+|\t+/i', $content, -1, PREG_SPLIT_NO_EMPTY);
		if ($rows) {
			$res = array();
			$keys = preg_split($delimiter, mb_ereg_replace($enclosure, '', $rows[0]), -1, PREG_SPLIT_NO_EMPTY); // use first row as keys
			$total = count($rows);
			$index = 0;
			for ($i = 1; $i < $total; $i++) {
				$columns = preg_split($delimiter, $rows[$i], -1, PREG_SPLIT_NO_EMPTY);
				$c = count($columns);
				$valueCounter = 0;
				for ($j = 0; $j < $c; $j++) {
					$key = $keys[$j];
					$value = mb_ereg_replace($enclosure, '', $columns[$j]);
					if (isset($key) && isset($value)) {
						$valueCounter += 1;
						if (is_numeric($value)) {
							$value = intval($value);
						}
						$res[$index][$key] = $value;
					}
				}
				if ($valueCounter === $c) {
					$index += 1;
				}
			}
		}
		return $res;
	}	
}
?>
