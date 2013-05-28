<?php
class FileSystem {
	
	private $path = '';
	private $cache;
	
	public function FileSystem($path) {
		$this->path = $path;
		$this->cache = new Cache();
	}
	
	public function getSourcePath() {
		return $this->path;
	}

	public function getFullPath($path) {
		$prefix = $this->path;
		$len = strlen($prefix) - 1;
		$prefixLast = $prefix[$len];
		$pathFirst = $path[0];
		if ($prefixLast !== '/' && $pathFirst !== '/') {
			$prefix .= '/';
		} else if ($prefixLast === '/' && $pathFirst === '/') {
			$prefix = substr($prefix, 0, $len);
		}
		return $prefix . $path;
	}

	public function readFile($path) {
		$fullPath = $this->getFullPath($path);
		$key = '__FSR' . $path . filemtime($fullPath);
		$data = $this->cache->get($key);
		if (!$data) {
			$data = file_get_contents($fullPath);
			if ($data) {
				$this->cache->set($key, $data);
			}
		}
		return $data;
	}

	public function getFullPaths($pathList) {
		$prefix = $this->path;
		$len = strlen($prefix) - 1;
		$prefixLast = $prefix[$len];
		for ($i = 0, $len = count($pathList); $i < $len; $i++) {
			$pathFirst = $pathList[$i][0];
			if ($prefixLast !== '/' && $pathFirst !== '/') {
				$prefix .= '/';
			} else if ($prefixLast === '/' && $pathFirst === '/') {
				$prefix = substr($prefix, 0, $len);
			}
			$pathList[$i] = $prefix . $pathList[$i];
		}
		return $pathList;
	}
	
	public function listFiles($dirPath = '') {
		if (substr($dirPath, 0, 1) === '/' && substr($this->path, strlen($this->path) - 1, 1) === '/') {
			$dirPath = substr($dirPath, 1, strlen($dirPath));
		}
		$path = $this->path . $dirPath;
		$cacheKey = '__FS:' . $path . filemtime($path);
		// try cache first
		$list = $this->cache->get($cacheKey);
		if (!$list) {
			// no cache
			if (substr($path, strlen($path) - 1, 1) !== '/') {
				$path .= '/';
			}
			$dirSrc = opendir($path);
			if ($dirSrc) {
				while ($entry = readdir($dirSrc)) {
					if ($entry !== '.' && $entry !== '..') {
						$isDir = is_dir($path . $entry);
						$uri = $dirPath . $entry;
						if (substr($uri, 0, 1) !== '/') {
							$uri = '/' . $uri;
						}
						$list[] = array(
							'uri' => $uri, 
							'directoryPath' => $dirPath, 
							'path' => $path . $entry, 
							'name' => $entry, 
							'isDir' => $isDir,
							'modtime' => filemtime($path . $entry) 
						);
					}
				}
				if (!empty($list)) {
					// set cache
					$this->cache->set($cacheKey, $list);
				}
			} else {
				Log::warn('[FIILESYSTEM] listFiles > given path is not a directory >> ' . $path);
			}
		}
		return (!$list) ? array() : $list;
	}
	
	public function listAllFiles($dir = '') {
		$allFiles = array();
		$list = $this->listFiles($dir);
		for ($i = 0, $len = count($list); $i < $len; $i++) {
			$item = $list[$i];
			if ($item['isDir']) {
				$moreList = $this->listAllFiles($dir . '/' . $item['name']);
				if (!empty($moreList)) {
					$allFiles = array_merge($allFiles, $moreList);
				}
			} else {
				$allFiles[] = $item;
			}
		}
		return $allFiles;
	}
}
?>
