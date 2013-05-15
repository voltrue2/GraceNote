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
	public function Router() {
		// extract requested UIR
		$this->uri = $_SERVER['REQUEST_URI'];
		Log::debug('[ROUTER] Router::constructor > Request >> ' . $this->uri);
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
		Log::verbose('[ROUTER] Router::constructor > Request URI processed >> ' . $this->controller . '::' . $this->method);
		// check rerounting
		if ($this->conf && isset($this->conf['reroute']) && !empty($this->conf['reroute'])) {
			$cnt = $this->controller . '/';
			foreach ($this->conf['reroute'] as $rerouteInfo) {
				$from = $rerouteInfo['from'];
				$to = $rerouteInfo['to'];
				if (strpos($from, $cnt) !== false) {
					$cls = null;
					 $method = null;
					$sep = explode('/', $from);
					if (isset($sep[1])) {
						$cls = $sep[1];
					}
					if (isset($sep[2])) {
						$method = $sep[2];
					}
					if ($cls == $this->controller) {
						if ($method == $this->method) {
							// both controller and method matched > reroute
							Log::verbose('[ROUTER] Router::constructor > Rerouting from >> ' . $this->controller . '->' . $this->method);
							list($notUsed, $this->controller, $this->method) = explode('/', $to);
							Log::verbose('[ROUTER] Router::constructor > Rerouting to >> ' . $this->controller . '->' . $this->method);
							break;
						}
					}
				}
			}
		}
		// upper case the first char of controller
		$this->controller = strtolower($this->controller);
	}

	public function getController() {
		return $this->controller;
	}
	
	public function getMethod() {
		return ($this->method) ? $this->method : 'index';
	}
	
	public function getParams() {
		return $this->params;
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
		return $this->conf['error'];
	}

	public function redirect($path, $altCode = false){
		if (!$altCode){
			header('HTTP/1.1 '.$this->headerCodes[301]);
		}
		else if (isset($this->headerCodes[$altCode])){
			header('HTTP/1.1 '.$this->headerCodes[$altCode]);
		}
		Log::verbose('[ROUTER] Router::redirect > ' . $path . ' code:' . $altCode);
		header('Location: '.$path);
		exit();
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
					Log::verbose('[ROUTER] Router::constructor > exception to force trailing slash >> ' . $controllerName);
					return;
				}
			}
			Log::verbose('[ROUTER] Router::constructor > force trailing slash on ' . $this->uri);
			// no trailing slash > redirect with trailing slash
			$this->redirect($uri . '/' . $queries);
		}
	}
}

?>
