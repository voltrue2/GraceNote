<?php 

class Auth extends Controller {
	
	private $cmsAdmin;
	
	public function Auth($view) {
		$this->view = $view;
		$dm = new DataModel(CmsData::getDbName());
		$this->cmsAdmin = $dm->table('cms_admin');
		// setup text content
		$authPageText = Text::get($this->view, $this, 'text');
	}

	public function index() {
		// check authentication
		$authed = CmsAuthHandler::check($this->view, $this);
		if ($authed) {
			// user is authenticated
			return $this->view->respondTemplate('auth/menu.html.php');
		}
		// user is not authenticated > sign in page
		$this->view->respondTemplate('auth/signin.html.php');
	}
	
	public function authenticate() {
		$authenticated = $this->getSession();
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
			return $this->view->redirect($redirect, 200);
		}
		$user = $this->getQuery('user');
		$pass = $this->getQuery('pass');
		// authenticate
		$this->cmsAdmin->where('name = ?', $user);
		$useCache = false;
		$list = $this->cmsAdmin->getMany($useCache);
		if (!empty($list)) {
			foreach ($list as $item) {
				// check password
				if (Encrypt::validateHash($pass, $item['hash'])) {
					$authenticated = $item;
					// regenerate hash for security
					$newHash = Encrypt::createHash($pass);
					$this->cmsAdmin->set('hash', $newHash);
					$this->cmsAdmin->where('hash = ?', $item['hash']);
					$this->cmsAdmin->update();
					break;
				}
			}
		}
		// handle after authentication
		if ($authenticated) {
			// create session data
			$lang = $this->getQuery('lang');
			if ($lang !== null) {
				$sessionValue['lang'] = $lang;
			}
			$sessionValue['id'] = $authenticated['id'];
			$sessionValue['user'] = $authenticated['name'];
			$sessionValue['permission'] = $authenticated['permission'];
			$sessionValue['fileRestriction'] = $authenticated['file_restriction'];
			$sessionValue['lastLogin'] = strtotime('NOW');
			// regenerate session id for security
			session_regenerate_id(true);
			// update session
			$this->setSession($sessionValue);
			// update last_login
			$this->cmsAdmin->set('last_login', $sessionValue['lastLogin']);
			$this->cmsAdmin->where('id = ?', $sessionValue['id']);
			$this->cmsAdmin->update();
			Log::debug('Auth::authenticate > Authenticated');

			GlobalEvent::emit('auth.authenticate', array($sessionValue));

			$this->view->redirect($redirect, 200);
		}
		// failed to authenticate > redirect to the top
		$this->view->redirect('/', 401);
	}
	
	public function signout() {
		$authed = $this->getSession();
		if (isset($authed['id']) && isset($authed['user']) && isset($authed['lastLogin'])) {
			$sessId = session_id();
			$this->removeSession();
			Log::debug('Auth::signout > Sign Out >> ' . $sessId);
			$this->view->redirect('/', 200);
			return;
		}
		$this->view->redirect('/', 200);
	}
}
