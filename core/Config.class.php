<?php

class Config {
	
	static private $configSrc;
	
	public static function parse($root) {
		// try JSON first
		$res = self::parseJsonConfig($root);
		if ($res) {
			return self::$configSrc = $res;
		}
		/* TODO: not functioning correctly
		// try XML next
		$res = self::parseXmlConfig($root);
		if ($res) {
			return self::$configSrc = $res;
		}
		*/
		// all failed
		//trigger_error('[CONFIG] parse: failed to load config file correctly at ' . $root . 'configs/', E_USER_ERROR);
		exit('Failed to load configuration file correctly');
	}
	
	private static function parseJsonConfig($root) {
		if (!file_exists($root . 'configs/config.json')) {
			return false;
		}
		return json_decode(file_get_contents($root . 'configs/config.json'), true);
	}
	
	private static function parseXmlConfig($root) {
		if (!file_exists($root . 'configs/config.xml')) {
			return false;
		}
		return json_decode(json_encode(simplexml_load_file($root . 'configs/config.xml')), true);
	}

	public static function get($cls) {
		if (isset(self::$configSrc) && isset(self::$configSrc[$cls])) {
			return self::$configSrc[$cls];
		} else {
			return null;
		}
	}
	
	public static function getAll() {
		return self::$configSrc;
	}

}

?>
