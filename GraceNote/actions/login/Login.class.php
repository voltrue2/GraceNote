<?php
Load::model('LoginModel');
Load::action('header/Header');
Load::lib('custom/LoginManager');

class Login {

	private $c;
	private $model;
	private $q;
	private $top_path;
	private $loginmanager;

	public function Login($view){
		$this->c = $view;
		$this->model = new LoginModel();
		$this->top_path = 'http://'.HOST.'/';
		$this->loginmanager = new LoginManager($this->c);
		$this->q = $this->c->get('QUERIES');
		$this->c->assign('LOGIN', true);
		$header = new Header($this->c);
		$this->c->contents('login_init');
	}
	
	public function init(){
		$this->session = $this->loginmanager->get();
		if ($this->session){
			// logged in -> redirect
			$this->c->redirect($this->top_path, 302);
		}
		$return_path = $this->c->referer();
		if (!isset($this->q['RETURN'])){
			$this->q['RETURN'] = $return_path;
			$this->c->assign('QUERIES', $this->q);
		}
		else {			
			$this->q['RETURN'] = $return_path;
			$this->c->assign('QUERIES', $this->q);
		}
		$this->c->display('init');
	}
	
	public function check(){
		$this->session = $this->loginmanager->get();
		if ($this->session){
			// logged in -> redirect
			$this->c->redirect($this->top_path, 302);
		}
		if (isset($this->q['name'])){
			$name = $this->q['name'];
		}
		else {
			$name = false;
		}
		if (isset($this->q['password'])){
			$pass = $this->q['password'];
		}
		else {
			$pass = false;
		}
		$res = $this->model->check_login($name, $pass);		
		if (!$res){
			// failed to login 
			if ($name || $pass){
				$this->c->assign('LOGIN', false);
			}
			$this->c->display('init');
		}
		else {		
			// get permission access data
			$model = new SelectModel();
			$permissions = $model->table('permissions');
			$permissions->select('table_name');
			$permissions->cond('permission = ?', $res['permission']);
			$permissions->cond('root = ?', 1);
			$root_access = $permissions->find_all();
			if ($root_access){
				foreach ($root_access as $item){
					$res['root_access'][$item['table_name']] = true;
				}
			}
			else {
				$res['root_access'] = false;
			}
			// login success
			$this->loginmanager->set($res); // set login session data
			$p = $this->c->get('PARSED_URI');
			if (strpos($this->q['RETURN'], $p[0]) === false && $this->q['RETURN']){
				$this->c->redirect($this->q['RETURN']); // redirect to the previous page 
			}
			else {
				$this->c->redirect($this->top_path);
			}
		}
	}
	
	public function out(){
		$this->loginmanager->out();
		$this->c->redirect($this->top_path, 302);
	}
}
?>