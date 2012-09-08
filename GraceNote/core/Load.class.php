<?php 
class Load {

	private static $loaded_list = array();
	private static $vars = false;
	
	public static function loaded(){
		if (DISPLAY_ERRORS){
			if (!empty(self::$loaded_list)){
				echo('<p style="font-size: 12px; color: #000000;">*************************<br />');
				foreach (self::$loaded_list as $path => $bool){
					echo('Load::loaded > '.$path.'<br />');
				}
				echo('*************************</p><br />');
			}
			else {
				echo('<p style="font-size: 12px; color: #000000;">Load::loaded > null</p>');
			}
		}
		else {
			if (!empty(self::$loaded_list)){
				error_log('*************************');
				foreach (self::$loaded_list as $path => $bool){
					error_log('Load::loaded > '.$path);
				}
				error_log('*************************');
			}
			else {
				error_log('Load::loaded > null');
			}
		}
	}
	
	public static function set_vars($vars){
		self::$vars = $vars;
	}
	
	public static function action($path, $check = false){
		return Load::loader(ACTIONS_PATH.$path, $check);
	}
	
	public static function batch($path, $check = false){
		return Load::loader(BATCH_PATH.$path, $check);
	}
	
	public static function model($path, $check = false){
		return Load::loader(MODELS_PATH.$path, $check);
	}
	
	public static function lib($path, $check = false){
		return Load::loader(LIB_PATH.$path, $check);
	}
	
	public static function core($path, $check = false){
		return Load::loader(CORE_PATH.$path, $check);
	}
	
	public static function config($path){
		include(BASE_PATH.'configs/'.$path.'.php');
	}
	
	public static function template($path){
		extract(self::$vars);
		include(self::$vars['TPL_PATH'].$path.TPL_TYPE);
	}
	
	private static function loader($path, $check){
		$path .= PHP_EXTENSION;
		if (Load::check_loaded($path)){
			if ($check){
				if (file_exists($path)){
					require_once($path);
					return true;
				}
				else {
					return false;
				}
			}
			else {
				require_once($path);
				return true;
			}
		}
		else {
			return false;
		}
	}
	
	private function check_loaded($path){
		if (!isset(self::$loaded_list[$path])){
			self::$loaded_list[$path] = 1;
			return true;	
		}
		else {
			Load::warning('::check_loaded > '.$path.' has already been loaded -> loading ignored.');
			return false;
		}
	}
	
	private function warning($msg){
		if (ERROR_LOG){
			if (DISPLAY_ERRORS){
				//echo('<span style="font-size: 12px; color: #CC0000">*** Load Class > '.$msg.'</span><br />');
				/*
				echo('====================<br />GraceNote Framework backtrace<br />====================');
				echo('<pre>');
				var_dump(debug_backtrace());
				echo('</pre>');
				*/
			}
			else {
				//error_log('*** Load Class > '.$msg);
				/*
				error_log('====================');
				error_log('GraceNote Framework backtrace');
				error_log('====================');
				error_log(print_r(debug_backtrace(), true));
			`	*/
			}
		}
	}
}
?>
