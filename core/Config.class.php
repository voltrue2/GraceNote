<?php

class Config {
	
	static private $configSrc;
	
	public static function parse($root) {
		// try JSON first
		$res = self::parseJsonConfig($root);
		if ($res) {
			return self::$configSrc = $res;
		}
		exit('Failed to load configuration file correctly');
	}

    // loads an extra config file (JSON)
    // call this method in your index.php
    static private function load($path) {
        if (file_exists($path)) {
            try {
                $config = json_decode(file_get_contents($path), true);
                foreach ($config as $key => $value) {
                    self::$configSrc[$key] = $value;
                }
                return true;
            } catch (Exception $e) {
                Log::error('load: failed to parse configuration file > ' . $path);
            }
        }
        return false;
    }

	private static function parseJsonConfig($root) {
		if (file_exists($root . '../configs/config.json')) {
			// load external config.json from ../GraceNote/configs/config.json
			return json_decode(file_get_contents($root . '../configs/config.json'), true);
		}
		// load default config.json from GraceNote/configs/config.json
		if (!file_exists($root . 'configs/config.json')) {
			return false;
		}
		return json_decode(file_get_contents($root . 'configs/config.json'), true);
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
