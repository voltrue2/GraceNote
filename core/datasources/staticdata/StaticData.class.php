<?php

class StaticData {
	
	private $cache;
	private $conf;
	private $srcPath;
	private $csvRules;

	/**
	* Configurations
	* NameOfYourChoice will be $confName used in the constructor
	* "NameOfYourChoice": {
	*	"sourcePath": "path/to/your/static/files/directory/"
	*	"csvParseRules": { "delimiter": ",", "enclosure": "\"" }
	* }
	*/
	public function __construct($confName, $cacheConfName) {
		$this->cache = new Cache($cacheConfName);
		$conf = Config::get($confName);
		if (!$conf) {
			Log::error('[STATICDATA] constructor: missing configurations');
		}
		if (!isset($conf['sourcePath'])) {
			Log::error('[STATICDATA] constructor: missing configuration "sourcePath"');
		} else {
			$this->srcPath = $conf['sourcePath'];
		}
		if (!isset($conf['csvParseRules'])) {
			Log::error('[STATICDATA] constructor: missing configuration "csvRules"');
		} else {
			$this->csvRules = $conf['csvParseRules'];
		}
	}

	public function getOne($fileNames, $index = 0) {
		$data = $this->getData($fileNames);
		if ($data && isset($data[$index])) {
			return $data[$index];
		}
		return null;
	}

	public function getMany($fileNames) {
		return $this->getData($fileNames);
	}

	private function getAllPaths($fileNames) {
		$fs = new FileSystem($this->srcPath);
		$pathList = array();
		if (!is_array($fileNames)) {
			$fileNames = array($fileNames);
		}
		for ($i = 0, $len = count($fileNames); $i < $len; $i++) {
			$path = $fileNames[$i];
			if (is_dir($this->srcPath . $path)) {
				$list = $fs->listAllFiles($path);
				if (!empty($list)) {
					for ($j = 0, $jen = count($list); $j < $jen; $j++) {
						$item = $list[$j];
						$pathList[] = $item['directoryPath'] . $item['name'];
					}
				}
			} else {
				$pathList[] = $path;
			}
		}
		return $pathList;
	}

	private function getData($fileNames) {
		$startTime = microtime(true);
		$fileNames = $this->getAllPaths($fileNames);
		try {
			$dataList = array();
			for ($i = 0, $len = count($fileNames); $i < $len; $i++) {
				$filePath = $this->srcPath . $fileNames[$i];
				$mtime = filemtime($filePath); 
				// try cache
				$key = '_SD_:' . $filePath . $mtime;
				$data = $this->cache->get($key);
				if (!$data) {
					// no cache
					$data = $this->readFile($fileNames[$i]);
					if ($data) {
						// set cache
						$this->cache->set($key, $data);
						// add data to list
						$dataList[] = $data;
					}
				} else {
					// there is cache
					$dataList[] = $data;
				}
			}
			$mergedData = $this->mergeData($dataList);
			$endTime = microtime(true);
			$time = (string)substr((($endTime - $startTime) * 1000), 0, 8);
			Log::verbose('[STATICDATA] getData: ' . implode(',', $fileNames) . ' took [' . $time. ' msec] to execute');
			return $mergedData;
		} catch (Exception $e) {
			Log::error('[STATICDATA] getData: ' . $e->getMessage());
			return null;
		}
	}

	private function readFile($filePath) {
		// check if it is a directory
		if (is_dir($this->srcPath . $filePath)) {
			// directory
			$fs = new FileSystem($this->srcPath);
			$fileList = $fs->listAllFiles($filePath);
			$dataList = array();
			for ($i = 0, $len = count($fileList); $i < $len; $i++) {
				$dataList[$i] = $this->handleData($fileList[$i]['path']);
			}
			return $mergedData = $this->mergeData($dataList);
		} else {
			// file
			return $this->handledata($this->srcPath . $filePath);
		}
	}

	private function mergeData($dataList) {
		$mergedData = array();
		for ($i = 0, $len = count($dataList); $i < $len; $i++) {
			$rlen = max(count($mergedData), count($dataList[$i]));
			for ($rows = 0; $rows < $rlen; $rows++) { 
				$data1 = (isset($mergedData[$rows])) ? $mergedData[$rows] : array();
				$data2 = (isset($dataList[$i][$rows])) ? $dataList[$i][$rows] : array();
				$mergedData[$rows] = $this->uniqueMerge($data1, $data2);
			}
		}
		return $mergedData;
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

	private function handleData($path) {
		$data = file_get_contents($path);
		$fileType = pathinfo($path, PATHINFO_EXTENSION);
		if ($fileType === 'csv') {
			$data = $this->csvToJson($data);
		}
		return $data;
	}

	private function csvToJson($content) {
		$delimiter = '/' . $this->csvRules['delimiter'] . '/';
		$enclosure = $this->csvRules['enclosure'];
		$rows = preg_split('/\r\n+|\r+|\n+|\t+/i', $content, -1, PREG_SPLIT_NO_EMPTY);
		$res = array();
		if ($rows) {
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
