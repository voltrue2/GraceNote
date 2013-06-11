<?php 
class UserAgent {
	
	private static $osPattern;
	private static $browserPattern;
	private static $os;
    private static $browser;
    private static $language;    

	public static function getOs() {
		return self::$os;
	}

	public static function getBrowser() {
		return self::$browser;
	}

    public static function getLanguage() {
        return self::$language;
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
		if ($conf) {
			// construct os pattern
			self::$osPattern = '/(' . implode('|', $conf['os']) . ')/i';
			self::$browserPattern = '/(' . implode('|', $conf['browser']) . ')/i';
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
        // parse default language
        self::$language = self::getDefaultLanguage(); 
        Log::verbose('[USERAGENT] parseUserAgentSource: language > ' . self::$language);
    }

    private static function getDefaultLanguage() {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return self::parseDefaultLanguage($_SERVER['HTTP_ACCEPT_LANGUAGE']);
        } else {
            return self::parseDefaultLanguage(null);
        }
    }

    private static function parseDefaultLanguage($httpAccept, $deflang = 'en') {
        if (isset($httpAccept) && strlen($httpAccept) > 1)  {
            $x = explode(',', $httpAccept);
            foreach ($x as $val) {
                if (preg_match('/(.*);q=([0-1]{0,1}\.\d{0,4})/i', $val, $matches)) {
                    $lang[$matches[1]] = (float)$matches[2];
                } else {
                    $lang[$val] = 1.0;
                }
            }
            $qval = 0.0;
            foreach ($lang as $key => $value) {
                if ($value > $qval) {
                    $qval = (float)$value;
                    $deflang = $key;
                }
            }
        }
        return strtolower($deflang);
    }

}

UserAgent::setConfig(Config::get('UserAgent'));
UserAgent::parseUserAgentSource();

?>
