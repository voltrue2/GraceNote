<?php
Load::model('SelectModel');

class LoginManager {
	
	private $c;
	private $login_key = 'login_key';	

	public function LoginManager($controller, $login_path = false){
		$this->c = $controller;
		$loggedin = $this->get();
		if ($loggedin){
			$this->c->assign('LOGGEDIN_USER', $loggedin);
			$this->c->assign('LOGGEDIN', true);
		}
		else {
			$this->c->assign('LOGGEDIN_USER', false);
			$this->c->assign('LOGGEDIN', false);
			if ($login_path){
				$this->c->redirect($login_path);
			}
		}
	}

	public function set($data){
		session_regenerate_id();
		$this->c->set_session(session_id(), $data);
	}
	
	public function out(){
		session_destroy();
	}
	
	public function get(){
		return $this->c->get_session(session_id());
	}
	
	public function check_permission($table_name){
		$user = $this->get();
		$model = new SelectModel();
		$permission = $model->table('permissions');
		$permission->cond('table_name = '.$permission->escape($table_name));
		$permission->cond('permission = '.$permission->escape($user['permission']));
		return $permission->find();
	}
}
?>
