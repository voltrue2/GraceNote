<?php
require_once(substr(__FILE__, 0, strpos(__FILE__, basename(__FILE__))) . 'ConfigParser.php');
require_once('Load.class.php');
// check for smarty use
if (SMARTY){
	Load::core('smarty/Smarty');
}
Load::core('Timer');
Load::core('View');
Load::core('Base');
Load::core('Message');
Load::core('SessionHandler');
// include -> template functions
include('template_funcs.php');
// start session
session_start();

class Main extends Base {
	
	public $def;
	private $uri;
	private $full_uri = false;
	private $uri_queries = false;
	private $script_path = false;
	private $timer = false;
	private $timer_msg = false;	

	public function Main(){
		// turn on benchmark if in display error mode
		if (DISPLAY_ERRORS){
			Message::init();
			$this->timer = new Timer(1);
		}
		$this->parse_def();
		$this->check_uri();
		$page = $this->read_page();
		$this->display($page);
		if (DISPLAY_ERRORS){
                        Message::register('<span style="color: #0000CC;">Page Display Time <span style="color: #FF0000;">[ '.round($this->timer->get() * 1000, 4).' ms ]</span></span>');
                        Message::show();
                }
		exit();	
	}
	
	private function parse_def(){
		$this->def = unserialize(DEF);
		if (!$this->def){
			$this->error('Main::constructor > Invalid Config.php Data');
		}
	}
	
	private function check_uri(){
		$suri = $_SERVER['REQUEST_URI'];
		if (isset($suri)){
			$query = strpos($suri, '?');
			if ($query !== false){
				$this->uri = substr($suri, 0, $query);
			}
			else {
				$this->uri = $suri;
			}
		}		
		else {
			$this->error('Main::page_def > Failed to read SERVER["REQUEST_URI"]');
			$this->uri = '/'; // take me to the top page
		}
		// this is necessary to support multibyte URI
		$this->uri = urldecode($this->uri);
		$this->full_uri = $this->uri;
		$qstr = $_SERVER['QUERY_STRING'];
		if (isset($qstr)){
			if ($qstr){
				$this->full_uri .= '?'.$qstr;
			}
		}
		// check for the trailing slash
		$s = substr($this->uri, strlen($this->uri) - 1);
		if ($s != '/'){
			if (isset($qstr)){
				if ($qstr){
					$q = '?'.$qstr;
				}
				else {
					$q = '';
				}
			}
			else {
				$q = '';
			}
			// redirect with trailing slash
			$this->redirect($this->uri.'/'.$q);
		}
	}
	
	private function read_page($alt_uri = false){
		$uri = $this->uri;
		$error_page = false;
		if ($alt_uri){
			// this is for error page display
			$uri = $alt_uri;
			$error_page = true;
		}
		// read page from the URI
		if ($uri == '/'){
			$src = $uri;
			$method = false;
		}
		else {
			$this->uri_queries = explode('/', $uri);
			$src = $this->uri_queries[1];
			if (isset($this->uri_queries[2])){
				$method = $this->uri_queries[2];
			}
			else {
				$method = false;
			}
		}
		// check for error page display
		if (!$error_page){
			// check routes settings
			$routes = $this->def['ROUTES'];
			$path = substr($uri, 1, -1);
			$route = false;
			if (isset($routes[$src.'/*'])){
				// first priority is the wildcard
				$route = $routes[$src.'/*'];
				// reset method
				$method = false;
			}
			else if (isset($routes[$path])){
				$route = $routes[$path];
			}
			else if (isset($routes[$src])){
				$route = $routes[$src];
			}
			if ($route){
				$reroutes = explode('/', $route);
				$src = $reroutes[0];
				if (!$method && isset($reroutes[1])){
					$method = $reroutes[1];
				}
				// insert URI queries if any
				if (isset($reroutes[2])){
					for ($i = 2; $i < count($reroutes); $i++){
						if (!isset($this->uri_queries[$i])){
							$this->uri_queries[$i] = $reroutes[$i]; 
						}
						else if (!$this->uri_queries[$i]){
							$this->uri_queries[$i] = $reroutes[$i];
						}
					}
				}
			}
		}
		// check default method
		if (!$method){
			$method = DEFAULT_PAGE_METHOD;
		}
		$directory = basename($src);
		$page_class = false;
		$handle = opendir(ACTIONS_PATH.'/'.$directory.'/');
		if ($handle){
			while (($file = readdir($handle)) !== false){
				if (strtolower($file) == $directory.PHP_EXTENSION){
					$page_class = str_replace(PHP_EXTENSION, '', $file);
					break;
				}
			}
		}	
		$path = $directory.'/'.$page_class;
		// set the PHP script directry
		$this->script_path = $directory;
		return array('require' => $path, 'action' => $page_class, 'method' => $method);
	}
	
