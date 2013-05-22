<?

Loader::import('module', 'fb/src/facebook.php');

class FB {
	
	/***
	* Facebook SDK
	* Required configurations
		"Facebook": {
			"appId": "facebookAppId",
			"secret": "facebookAppSecret",
			"fileUpload": true/false, (optional)
			"afterLoginUrl": 'https://yourApp.com/afterLogin/', (optional)
			"afterLogoutUrl": 'https://yourApp.com/afterLogout/', (optional)
		}
	*
	**/
	
	private $fb;
	private $afterLogin;
	private $afterLogout;

	public function FB() {
		$conf = Config::get('Facebook');
		$params = array(
			'appId' => $conf['appId'],
			'secret' => $conf['secret'],
			'fileUpload' => $conf['fileUpload']
		);
		$this->afterLogin = isset($conf['afterLoginUrl']) ? $conf['afterLoginUrl'] : null;
		$this->afterLogout = isset($conf['afterLogoutUrl']) ? $conf['afterLogoutUrl'] : null;
		$this->fb = new Facebook($params);
	}

	public function checkAuth() {
		$user = $this->fb->getUser();
		if ($user) {
			// found logged in user
			return $user;
		}
		return null;	
	}

	public function getMyData() {
		$userId = $this->checkAuth();
		if ($userId) {
			try {
				return $this->fb->api('me/', 'GET');
			} catch (Exception $e) {
				Log::error('[FB] getMyData > ', $e->getMessage());
			}
		}
		// user not logged in
		return null;
	}

	public function getUserData($fbId) {
		try {
			return $this->fb->api($fbId . '/', 'GET');
		} catch (Exception $e) {
			Log::error('[FB] getUserData > ', $e->getMessage());
		}
	}

	public function getMyFriends() {
		$userId = $this->checkAuth();
		if ($userId) {
			try {
				return $this->fb->api('me/friends', 'GET');
			} catch (Exception $e) {
				Log::error('[FB] getMyFriends > ', $e->getMessage());
			}
		}
		// user not logged in
		return null;
	}

	// post to facebook
	public function postToMyWall($link, $msg) {
		$userId = $this->checkAuth();
		if ($userId) {
			try {
				$post = array('link' => $link, 'message' => $msg);
				$this->fb->api('me/feed', 'POST');
				return true;
			} catch (Exception $e) {
				Log::error('[FB] postToMyWall > ',$e->getMessage() );
			}
		}
		return false;
	}

	/***
	* @param params (Array) array('scope' => 'commaseparated list', 'redirect_uri' => 'URL to come back to after login')
	* scope => (String) comma separated string for permissions
	* read_stream, read_friendlists, read_insights, read_mailbox, read_requests, publish_actions etc...
	* for more details: http://developers.facebook.com/docs/reference/login/extended-permissions/
	*/
	public function getLoginUrl($params) {
		if (!isset($params)) {
			Log::error('[FB] missing required parameter(s)', $params);
			return false;
		}
		if (!isset($params['redirect_uri']) && $this->afterLogin) {
			$params['redirect_uri'] = $this->afterLogin;
		}
		if (!isset($params['redirect_uri'])) {
			Log::error('[FB] missing required parameter(s)', $params);
			return false;
		}
		return $this->fb->getLoginUrl($params);
	}

	// $params is optional: array('next' => 'http://yourApp.com/afterLogout/');
	public function getLogoutUrl($params) {
		if ($this->afterLogout) {
			if (!isset($params)) {
				$params = array();
			}
			if (!isset($params['next'])) {
				$params['next'] = $this->afterLogout;
			}
		}
		return $this->fb->getLogoutUrl($params);
	}
}

?>
