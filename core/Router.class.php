<?php 

class Router {
	
	private $uri;
	private $conf;
	private $controller;
	private $method;
	private $params = null;
	private $queries = null;
	private $post = null;
	
	private $headerCodes = array(
		200 => '200 OK',
		300 => '300 Multiple Choices',
		301 => '301 Moved Permanently',
		302 => '302 Found',
		304 => '304 Not Modified',
		403 => '403 Forbidden',
		400 => '400 Bad Request',
		401 => '401 Unauthorized',
		404 => '404 Not Found',
		500 => '500 Internal Server Error',
		503 => '503 Service Unavailable'
	);

	/*
	* Configurations
	* {
	* 	"forceTrailingSlash": true/false,
	*	"noTrailingSlash": ["controllerNameInLowerCase"]
	*	"reroute": [
	*		{ "from": "origianUri", "to", "reroutedUri" } Example: { "from": "/", "to" "/main/index/" }
	*	],
	*	"error": {
			// code: is a prefix
	*		"code:" + "errorCode": "errorUri" Example: "404": "/error/notFound/"	
	*	}
	* }
	*/
	public function __construct() {
		// extract requested UIR
		$this->uri = $_SERVER['REQUEST_URI'];
		Log::debug('[ROUTER] resolve HTTP request: ' . $this->uri);
		// load config
		$this->conf = Config::get('Router');
		if ($this->conf && isset($this->conf['forceTrailingSlash']) && $this->conf['forceTrailingSlash']) {
			// we force trailing slash
			$this->forceTrailingSlash();
		}
		// trim query string if there is any
		$queryPos = strpos($this->uri, '?');
		if ($queryPos !== false) {
			$this->uri = substr($this->uri, 0, $queryPos);
		}
		// extract app info
		$paramsUri = explode('/', substr(trim($this->uri, '/'), 0));
		if (isset($paramsUri[0])) {
			$this->controller = $paramsUri[0];
		}
		if (isset($paramsUri[1])) {
			$this->method = $paramsUri[1];
		}
		$this->params = array_splice($paramsUri, 2);
		$this->queries = $_GET;
		$this->post = $_POST;
		Log::debug('[ROUTER] resolved HTTP request: ' . $this->controller . '::' . $this->method);
		// check rerounting
		if ($this->conf && isset($this->conf['reroute']) && !empty($this->conf['reroute'])) {
			$cnt = $this->controller . '/';
			foreach ($this->conf['reroute'] as $rerouteInfo) {
				$from = $rerouteInfo['from'];
				$to = $rerouteInfo['to'];
				if (strpos($from, $cnt) !== false) {
                    $cls = null;
                     $method = null;
                    $cntSep = explode('/', $from);
                    if (isset($cntSep[1])) {
                        $cls = $cntSep[1];
                    }
                    if (isset($cntSep[2])) {
                        $method = $cntSep[2];
                    }
                    if ($method) {
                        if ($method !== $this->method) {
                            continue;
                        }
                    }
                    if ($cls == $this->controller) {
                        // controller matched > reroute
                        $mSep = explode('/', $to);
                        if (isset($mSep[2])) {
                            $this->method = $mSep[2];
                        }
                        Log::verbose('[ROUTER] rerouting from >> ' . $this->controller . '->' . $this->method);
                        list($notUsed, $this->controller, $this->method) = explode('/', $to);
                        Log::verbose('[ROUTER] rerouting to >> ' . $this->controller . '->' . $this->method);
                        break;
                    }
				}
			}
		}
		$this->controller = $this->controller;
	}

	public function createController($view, $startTime) {
		$className = $this->findControllerClass($this->controller);
		$controller = $this->initController($className, $view);
		if (!$controller) {
			$this->handleError(404, $view, $startTime);
		}
		$success = $this->callControllerMethod($controller, $startTime);
		if (!$success) {
			$this->handleError(404, $view, $startTime);
		}
	}
	
	public function getMethod() {
		return ($this->method) ? $this->method : 'index';
	}

	public function getUri() {
		return $this->uri;
	}
	
	public function getHeaderCode($code) {
		if (isset($this->headerCodes[$code])) {
			return $this->headerCodes[$code];
		} else {
			return null;
		}
	}
	
	public function getErrorRules() {
		return (isset($this->conf['error'])) ? $this->conf['error'] : null;
	}

	public function redirect($path, $altCode = false){
		if (!$altCode){
			header('HTTP/1.1 '.$this->headerCodes[301]);
		}
		else if (isset($this->headerCodes[$altCode])){
			header('HTTP/1.1 '.$this->headerCodes[$altCode]);
		}
		Log::verbose('[ROUTER] redirect > ' . $path . ' code:' . $altCode);
		header('Location: ' . $path);
		exit();
	}
	
