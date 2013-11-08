<?php 

class Controller {
	
	public function __construct() {
	
	}
	
	public function getSession() {
		return $_SESSION;
	}
	
	public function setSession($session) {
		foreach ($session as $key => $val) {
			$_SESSION[$key] = $val;
		}
	}
	
	public function addSession($key, $value) {
		$_SESSION[$key] = $value;
	}

	public function removeSession() {
		Log::info('[CONTROLLER] removeSession: ' . session_name());
		setcookie(session_name(), null);
		session_destroy();
	}
	
	public function getQuery($key = null) {
		if ($key) {
			if (isset($_REQUEST[$key])) {
				return $this->prepareValue($_REQUEST[$key]);
			} else {
				return null;
			}
		}
		return $_REQUEST;
	}

	public function post($key = null) {
		if ($key) {
			if (isset($_POST[$key])) {
				return $this->prepareValue($_POST[$key]);
			} else {
				return null;
			}
		}
		return $_POST;
	}
	
	public function get($key = null) {
		if ($key) {
			if (isset($_GET[$key])) {
				return $this->prepareValue($_GET[$key]);
			} else {
				return null;
			}
		}
		return $_GET;
	}
	
	public function getFile($key = null) {
		if ($key) {
			if (isset($_FILES[$key])) {
				return $_FILES[$key];
			} else {
				return null;
			}
		}
		return $_FILES;
	}

	private function findControllerClass($controller) {
		$className = null;
		$classes = get_declared_classes();
		$res = preg_grep('/' . $controller . '/i', $classes);
		foreach ($res as $cls) {
			if ($controller === strtolower($cls)) {
				$className = $cls;
				break;
			}
		}
		unset($classes);
		return $className;
	}
	
	private function prepareValue($value) {
		try {
			$res = json_decode($value, true);
			if ($res) {
				return $res;
			} else {
				throw new Exception('notJSON');
			}
		} catch (Exception $e) {
			return $value;
		}
	}
}
