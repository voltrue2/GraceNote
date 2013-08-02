<?php

class User extends Controller { 
	
	private $view;
	private $dm;

	public function User($view) {
		$this->view = $view;
		// check for authentication
		$sess = CmsAuthHandler::check($view, $this);
		if ($sess) {
			$this->dm = new DataModel(CmsData::getDbName());
			return;
		}
		// not authenticated remember where you were
		$this->view->redirect('/', 401);
	}

	public function getUserList() {
		$sess = $this->getSession();
		if ($sess && isset($sess['permission']) && $sess['permission'] == 1) {
			$cmsAdmin = $this->dm->table('cms_admin');
			$cmsAdmin->where('id != ?', $sess['id']);
			$this->view->assign('list', $cmsAdmin->getMany());
			$this->view->respondJson();
		} else {
			$this->view->respondError(401);
		}
	}

	public function getUserData($userId = null) {
		if ($userId) {
			// get someone else's user data
			$cmsAdmin = $this->dm->table('cms_admin');
			$cmsAdmin->where('id = ?', $userId);
			$res = $cmsAdmin->getOne();
			$userData = array(
				'user' => $res['name'],
				'permission' => $res['permission'],
				'fileRestriction' => $res['file_restriction']
			);
		} else {
			// get my user data
			$userData = $this->getSession();
		}
		$this->view->assign('userData', $userData);
		$this->view->respondJson();
	}

	public function updateUserData($updateMode = 'update', $id = null) {
		$userData = $this->getQuery('userData');
		if (!$userData) {
			Log::error('[USER] updateUserData > missing userData to update');
			return $this->view->respondError(404);
		}
		$sess = $this->getSession();
		if (!$sess || !isset($sess['id'])) {
			throw new Exception('invalid session');
		}
		if ($updateMode !== 'update' && $sess['permission'] != 1) {
			if (isset($userData['permission']) || isset($userData['fileRestriction'])) {
				Log::error('updateUserData: no permission');
				throw new Exception('action not allowed');
			}
		}
		// update
		$cmsAdmin = $this->dm->table('cms_admin');
		// check user name > they must be unique
		if ($updateMode !== 'update' && !$id) {
			// new user only
			$users = $cmsAdmin->getMany();
			for ($i = 0, $len = count($users); $i < $len; $i++) {
				if ($users[$i]['name'] == $userData['user']) {
					$this->view->assign('error', 'User name must be unique');
					throw new Exception('user name must be unique');
					break;
				}
			}
		}
		$cmsAdmin->transaction();
		try {
			foreach ($userData as $col => $value) {
				$val = $value;
				switch ($col) {
					case 'user':
						$val = $this->cleanUserName($value);
						$col = 'name';
						break;
					case 'fileRestriction':
						$val = $this->cleanFileRestriction($value);
						$col = 'file_restriction';
						break;
					case 'password':
						$pass = $this->cleanPassword($value);
						$passwordData = Encrypt::createHashWithSalt($pass, 1);
						$val = $passwordData['hash'];
						$cmsAdmin->set('password_salt', $passwordData['salt']);			
						break;
					case 'permission':
						$val = $this->cleanPermission($value);
						break;		
				}
				if ($val !== null) {
					$cmsAdmin->set($col, $val);
				}
			}
			// save
			if ($updateMode === 'update') {
				// update
				if ($id) {
					$cmsAdmin->where('id = ?', $id);
				} else {
					$cmsAdmin->where('id = ?', $sess['id']);
				}
				$success = $cmsAdmin->update();
			} else {
				// new
				if ($sess['permission'] != 1) {
					return $this->view->respondError(401);
				} 
				$success = $cmsAdmin->save();
			}
			if (!$success) {
				throw new Exception('failed to update');
			}
			$cmsAdmin->commit();
		} catch (Exception $e) {
			$cmsAdmin->rollBack();
			Log::error($e->getMessage());
			return $this->view->respondError(404);
		}
		$this->view->respondJson();
	}

	public function deleteUser($id) {
		$sess = $this->getSession();
		if ($sess && isset($sess['permission']) && $sess['permission'] == 1) {
			$cmsAdmin = $this->dm->table('cms_admin');
			$cmsAdmin->where('id = ?', $id);
			$cmsAdmin->delete();
			return $this->view->respondJson();
		}
		$this->view->respondError(401);
	}

	private function cleanUserName($value) {
		return trim($value);
	}

	private function cleanFileRestriction($value) {
		return str_replace(' ', '', $value);
	}

	private function cleanPassword($value) {
		return str_replace(' ', '', $value);
	}

	private function cleanPermission($value) {
		return $value;
	}
}
