<?php

class Loader {

	static private $paths = array(
		'root' => '',
		'core' => 'core/', 
		'datasources' => 'core/datasources/',
		'controller' => 'controller/', 
		'model' => 'model/',	
		'template' => 'template/',
		'lib' => 'lib/'
	);
	static private $root;
	static private $templateVars = null;

	// called only in core/main.php
	static public function setRoot($root) {
		foreach (self::$paths as $name => $path) {
			self::$paths[$name] = $root . $path;
		}
		self::$root = $root;
	}

	// called ONLY in View.class.php
	static public function setTemplateVars($vars) {
		self::$templateVars = $vars;
	}

	// called ONLY in core/main.php
	// loads the bootstrap file index.php from ../GraceNote/index.php, if index.php is not found, GraceNote defaults to GraceNote/index.php
	static public function index($root, $indexFile) {
		$path = self::$paths[$root] . '../';
		if (file_exists($path . $indexFile)) {
			// external index.php found
			Log::info('[LOADER] index > "' . $path . $indexFile . '" loaded');
			self::import($path, $indexFile);
		} else {
			// external index.php not found > load default index.php
			Log::info('[LOADER] index > "default index" loaded');
			self::import($root, $indexFile);
		}
	}

	// the method can be used to override existing paths such as controller
	static public function setPath($name, $path) {
		self::$paths[$name] = self::$root . $path;
	}

	static public function getPath($name) {
		return (isset(self::$paths[$name])) ? self::$paths[$name] : null;
	}
	
	// should be used in a template file
	static public function jsVars($namespace = 'window') {
		if (!empty(self::$templateVars)) {
			$start = microtime(true);
			$var = '';
			$cls = '';
			if ($namespace) {
				$cls = $namespace . '.';
			} else {
				return Log::warn('[LOADER] jsVars > namespace cannot be empty');
			}
			foreach (self::$templateVars as $key => $value) {
				if (is_array($value)) {
					$value = json_encode($value);
				} else if (is_string($value)) {
					$value = "'" . $value . "'";
				} else if (!$value) {
					$value = '""';
				}
				$value = mb_ereg_replace('<(.|\n)*?>', '', $value);
				$var .= $cls . $key . ' = ' . $value . '; ';
				Log::debug('[LOADER] jsVars: assgined > ' . $cls . $key);
			}
			$end = microtime(true);
			$time = substr((string)(($end - $start) * 1000), 0, 8);
			Log::debug('[LOADER] jsVars > converting PHP variables to JavaScript vriables took [' . $time . ' msec]');
			if ($namespace === 'window') {
				$namespace = '';
			} else {
				$namespace = '.' . $namescape;
			}
			return '<script type="text/javascript">(function () {window'. $namespace . ' = {}; ' . $var . '}());</script>';
		}
		return '';
	}

	static public function import($name, $file) {
		try {
			if (isset(self::$paths[$name])) {
				$path = self::$paths[$name] . $file;
				if (file_exists($path)) {
					require_once($path);
					return true;
				} else {
					throw new Exception('Loader::import > File does not exist "' . $path . '"');
				}
			} else {
				// $name is given as the file path
				if (file_exists($name . $file)) {
					require_once($name . $file);
					return true;
				} else {
					throw new Exception('Loader::import > Attemped to load invalid file "' . $name . '" ' . $file);
				}
			}
		} catch (Exception $e) {
			Log::error('[LOADER] import' . $e->getMessage());
			return false;
		}
	}

	static public function template($name, $file = null) {
		try {
			if (!$file) {
				$file = $name;
				$name = self::$paths['template'];
			}
			if (isset(self::$paths[$name])) {
				$path = self::$paths[$name] . $file;
				if (file_exists($path)) {
					extract(self::$templateVars);
					include($path);
					return true;
				} else {
					throw new Exception('Loader::template > File does not exist "' . $path . '"');
				}
			} else {
				// $name is given as the file path
				if (file_exists($name . $file)) {
					require_once($name . $file);
					return true;
				} else {
					throw new Exception('Loader::template > Attemped to load invalid file "' . $name . '" ' . $file);
				}
			}
		} catch (Exception $e) {
			Log::error('[LOADER] template' . $e->getMessage());
			return false;
		}
	}
}

?>
