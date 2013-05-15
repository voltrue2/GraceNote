<?php 
class UserAgent {
	
	private static $conf;
	private static $osPattern;
	private static $browserPattern;
	private static $os;
	private static $browser;	

	public static function getOs() {
		return self::$os;
	}

	public static function getBrowser() {
		return self::$browser;
	}

	public static function isOs($os) {
		if (strtolower($os) === strtolower(self::$os)) {
			return true;
		}
		return false;
	} 

	public static function isBrowser($browser) {
		if (strtolower($browser) === strtolower(self::$browser)) {
			return true;
		}
		return false;
	}

	public static function setConfig($conf) {
		self::$conf = $conf;
		if (self::$conf) {
			// construct os pattern
			$pattern = '/(';
			$total = count($conf['os']) - 1;
			$i = 0;
			foreach ($conf['os'] as $name => $item) {
				$end = '|';
				if ($i === $total) {
					$end = '';
				}
				$i += 1;
				$pattern .= $conf['os'][$name] . $end;
			}
			self::$osPattern = $pattern . ')/i';
			// construct browser pattern
			$pattern = '/(';
			$total = count($conf['browser']) - 1;
			$i = 0;
			foreach ($conf['browser'] as $name => $item) {
				$end = '|';
				if ($i === $total) {
					$end = '';
				}
				$i += 1;
				$pattern .= $conf['browser'][$name] . $end;
			}
			self::$browserPattern = $pattern . ')/i';
			return; 
		}
		Log::warn('[USERAGENT] setConfig: No configurations');
	}

	public static function parseUserAgentSource() {
		$auSrc = $_SERVER['HTTP_USER_AGENT'];
		// parse os
		$res = preg_match_all(self::$osPattern, $auSrc, $matched);
		self::$os = null;
		if ($res) {
			self::$os = $matched[0][count($matched[0]) - 1];	
		}
		Log::verbose('[USERAGENT] parseUserAgentSource: os > ' . self::$os);
		// parse browser
		$res = preg_match(self::$browserPattern, $auSrc, $matched);
		self::$browser = null;
		if ($res) {
			self::$browser = $matched[count($matched) - 1];
		}
		Log::verbose('[USERAGENT] parseUserAgentSource: browser > ' . self::$browser);
	}
}

UserAgent::setConfig(Config::get('UserAgent'));
UserAgent::parseUserAgentSource();

?>