	private function display($src){
		// setup Controller object instance
		if (SMARTY){
			// use Smarty
			$smarty = new Smarty();
			$view = new View($this, $smarty);
		}
		else {
			// use GraceNote native template output
			$view = new View($this);
		}
		$view->assign('HOST', HOST);
		$view->assign('SHOST', SHOST);
		$view->assign('CDN', CDN);
		$view->assign('CURRENT_HOST', CURRENT_HOST);
		$view->assign('JS_PATH', JS_PATH);
		$view->assign('CSS_PATH', CSS_PATH);
		$view->assign('IMG_PATH', IMG_PATH);
		$view->assign('CIMG_PATH', CIMG_PATH);
		$view->assign('URI', $this->uri);
		$view->assign('FULL_URI', $this->full_uri);
		$view->assign('LANG_QUERY_NAME', LANG_QUERY_NAME);
		$view->assign('PARSED_URI', $this->parse_uri($this->uri));
		$view->assign('QUERIES', $this->parse_queries($view->get('PARSED_URI')));
		$view->assign('PROTOCOL', $this->protocol());
		// session array
		if (isset($_SESSION)){
			$view->assign('SESSION', $_SESSION);	
		}
		else {
			$view->assign('SESSION', false);
		}
		// user agent and device
		$user_info = $this->check_device();
		$view->assign('DEVICE', $user_info['DEVICE']);
		$view->assign('USER_AGENT', $user_info['AGENT']);
		// set template header
		$header = $this->get_header($user_info);
		$view->set_header($header);
		// unix timestamp
		$view->assign('EPOCH', $this->epoch());
		// HTTP referer
		if (isset($_SERVER['HTTP_REFERER'])){
			$view->assign('REFERER', $_SERVER['HTTP_REFERER']);	
		}
		// Probably no need for these
		// set auto css
		//$view->assign('AUTO_CSS', $this->auto_css($this->script_path, $view));
		// set auto javascript
		//$view->assign('AUTO_JS', $this->auto_js($this->script_path, $view));
		// check language
		$this->check_language($view); // check and store the language in session
		$view->assign('CURRENT_LANG', $view->lang());
		// template directory check for the given device
		$tpl_dir = $this->def['TPL_DIR'];
		if (isset($tpl_dir[$user_info['DEVICE']])){
			$dir = $tpl_dir[$user_info['DEVICE']];
		}
		else {
			$dir = DEFAULT_TPL;
		}
		if (SMARTY){
			// setup smarty
			$smarty->template_dir = TPL_PATH.$dir;
			$smarty->compile_dir = BASE_PATH.'templates_c/';
			$smarty->config_dir = BASE_PATH.'configs/';
			$smarty->cache_dir = BASE_PATH.'cache/';
		}
		$file_type = $view->tpl_path(TPL_PATH.$dir.$this->script_path.'/');
		$view->assign('TPL_TYPE', $file_type);
		$view->assign('TPL_PATH', TPL_PATH.$dir);
		// remember previous and current URL
		$view->set_session('PREVIOUS_URL', $view->get_session('CURRENT_URL'));
		$view->set_session('CURRENT_URL', $view->get('PROTOCOL').'://'.$view->get('CURRENT_HOST').$view->get('FULL_URI'));
		// display
		if ($src['action']){
			$success = $this->load($src['require']);
			if ($success){
				// display the request page successfully
				if (DISPLAY_ERRORS){
					$timer = new Timer(1);
				}
				try {
					$page_obj = new $src['action']($view);
				}
				catch (Exception $e){
					$this->error($e->getMessage());
				}
				if (method_exists($page_obj, $src['method']) && $src['method']){
					// call the method if found in the given URI with the rest of URI as arguments
					$params = array();
					if ($view->get('PARSED_URI')){
						foreach ($view->get('PARSED_URI') as $i => $item){
                                        		if ($i > 0 && $item != $src['method']){
                                                		$params[] = $item;
                                                	}
                                        	}
					}
					call_user_func_array(array($page_obj, $src['method']), $params);
				}
				else {
					// no method given -> display 404
					$this->display_error_page(404);
				}
				if (DISPLAY_ERRORS){
					Message::register('<span style="color: #0000CC;">Action Process Time <span style="color: #FF0000;">[ '.round($timer->get() * 1000, 4).' ms ]</span></span>');
				}
			}
			else {
				$this->error('Main::display > Action File Not Found For = '.$this->uri);
				// page display error show 404 page
				$this->display_error_page(404);
			}
		}
		else {
			$this->error('Main::display > Failed to load Action class at = '.$this->uri.' : ('.$src['require'].')');
			// page display error show 404 page
			$this->display_error_page(404);
		}
	}

