<?php 

/****
* Configurations
* {
*	// list the name of log in "active" to enable
*	"active": [
*		"verbose",
		"debug",	
*		"info",
*		"warn",
*		"error",
		"fatal"
*	],
*	"paths": {
		"verbose": "logFilePath",
*		"debug": "logFilePath",
*		"info": "logFilePath",
*		"warn": "logFilePath",
*		"error": "logFilePath",
		"fatal": "logFilePath"
*	}
* }
*
*/

class Log {
	
	private static $verbose = null;
	private static $debug = null;
	private static $info = null;
	private static $warn = null;
	private static $error = null;
	private static $fatal = null;
	private static $br = PHP_EOL;
	private static $foregroundColors = array(
		'black' => '0;30',
		'darkGray' => '1;30',
		'lightGray' => '0;37',
		'blue' => '0;34',
		'lightBlue' => '1;34',
		'green' => '0;32',
		'lightGreen' => '1;32',
		'cyan' => '0;36',
		'lightCyan' => '1;36',
		'red' => '0;31',
		'lightRed' => '1;31',
		'purple' => '0;35',
		'lightPurple' => '1;35',
		'brown' => '0;33',
		'white' => '1;37'
	);
	private static $backgroundColors = array(
		'black' => '40',
		'red' => '41',
		'green' => '42',
		'blue' => '44',
		'magenta' => '45',
		'cyan' => '46',
		'lightGray' => '47'
	);

	public static function setVerbosePath($path) {
		self::$verbose = $path;
	}

	public static function setDebugPath($path) {
		self::$debug = $path;
	}

	public static function setInfoPath($path) {
		self::$info = $path;
	}
	
	public static function setWarnPath($path) {
		self::$warn = $path;
	}
	
	public static function setErrorPath($path) {
		self::$error = $path;
	}

	public static function setFatalPath($path) {
		self::$fatal = $path;
	}

	public static function verbose() {
		if (self::$verbose === null) {
			return;
		}
		$args = func_get_args();
		self::write(self::$verbose, '', 'verbose', $args, 'lightGray');
	}

	public static function debug() {
		if (self::$debug === null) {
			return;
		}
		$args = func_get_args();
		self::write(self::$debug, '  ', 'debug', $args, 'lightBlue');
	}
	
	public static function info() {
		if (self::$info === null) {
			return;
		}
		$args = func_get_args();
		self::write(self::$info, '   ', 'info', $args, 'green');
	}
	
	public static function warn() {
		if (self::$warn === null) {
			return;
		}
		$args = func_get_args();
		self::write(self::$warn, '   ', 'warn', $args, 'lightPurple');
	}
	
	public static function error() {
		if (self::$error === null) {
			return;
		}
		$args = func_get_args();
		self::write(self::$error, '  ', 'error', $args, 'lightRed');
	}
	
	public static function fatal() {
		error_log(self::$fatal);
		if (self::$fatal === null) {
			return;
		}
		$args = func_get_args();
		self::write(self::$fatal, '  ', 'fatal', $args, 'white', 'red');
	}
	
	private static function write($logPath, $indent, $logType, $msgList, $fcolor = null, $bcolor = null) {
		$c = count($msgList);
		for ($i = 0; $i < $c; $i++) {
			if ($logPath) {
				$msg = self::msg($indent, $logType, $msgList[$i], $i);
				error_log(self::colorize($msg, $fcolor, $bcolor), 3, $logPath);
			}
		}
	}
	
	private static function msg($indent, $type, $msg, $msgCounter) {
		$timestamp = '' . date('Y-m-d H:i:s') . substr((string)microtime(), 1, 8) . ' ' . $indent . '<' . $type . '> ';
		if ($msgCounter > 0) {
			$timestamp = self::createBar($timestamp);
		}
		if (is_array($msg)) {
			return self::parseArray('', self::createBar($timestamp), $msg);
		}
		return $timestamp . $msg . self::$br;
	}
	
	private static function parseArray($indent, $bar, $a) {
		$str = '';
		foreach ($a as $i => $item) {
			if (is_array($item)) {
				$parentKey = $i . ' => ';
				$wsp = '';
				for ($i = 0; $i < strlen($parentKey); $i++) {
					$wsp .= ' ';
				}
				$str .= $bar . $indent . $parentKey . self::$br;
				$str .= self::parseArray($indent . $wsp, $bar, $item);
			} else {
				$str .= $bar . $indent . $i . ' => ' . $item . ' (' . gettype($item) . ')' . self::$br; 
			}
		}
		return $str;
	}
	
	private static function colorize($string, $fcolor = null, $bcolor = null) {
		$cstring = '';
		// Check if given foreground color found
		if (isset(self::$foregroundColors[$fcolor])) {
			$cstring .= "\033[" . self::$foregroundColors[$fcolor] . "m";
		}
		// Check if given background color found
		if (isset(self::$backgroundColors[$bcolor])) {
			$cstring .= "\033[" . self::$backgroundColors[$bcolor] . "m";
		}
		// Add string and end coloring
		$cstring .=  $string . "\033[0m";
		error_log($cstring);
		return $cstring;
	}

	private static function createBar($timestamp) {
		$len = strlen($timestamp) - 3;
		$bar = '+  ';
		for ($i = 0; $i < $len; $i++) {
			$bar .= ' ';
		}
		return $bar;
	}
}

// get config and set up
$conf = Config::get('Log');
if ($conf) {
	$activeLogs = array();
	// log file paths
	if (isset($conf['paths'])) {
		foreach ($conf['paths'] as $name => $path) {
			switch ($name) {
				case 'verbose': 
					if (isset($conf['active']) && in_array($name, $conf['active'])) {
						Log::setVerbosePath($path);
					}
					break;
				case 'debug': 
					if (isset($conf['active']) && in_array($name, $conf['active'])) {
						Log::setDebugPath($path);
					}
					break;
				case 'info': 
					if (isset($conf['active']) && in_array($name, $conf['active'])) {
						Log::setInfoPath($path);
					}
					break;
				case 'warn': 
					if (isset($conf['active']) && in_array($name, $conf['active'])) {
						Log::setWarnPath($path);
					}
					break;
				case 'error': 
					if (isset($conf['active']) && in_array($name, $conf['active'])) {
						Log::setErrorPath($path);
					}
					break;
				case 'fatal': 
					if (isset($conf['active']) && in_array($name, $conf['active'])) {
						Log::setFatalPath($path);
					}
					break;
			}
		}
	}
}
