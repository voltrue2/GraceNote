<?php 

class Controller {
	
	private $router;
	private $view;
	private $errorConf = null;
	
	public function Controller($router, $view) {
		$this->router = $router;
		$this->view = $view;
		$this->errorConf = $this->router->getErrorRules();
	}

	public function create($startTime) {
		$controller = $this->router->getController();
		$method = $this->router->getMethod();
		// import controller index file
		$success = Loader::import('controller', $controller . '/index.class.php');
		if ($success) {
			// find class controller class name
			$className = $this->findControllerClass($controller);
			if (!$className) {
				Log::error('[CONTROLLER] create > controler "' . $controller. '" not found');
				return $this->handleError(404);
			}	
			// create app
			$app = new $className($this->view, $this);
			Log::verbose('[CONTROLLER] create > created app "' . $controller . '" >> check method >>> ' . $method);
			if (method_exists($app, $method)) {
				// call the method
				try {
					call_user_func_array(array($app, $method), $this->router->getParams());
				} catch (Exception $e) {
					Log::error($e);
					return $this->handleError(500);
				}
				// calculate the time it took to execute
				$endTime = microtime(true);
				$time = (string)substr((($endTime - $startTime) * 1000), 0, 8);
				Log::info('[CONTROLLER] "' . $this->router->getUri() . '" took [' . $time . ' msec] to execute');
				// done execution
				exit();
			} else {
				Log::error('[CONTROLLER] create > ' . $this->router->getUri() . ' >> ' . $controller . '->' . $method . ' does not exist');
				// 500 error > method missing
				$this->handleError(404);
			}
		} else {
			Log::error('[CONTROLLER] create > ' . $this->router->getUri() . ' >> ' . $controller . ' does not exist');
			// 404 error > controller missing
			$this->handleError(404);
		}
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
	
	public function getUri() {
		return $this->router->getUri();
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
	
	public function redirect($path, $altCode = null) {
		$this->router->redirect($path, $altCode);
	}

	public function handleError($errorCode) {
		Log::error('Controller::handleError > ' . $errorCode);
		$headerCode = $this->router->getHeaderCode($errorCode);
		if ($headerCode) {
			Log::error('Controller::handleError > Error header is "' . $headerCode . '"');
			header('HTTP/1.0 ' . $headerCode);
		}
		if (isset($this->errorConf[$errorCode])) {
			list($notUsed, $controller, $method) = explode('/', $this->errorConf[$errorCode]);
			$controller = strtolower($controller); 
			Log::verbose('Controller::handleError > Loading error controller >> ' . $controller . '->' . $method);
			$success = Loader::import('controller', $controller . '/index.class.php');
			if ($success) {
				$app = new $controller($this->view, $this);
				if (method_exists($app, $method)) {
					call_user_func_array(array($app, $method), $this->router->getParams());
					exit();
				} else {
					Log::error('Controller::handleError > method "' . $method . '" of "' . $controller . '" not found');
					exit('Error page "' . $method . '" could not display');
				}	
			}
			
		}
		Log::error('Controller::handleError > no error (' . $errorCode . ') defined in Controller configuration or error controller is not properly defined');
		return exit();
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
