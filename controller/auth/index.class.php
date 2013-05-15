<?php 

class Auth {
	
	private $view;
	private $controller;
	private $cmsAdmin;
	
	public function Auth($view, $controller) {
		$this->view = $view;
		$this->controller = $controller;
		$dm = new DataModel(CmsData::getDbName());
		$this->cmsAdmin = $dm->table('cms_admin');
		// setup text content
		$authPageText = Text::get($view, $controller, 'text');
	}

	public function index() {
		// check authentication
		$authed = CmsAuthHandler::check($this->view, $this->controller);
		if ($authed) {
			// user is authenticated
			return $this->view->respondTemplate('auth/menu.html.php');
		}
		// user is not authenticated > sign in page
		$this->view->respondTemplate('auth/signin.html.php');
	}
	
	public function authenticate() {
		$authenticated = $this->controller->getSession();
		$redirect = '/';
		if (isset($authenticated['prevUri'])) {
			if ($authenticated['prevUri']) {
				$redirect = $authenticated['prevUri'];
			} else {
				$redirect = '/';
			}
			Log::debug('Auth::authenticate > redirect to previous URL > ' . $redirect);
		}
		if (isset($authenticated['id']) && isset($authenticated['user']) && isset($authenticated['lastLogin'])) {
			return $this->controller->redirect($redirect, 200);
		}
		$user = $this->controller->getQuery('user');
		$pass = $this->controller->getQuery('pass');
		// authenticate
		$this->cmsAdmin->where('name = ?', $user);
		$useCache = false;
		$list = $this->cmsAdmin->getMany($useCache);
		if (!empty($list)) {
			foreach ($list as $item) {
				// check password
				$passHash = Encrypt::getHashWithSalt($pass, $item['password_salt']);
				if ($passHash === $item['password']) {
					$authenticated = $item;
					break;
				}
			}
		}
		// handle after authentication
		if ($authenticated) {
			// create session data
			$lang = $this->controller->getQuery('lang');
			if ($lang !== null) {
				$sessionValue['lang'] = $lang;
			}
			$sessionValue['id'] = $authenticated['id'];
			$sessionValue['user'] = $authenticated['name'];
			$sessionValue['permission'] = $authenticated['permission'];
			$sessionValue['fileRestriction'] = $authenticated['file_restriction'];
			$sessionValue['lastLogin'] = strtotime('NOW');
			// regenerate session id for security
			session_regenerate_id();
			// update session
			$this->controller->setSession($sessionValue);
			// update last_login
			$this->cmsAdmin->set('last_login', $sessionValue['lastLogin']);
			$this->cmsAdmin->where('id = ?', $sessionValue['id']);
			$this->cmsAdmin->update();
			Log::debug('Auth::authenticate > Authenticated');

			GlobalEvent::emit('auth.authenticate', array($sessionValue));

			$this->controller->redirect($redirect, 200);
		}
		// failed to authenticate > redirect to the top
		$this->controller->redirect('/', 401);
	}
	
	public function signout() {
		$authed = $this->controller->getSession();
		if (isset($authed['id']) && isset($authed['user']) && isset($authed['lastLogin'])) {
			$sessId = session_id();
			$this->controller->removeSession();
			Log::debug('Auth::signout > Sign Out >> ' . $sessId);
			$this->controller->redirect('/', 200);
			return;
		}
		$this->controller->redirect('/', 200);
	}
}

?>
