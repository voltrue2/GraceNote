<?php
// GraceNote Framework Template functions

/** 
 - if the second parameter is given      --> checks the validity of the given array value before display
 - if the second parameter is false/null --> checks the validity of the variable before display  
**/
function text($array, $index = false, $default = '', $vars = null){
	$output = false;
	if ($default === ''){
		$default = $index;
	}
	if ($index !== false){
		if (isset($array[$index])){
			if ($array[$index] !== false){
				$output = $array[$index];
			}
			else {
				$output = $default;
			}
		}
		else {
			$output = $default;
		}
	}
	else {
		if (isset($array)){
			if ($array !== false){
				$output = $array;
			}
			else {
				$output = $default;
			}
		}
		else {
			$output = $default;
		}
	}
	if (!empty($vars) && is_array($vars)){
		foreach ($vars as $key => $var){
			$output = str_replace('{'.$key.'}', $var, $output);
		}
	}
	return $output;
}

/**
 - returns file modtime with ? before the number
 - to avoid being cached
**/
function mtime($file){
	if (file_exists($file)){
		$time = filemtime($file);
		if ($time){
			return '?'.$time;
		}
		else {
			return '';
		}
	}
	else {
		return '';
	}
}

/**
 - returns truncated string
**/
function truncate($str, $limit, $tail = '...'){
	$encoding = @mb_detect_encoding($str, 'atuo');
	if (mb_strlen($str) > $limit){
		$res = mb_substr($str, 0, $limit, $encoding);
		return $res.$tail;
	}
	else {
		return $str;
	}
}

/**
- remove HTML tags
*/
function remove_html($str){
	return mb_ereg_replace('<(.|\n)*?>', '', $str);
}

/**
- escape quotes and single quotes
*/
function escape($str){
	$str = mb_ereg_replace('"', '&quot;', $str);
	$str = mb_ereg_replace("'", '&#039;', $str);
	return $str;
}

 function error($msg){
	if (ERROR_LOG){
		if (DISPLAY_ERRORS){
			echo('*** Error > '.$msg.'<br />');
		}
		else {
			error_log('*** Error > '.$msg);
		}
	}
}
	
function trace($msg){
	if (ERROR_LOG){
		if (DISPLAY_ERRORS){
			if (is_array($msg) || is_object($msg)){
				echo('<pre>');
				var_dump($msg);
				echo('</pre>');
			}
			else {
				echo($msg.'<br />');
			}
		}
		else {
			if (is_array($msg) || is_object($msg)){
				error_log(print_r($msg, true));
			}
			else {
				error_log($msg);
			}
		}
	}
}

function epoch(){
	list($msecs, $uts) = explode(' ', microtime());
	return floor(($uts + $msecs) * 1000);
}
?>
