<?php
// this class should never be used outside of this file
class ErrorHandler {
	private static $cnt;
	private static $staticPage;

	public static function setController($cnt) {
		self::$cnt = $cnt;
	}
	
	public static function setStaticPage($path) {
		self::$staticPage = $path;
	}

	public static function handle() {
		if (self::$cnt) {
			Log::error('[ERRORHANDLER] handle >> controller handle error as 500');
			return self::$cnt->handleError(500);
		}
		if (self::$staticPage && file_exists(self::$staticPage)) {
			Log::error('[ERRORHANDLER] handle >> output static error page from "' . self::$staticPage . '"');
			echo file_get_contents(self::$staticPage);
			return exit();
		}
		Log::debug('[ERRORHANDLER] handle >> no error handling out has been set correctly');
		echo '500 Error';
		exit();
	}
}

register_shutdown_function('errorCatcher');

function errorcatcher() {
	$error = error_get_last();
	if ($error) {
		$type = $error['type'];
		switch ($type) {
			case E_ERROR: 
				Log::fatal('*** Fatal Error: run-time', $error);
				ErrorHandler::handle();
				break;
			case E_PARSE: 
				Log::fatal('*** Fatal Error: parser', $error);
				ErrorHandler::handle();
				break;
			case E_CORE_ERROR:
				Log::fatal('*** Fatal Error: PHP startup', $error);
				ErrorHandler::handle();
				break;
			case E_COMPILE_ERROR: 
				Log::fatal('*** Fatal Error: compile', $error);
				ErrorHandler::handle();
				break;
			case E_USER_ERROR:
				Log::fatal('*** Fatal Error: Application error', $error);
				ErrorHandler::handle();
			default:
				Log::warn('[WARN]', $error);
				break;
		}
	}
}

?>