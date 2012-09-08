<?php 
Load::core('orm/Model');

class LoginModel {
	
	private $model;
	/**** Tables ****/
	private $admins;
	private $permissions;
	
	public function LoginModel(){
		$this->model = new Model('admin');
		$this->admins = $this->model->table('admins');
		$this->permissions = $this->model->table('permissions');
	}
	
	public function check_login($name, $pass){
		//$this->admins->cache(false);
		$this->admins->select('admins.name as name');
		$this->admins->select('pn.name as permission_name');
		$this->admins->select('admins.permission as permission');
		$this->admins->select('admins.media_restriction as media_restriction');
		$this->admins->cond('admins.name = ?', $name);
		$this->admins->cond('admins.password = ?', md5($pass));
		$this->admins->join('permission_names AS pn');
		$this->admins->cond('admins.permission = pn.permission_id');
		return $this->admins->find();
	}
}
?>
