<?php 

require_once('JSMin.class.php');

class Asset {

	private static $conf = null;
	private static$cache = null;

	/*
	* Configuration
	*
	"Asset": {
		"embedPaths": {
			"js": "/path/to/my/js/files/",
			"css": "/path/to/my/css/files/",
			"media": "/path/to/my/media/files/"
		},
		"httpUrls": {
			"myCustomName": {
				"protocol": "http or https",
				"host": "myDomain",
				"sourcePath": "/file/server/path/to/files/"
			},
			"myCustomName2": {
				"protocol": "http or https",
				"host": "myDomain",
				"sourcePath": "/file/server/path/to/files/"
			}...
		}
	}
	*/

	// never call this method outside
	public static function setConf($conf) {
		self::$conf = $conf;
		self::$cache = new Cache();
	}
	
	// embeds minified a JavaScript file or if directory path is given as path, all matching files
	public static function js($key, $path) {
		if (isset(self::$conf['embedPaths']) && isset(self::$conf['embedPaths'][$key])) {
			return self::getAsset(self::$conf['embedPaths'][$key] . $path, 'js');
		}
		Log::warn('[ASSET] failed to embed file > ' . $key . ' >> ' . $path);
		return '';
	}
	
	// embeds minified a CSS file or if directory path is given as path, all matching files
	public static function css($key, $path) {
		if (isset(self::$conf['embedPaths']) && isset(self::$conf['embedPaths'][$key])) {
			return self::getAsset(self::$conf['embedPaths'][$key] . $path, 'css');
		}
		return '';
	}
	
	// embeds a base64 encoded media file
	public static function media($key, $path) {
		if (isset(self::$conf['embedPaths']) && isset(self::$conf['embedPaths'][$key])) {
			return self::getAsset(self::$conf['embedPaths'][$key] . $path, 'media');
		}
		return '';
	}

	// maps files: Usage > $imgMap = Asset::map('image', '/img/'); $view->assign('imageMap', $imgMap); w/ Loader::jsVars();
	public static function map($httpUrlName, $path) {	
		if (isset(self::$conf['httpUrls'])) {
			$urls = self::$conf['httpUrls'];
			if (isset($urls[$httpUrlName])) {
				$conf = $urls[$httpUrlName];
				$domain = $conf['protocol'] . '://' . $conf['host'];
				$srcPath = $conf['sourcePath'];
				// read all files in the given path and map them
				return self::mapAssets($domain, $srcPath, $path);
			} else {
				Log::warn('[ASSET] map > "' . $httpUrlName. '" not found', $urls);
			}
		} else {
			Log::warn('[ASSET] map > no httpUrls provided in config file');
		}
		return null;
	}
	
	private static function getAsset($path, $type) {
		$cacheKey = null;
		if (is_dir($path) && $type !== 'media') {
			$file = self::handleDir($path, $type);
		} else {
			// try cache first
			$modtime = filemtime($path);
			$cacheKey = '__ASSET:' . $path . $modtime;
			$file = self::$cache->get($cacheKey);
			if ($file) {
				return $file;
			}
			$file = file_get_contents($path);
		}
		// treat file according to type
		switch ($type) {
			case 'js':
				$file = JSMin::minify($file);
				break;
			case 'css':
				$file = preg_replace('/\r\n+|\r+|\n+|\t+/i', '', $file);
				break;
			case 'media':
				$file = base64_encode($file);
				break;	
			default:
				break;
		}
		if ($file && $cacheKey) {
			// set cache
			self::$cache->set($cacheKey, $file);
		}
		return $file;
	}

	private static function handleDir($path, $type) {
		$fs = new FileSystem($path);
		$allFiles = $fs->listAllFiles();
		$file = '';
		$seen = array();
		for ($i = 0, $len = count($allFiles); $i < $len; $i++) {
			$ext = pathinfo($allFiles[$i]['name'], PATHINFO_EXTENSION);
			if ($ext === $type && !isset($seen[$allFiles[$i]['path']])) {
				$file .= self::getAsset($allFiles[$i]['path'], $type);
				$seen[$allFiles[$i]['path']] = true;
			} else {	
				Log::debug('[ASSET] handleDir > already seen "' . $allFiles[$i]['path'] . '" >> ignored');
			}
		}
		unset($seen);
		return $file;
	} 
	
	private static function mapAssets($domain, $srcPath, $path) {
		$start = microtime(true);
		$modtime = filemtime($srcPath . $path);
		$cacheKey = $srcPath . $path . $modtime;
		// try cache first
		$map = self::$cache->get($cacheKey);
		if (!$map) { 
			$fs = new FileSystem($srcPath);
			$allFiles = $fs->listAllFiles($path);
			for ($i = 0, $len = count($allFiles); $i < $len; $i++) {
				$keyPath = str_replace($srcPath, '', $allFiles[$i]['path']);
				$key = substr($keyPath, 0, strrpos($keyPath, '.'));
				$key = str_replace($path, '', $key);
				$first = substr($key, 0, 1);
				if ($first === '/') {
					$key = substr($key, 1, strlen($key));
				}
				$map[$key] = $domain . '/' . str_replace(' ', '%20', $keyPath);
			}
			// set cache
			if ($map) {
				self::$cache->set($cacheKey, $map);
			}
		}
		$end = microtime(true);
		$time = (string)substr((($end - $start) * 1000), 0, 8);
		Log::debug('[Asset] map > mapping all assets in "' . $srcPath . $path . '" took [' . $time . ' msec]');
		return $map;
	}
}

// set up Asset class with configurations
Asset::setConf(Config::get('Asset'));

?>
