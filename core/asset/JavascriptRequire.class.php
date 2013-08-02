<?php
class JavascriptRequire {

	private $seenList = array();
	
	/*
	* Javascript require syntax: $require('pathToMyJsFile.js');
	*
	*/
	public static function parse($source) {
		return $source;	
		$pattern = '/\$require\((.*)\)\;/';
		$callback = create_function(
			'$matched',
			'$path = preg_replace("/(\'|\")/", "", $matched[1]);
			Log::debug(self::$seenList, $path);
			if (isset(self::$seenList[$path])) {
				return \'\';
			}
			try {
				$file = file_get_contents($path);
				self::$seenList[$path] = 1;
				return self::parse($file);
			} catch (Exception $e) {
				Log::error(\'JavascriptRequire::replaceMatched > \', $e->getMessage());
				return \'\';
			}'
		);
		//return mb_ereg_replace_callback($pattern, $callback, $source);
	} 
}
