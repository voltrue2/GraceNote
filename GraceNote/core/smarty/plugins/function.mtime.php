<?php
/*
 * Smarty plugin
 * ---------------------
 * File:     function.mtime.php
 * Type:     function
 * Name:     mtime
 * Purpose:  adds a modtime to the file path for file cache handling
 * ---------------------
 */
function smarty_function_mtime($args, &$smarty){
       if (isset($args["path"])){
		$type = substr($args["path"], 0, strpos($args["path"], "|"));
		$path = substr($args["path"], strlen($type) + 1);
		if ($type == 'CSS'){
			$type = CSS_PATH;
		}
		else if ($type == 'JS'){
			$type = JS_PATH;
		}
		else if ($type == 'IMG'){
			$type = IMG_PATH;
		}
		if (file_exists($type.$path)){
			return "?".filemtime($type.$path);
		}
		else {
			return "";
		}	
       }
       else {
		return "";
       }
}
?>