	public function handleError($errorCode, $view, $startTime) {
		Log::error('[ROUTER] Error URI ' . $this->getUri());
		$headerCode = $this->getHeaderCode($errorCode);
		if ($headerCode) {
			Log::error('[ROUTER] > Error header is "' . $headerCode . '"');
			header('HTTP/1.1 ' . $headerCode);
		}
		$eRules = $this->getErrorRules();
		if ($eRules && isset($eRules[$errorCode])) {
			list($notUsed, $eCnt, $method) = explode('/', $eRules[$errorCode]);
			$eCnt = strtolower($eCnt); 
			$imported = Loader::import('controller', $eCnt . '/index.class.php');
			if (!$imported) {
				trigger_error('[ROUTER] error controller (' . $eCnt . ') not found', E_USER_ERROR);
			}
			$controller = $this->initController($eCnt, $view);
			if (!$controller) {
				trigger_error('[ROUTER] error controller (' . $eCnt . ') failed', E_USER_ERROR);
			}
			$this->method = $method;
			$this->callControllerMethod($controller, $startTime);
			if (!$success) {
				trigger_error('[ROUTER] error controller method (' . $this->method . ') not found');
			}
			
		}
		trigger_error('[ROUTER] > no error (' . $errorCode . ') defined in Router configuration or error controller is not properly defined', E_USER_ERROR);
	}

	private function forceTrailingSlash() {
		$pos = strpos($this->uri, '?');
		$queries = '';
		$uri = $this->uri;
		if ($pos !== false) {
			$queries = substr($uri, $pos, strlen($uri));
			$uri = substr($uri, 0, $pos);
		}
		$lastChar = substr($uri, strlen($uri) - 1);
		if ($lastChar !== '/') {
			// check for exceptions
			if (isset($this->conf['noTrailingSlash']) && is_array($this->conf['noTrailingSlash'])) {
				$paramsUri = explode('/', substr(trim($this->uri, '/'), 0));
				$controllerName = $paramsUri[0];
				if (in_array($controllerName, $this->conf['noTrailingSlash'])) {
					// this controller is exception > no need to force trailing slash
					Log::verbose('[ROUTER] exception to force trailing slash (configured in config.json) > ' . $controllerName);
					return;
				}
			}
			Log::verbose('[ROUTER] force trailing slash on ' . $this->uri);
			// no trailing slash > redirect with trailing slash
			$this->redirect($uri . '/' . $queries);
		}
	}

	private function findControllerClass($controller) {
		$className = null;
		$success = Loader::import('controller', $controller . '/index.class.php');
		Log::debug('[ROUTER] Load ' . $controller . '/index.class.php [' . (($success) ? 'true' : 'false') . ']');
		if ($success) {
			$classes = get_declared_classes();
			$res = preg_grep('/' . $controller . '/i', $classes);
			$cnt = strtolower($controller);
			foreach ($res as $cls) {
				if ($cnt === strtolower($cls)) {
					$className = $cls;
					break;
				}
			}
			unset($classes);
		}
		Log::debug('[ROUTER] Controller (' . $className . ') found [' . (($className) ? 'true' : 'false') . ']');
		return $className;
	}

	private function initController($className, $view) {
		if ($className) {
			// instanciate controller class
			$cnt = new $className($view);
			return $cnt;	
		}
		// controller class not found
		return null;
	}
	
	private function callControllerMethod($cnt, $startTime) {
		$method = $this->getMethod();
		if (method_exists($cnt, $method)) {
			// call the method
			try {
				// check the required arguments for the controller method
				$ref = new ReflectionMethod($cnt, $method);
				$params = $ref->getParameters();
				for ($i = 0, $len = count($params); $i < $len; $i++) {
					$param = $params[$i];
					if (!$param->isOptional() && !isset($this->params[$i])) {
						// missing a required argument
						Log::error('[ROUTER] missing required argument for controller method (' . $method . '): argument #' . $param->getPosition() . ' ' . $param->getName());
						return null;
					}
				}
				// call controller method	
				call_user_func_array(array($cnt, $method), $this->params);
				Log::debug('[ROUTER] Controller method called (' . $method . ')');
			} catch (Exception $e) {
				Log::error($e);
				return null;
			}
			// calculate the time it took to execute
			$endTime = microtime(true);
			$time = (string)substr((($endTime - $startTime) * 1000), 0, 8);
			Log::info('[ROUTER] "' . $this->getUri() . '" took [' . $time . ' msec] to execute');
			// done execution
			exit();
		}
		// method not found
		Log::error('[ROUTER] Controller method (' . $method . ') not found');
		return null;
	}
}
