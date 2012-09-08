<?php 
Load::model('EditModel');
Load::action('header/Header');
Load::lib('custom/LoginManager');

class PermissionManager {
	
	private $model;
	private $loginmanager;
	private $c;
	private $p = false;
	
	public function PermissionManager($view){
		$this->c = $view;
		$this->model = new EditModel();
		$this->loginmanager = new LoginManager($this->c);
		$header = new Header($this->c);
		$this->session = $this->loginmanager->get();
		if (!$this->session){
			// not logged in -> redirect
			$this->c->redirect('http://'.HOST.'/login/', 302);
		}
		// ONLY root has access
		if (!$this->session || $this->session['permission'] !== 0){
			$this->c->redirect('http://'.HOST.'/');
		}
		$this->p = $this->model->table('permissions');
		$admins = $this->model->table('admins');
		$admins->select('id');
		$admins->select('name');
		$admins->select('permission');
		$admins->select('media_restriction');
		$admin_list = $admins->find_all();
		$this->c->assign('ADMIN_LIST', $admin_list);
		$pnames = $this->model->table('permission_names');
		$plist = $pnames->find_all();
		$permission_name_list = array();
		if ($plist){
			foreach ($plist as $item){
				$permission_name_list[$item['permission_id']] =$item;
			}
		}
		$this->c->assign('PERMISSION_NAME_LIST', $permission_name_list);
		$this->c->contents('permissionmanager_contents');
	}
	
	public function init(){
		// get permission list for a cms user
		$this->p->join('permission_names');
		$this->p->where('permission = ?', (int)$this->c->queries('permission'));
		$permission_list = $this->p->find_all();
		$this->c->display('init');
	}
	
	public function edit_admin(){
		$id = $this->c->queries('id');
		$name = $this->c->queries('name');
		$media_restriction = $this->c->queries('media_restriction');
		if ($id && $name){
			$admins = $this->model->table('admins');
			$admins->set('name', $name);
			$pass = $this->c->queries('password');
			if ($pass){
				$admins->set('password', md5($pass));
			}
			$admins->set('media_restriction', $media_restriction);
			$admins->cond('id = ?', $id);
			$admins->save();
		}
		$this->c->redirect('/permissionmanager/');
	}
	
	public function edit_permission(){
		$id = $this->c->queries('id');
		$permission = $this->c->queries('permission');
		if ($id && $permission !== false){
			$admins = $this->model->table('admins');
			$admins->set('permission', $permission);
			$admins->cond('id = ?', $id);
			$admins->save();
		}
		$this->c->redirect('/permissionmanager/');
	}
	
	public function delete_admin(){
		$id = $this->c->queries('CODE');
		if ($id){
			$admins = $this->model->table('admins');
			$admins->cond('id = ?', $id);
			$admins->delete();
		}
		$this->c->redirect('/permissionmanager/');
	}
	
	public function create_admin(){
		$name = $this->c->queries('name');
		$pass = $this->c->queries('password');
		$permission = $this->c->queries('permission');
		$media_restriction = $this->c->queries('media_restriction');
		if ($name && $pass && $permission !== false){
			$admins = $this->model->table('admins');
			$admins->set('name', $name);
			$admins->set('password', md5($pass));
			$admins->set('permission', $permission);
			$admins->set('media_restriction', $media_restriction);
			$admins->save();
		}
		$this->c->redirect('/permissionmanager/');
	}
	
	public function create_permission(){
		$name = $this->c->queries('name');
		if ($name){
			$p = $this->model->table('permission_names');
			$p->select('count(*) as id');
			$res = $p->find();
			if (isset($res['id'])){
				$p->set('name', $name);
				$p->set('permission_id', $res['id']);
				$p->save();
			}
		}
		$this->c->redirect('/permissionmanager/');
	}
	
	public function delete_permission(){
		$permission = $this->c->queries('permission');
		if ($permission){
			$p = $this->model->table('permission_names');
			$p->cond('permission_id = '.$p->escape($permission));
			$p->delete();
		}
		$this->c->redirect('/permissionmanager/');
	}
	
	public function edit_access(){
		$permission = $this->c->queries('permission');
		if ($permission !== false && is_numeric($permission)){
			// admin user permission has been selected
			$ext = $this->model->table('extended_cms');
			$ext->cond('lang_id = '.$ext->escape($this->c->lang()));
			$ext_list = $ext->find_all();
			$this->c->assign('EXT_LIST', $ext_list);
			$tables = $this->model->table('table_desc');
			$table_list = $tables->find_all();
			$this->c->assign('TABLE_LIST', $table_list);
			$p = $this->model->table('permissions');
			$p->cond('permission = '.$p->escape($permission));
			$access = $p->find_all();
			$root = array();
			if ($access){
				$tmp = $access;
				$access = array();
				foreach ($tmp as $i => $item){
					$access[$item['table_name']] = $item;
					$root[$item['table_name']] = $item['root'];
				}
			}
			$this->c->assign('ACCESS_LIST', $access);
			$this->c->assign('ROOT_LIST', $root);
		}
		else {
			$this->c->redirect('/permissionmanager/');
		}
		$this->c->assign('PERMISSION', $permission);
		$this->c->display('init');
	}
	
	public function edit_access_list(){
		$permission = $this->c->queries('permission');
		$access_list = $this->c->queries('access_list');
		$root_list = $this->c->queries('root_list');
		if ($access_list){
			// delete all access first
			$p = $this->model->table('permissions');
			$p->cond('permission = '.$p->escape($permission));
			$p->delete();
			// add access
			foreach ($access_list as $name => $item){
				$p->set('table_name', $name);
				$p->set('permission', $permission);
				if (isset($root_list[$name])){
					$p->set('root', 1);
				}
				else {
					$p->set('root', 0);
				}
				$p->save();
			}
		}
		$this->c->redirect('/permissionmanager/edit_access/?permission='.$permission);
	}
}
?>