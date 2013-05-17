<?php 

class Controller {
	
	public function __construct() {
	
	}
	
	public function getSession() {
		$sessId = session_id();
		if (isset($_SESSION[$sessId])) {
			$sess =  $_SESSION[$sessId];
			return $sess;
		}
		return null;
	}
	
	public function setSession($session) {
		$sessId = session_id();
		$_SESSION[$sessId] = $session;
	}
	
	public function addSession($key, $value) {
		$sessId = session_id();
		$session = $_SESSION[$sessId];
		$session[$key] = $value;
		$_SESSION[$sessId] = $session;
	}

	public function removeSession() {
		Log::info('[CONTROLLER] removeSession');
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

?>