	public function display_error_page($header_code){
		$header = $this->header($header_code);
		$no_error_page = true;
		if (isset($this->def['ERROR_PAGES'])){
			if (isset($this->def['ERROR_PAGES'][$header_code])){
				$no_error_page = false;
				$error_path = '/'.$this->def['ERROR_PAGES'][$header_code];
				$src = $this->read_page($error_path);
				header('HTTP/1.0 '.$header);
				$this->display($src);
			}
		}
		if ($no_error_page) {
			$this->error('Main::display_error_page > '.$header);
			header('HTTP/1.0 '.$header);
			echo('GraceNote Framework : Error ('.$header.')');
		}
		exit();
	}
	
	private function load($path){
		return Load::action($path, true);
	}
	
	private function parse_uri($u){
		$pu = explode('/', $u);
		if (!empty($pu)){
			$uri = array();
			foreach ($pu as $val){
				if ($val){
					$uri[] = $val;
				}
			}
			if (empty($uri)){
				$uri = false;
			}
		}
		else {
			$uri = false;
		}
		return $uri;
	}
	
	private function parse_queries($uri){
		$q = array();
		if (!empty($_GET)){
			$q = $_GET;
		}
		if (!empty($_POST)){
			if (!empty($q)){
				$q = array_merge($q, $_POST);
			}
			else {
				$q = $_POST;
			}
		}
		if (!empty($_FILES)){
			if (!empty($q)){
				$q = array_merge($q, $_FILES);
			}
			else {
				$q = $_FILES;
			}
		}
		if ($this->uri_queries){
			// retrieve uri queries
			if (!empty($uri)){
				$viewounter = 0;
				if (!empty($this->uri_queries)){
					$query_keys = explode(',', URI_QUERIES);
					foreach ($this->uri_queries as $i => $item){
						if ($item && $item){
							if (isset($query_keys[$viewounter])){
								$key = $query_keys[$viewounter];
							}
							else {
								$key = $viewounter;
							}
							$q[$key] = $item;
							$viewounter++;
						}
					}
				}
			}
		}
		if (empty($q)){
			$q = false;
		}
		return $q;
	}
	
	private function protocol(){
		if (!empty($_SERVER['HTTPS']) || $_SERVER['SERVER_PORT'] == 443 || $_SERVER['HTTP_HOST'] == SHOST){
			return 'https';
		}
		else {
			return 'http';
		}
	}
	
	private function get_header($user_info){
		if (isset($this->def['TEMPLATE_HEADERS'][$user_info['DEVICE']])){
			return $this->def['TEMPLATE_HEADERS'][$user_info['DEVICE']];
		}
		else {
			// no header for this device has been set
			if (isset($this->def['TEMPLATE_HEADERS']['DEFAULT'])){
				return $this->def['TEMPLATE_HEADERS']['DEFAULT'];
			}
			else {
				$this->error('Main::display > No header has been set for the device ('.$user_info['DEVICE'].')');
				return '';
			}
		}
	}
	
	private function epoch(){
		list($msecs, $uts) = explode(' ', microtime());
		return floor(($uts + $msecs) * 1000);
	}
	
	private function auto_css($uri, $view){
		return $this->auto_file(CSS_PATH, $uri, $view, 'css');
	}
	
	private function auto_js($uri, $view){
		$res = $this->auto_file(JS_PATH, $uri, $view, 'js');
		return $res;
	}
	
	private function auto_file($path, $uri, $view, $ext){
		if (isset($uri) && $view){
			$device = strtolower($view->get('DEVICE'));
			$path = $path.$device.'/'.$uri.'.'.$ext;
			if (file_exists($path)){
				$mtime = filemtime($path);
				if ($mtime){
					$ts = '?'.$mtime;
				}
				else {
					$ts = '';
				}
				return $device.'/'.$uri.'.' . $ext . $ts;
			}
			return false;
		}
		else {
			return false;
		}
	}
	
	private function check_language($view){
		$q = $view->get('QUERIES');
		if (isset($q[LANG_QUERY_NAME])){
			$lang_id = $q[LANG_QUERY_NAME];
		}
		else {
			$lang_id = false;
		}
		if ($lang_id){
			// language has been given -> store the value both in session and cookie
			$view->set_session(LANG_QUERY_NAME, $lang_id);
			$view->set_cookie(LANG_QUERY_NAME, $lang_id);
		}
		else {
			if (!$view->lang()){
				// no language has sepecified -> switch to default
				$view->set_session(LANG_QUERY_NAME, DEFAULT_LANG);
			}
		}
	}
	
	private function check_device(){
		if (isset($_SERVER['HTTP_USER_AGENT'])){
			$agent = $_SERVER['HTTP_USER_AGENT'];
			$browsers =  $this->def['UAGENTS'];
			foreach ($browsers as $i => $item){
				$viewheck = strpos($agent, $i);
				if ($viewheck !== false){
					list($agent, $device) = explode(',', $item);
					return array('AGENT' => $agent, 'DEVICE' => $device);
				}
			}			
		}
		else {
			return '';
		}
	}
}
?>
