<?php 
class Base {
	
	private $header_codes = array(
		300 => '300 Multiple Choices',
		301 => '301 Moved Permanently',
		302 => '302 Found',
		403 => '403 Forbidden',
		404 => '404 Not Found',
		500 => '500 Internal Server Error',
		503 => '503 Service Unavailable'
	);
	
	public function Base(){

	}
	
	public function header($code){
		if (isset($this->header_codes[$code])){
			return $this->header_codes[$code];
		}
		else {
			return false;
		}
	}
	
	public function error($msg){
		if (ERROR_LOG){
			if (DISPLAY_ERRORS){
				echo('*** Error > '.$msg.'<br />');
			}
			else {
				error_log('*** Error > '.$msg);
			}
		}
	}

	public function redirect($path, $alt_code = false){
		if (!$alt_code){
			header('HTTP/1.1 '.$this->header_codes[301]);
		}
		else if (isset($this->header_codes[$alt_code])){
			header('HTTP/1.1 '.$this->header_codes[$alt_code]);
		}
		header('Location: '.$path);
		exit();
	}
	
	public function get_session($key = false){
		if (!$key){
			return $_SESSION;		
		}
		else {
			if (isset($_SESSION[$key])){
				return $_SESSION[$key];
			}
			else {
				return false;
			}
		}
	}
	
	public function set_session($key, $value){
		try {
			$_SESSION[$key] = $value;
			return true;
		}
		catch (Exception $e){
			return false;
		}
		
	}
	
	public function remove_session($key){
		if (isset($_SESSION[$key])){
			$_SESSION[$key] = null;
			return true;
		}
		else {
			return false;
		}
	}
	
	public function set_cookie($key, $value, $secure = false){
		if (is_array($value)){
			$value = serialize($value);
		}
		setcookie($key, $value, time() + (3600 * COOKIE_EXPIRATION), '/', '.'.HOST, $secure);
	}

	public function get_cookie($key){
		if (isset($_COOKIE[$key])){
			$res = $_COOKIE[$key];
			$s = @unserialize($res);
			if ($s){
				return $s;
			}
			else {
				return $res;
			}
		}
		else {
			return false;
		}
	}
	
	public function server($key){
		if (isset($_SERVER[$key])){
			return $_SERVER[$key];
		}
		else {
			return false;
		}
	}
	
	public function array_value($array, $key){
		if (isset($array[$key])){
			return $array[$key];
		}
		else {
			return false;
		}
	}

	private function get_caller(){
		$trace_src = debug_backtrace();
		$trace = array_shift($trace_src);
		$meta = $trace['file'].' at line ('.$trace['line'].') ';
		if (isset($trace['class']) && isset($trace['function'])){
			// called by an object method
			return $meta.$trace['class'].'::'.$trace['function'];
		}
		else if (!isset($trace['class']) && isset($trace['function'])){
			// called by a function
			return $meta.$trace['function'];
		}
		else {
			// called by something else
			return $meta;
		}
	}
}
?>
