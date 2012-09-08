<?php 
Load::core('orm/Model');

class AdminModel {
	
	private $model;
	/**** Tables ****/
	private $admins;
	private $permissions;
	private $tables;
	
	public function AdminModel(){
		$this->model = new Model('admin');
		$this->admins = $this->model->table('admins');
		$this->permissions = $this->model->table('permissions');
		$this->tables = $this->model->table('table_desc');
	}
	
	public function check_table(){
		$this->admins->select('id');
		$this->admins->where('id != '.$this->admins->escape(0));
		$this->admins->limit(0, 1);
		return $this->admins->find();
	}
	
	public function create_admin_table($root_password){
		$res = $this->check_table();
		if (!$res){
			// create admin user table
			$this->admins->set_auto_increment_column('id', false, 'primary');
			$this->admins->set_column('name', 'varchar', 12);
			$this->admins->set_column('password', 'varchar', 255);
			$this->admins->set_column('permission', 'int');
			$this->admins->set_column('media_restriction', 'varchar', 255);
			$this->admins->create();
			// create a root user
			$this->admins->set('name', 'root');
			$this->admins->set('password', md5($root_password));
			$this->admins->set('permission', 0);
			$this->admins->save();
			// create table description
			$this->tables->set_auto_increment_column('id', false);
			$this->tables->set_column('table_name', 'varchar', 30, false, 'primary');
			$this->tables->set_column('category', 'varchar');
			$this->tables->set_column('description', 'text');
			$this->tables->set_column('column_meta', 'text');
			$this->tables->create();
			$this->tables->set('table_name', 'admins');
			$this->tables->set('category', 'cms');
			$this->tables->set('description', 'The table for CMS admin users.');
			$this->tables->save();
			$this->tables->set('table_name', 'permissions');
			$this->tables->set('category', 'cms');
			$this->tables->set('description', 'The table for CMS admin user permissions to table access.');
			$this->tables->save();
			$this->tables->set('table_name', 'permission_names');
			$this->tables->set('category', 'cms');
			$this->tables->set('description', 'Names for permissions');
			$this->tables->save();
			$this->tables->set('table_name', 'table_desc');
			$this->tables->set('category', 'cms');
			$this->tables->set('description', 'Description for each table.');
			$this->tables->save();
			$this->tables->set('table_name', 'sessions');
			$this->tables->set('category', 'system');
			$this->tables->set('description', 'The data table to manage session data. In most cases, DO NOT touch this table.');
			$this->tables->save();
			$this->tables->set('table_name', 'languages');
			$this->tables->set('category', 'system');
			$this->tables->set('description', 'The table to hold language keys for text contents.');
			$this->tables->save();
			$this->tables->set('table_name', 'extended_cms');
			$this->tables->set('category', 'cms');
			$this->tables->set('description', 'Top horizontal menu for CMS');
			$this->tables->save();
			// create permission table
			$this->permissions->set_auto_increment_column('id', false, 'primary');
			$this->permissions->set_column('table_name', 'varchar', 30);
			$this->permissions->set_column('permission', 'int');
			$this->permissions->set_column('root', 'int');
			$this->permissions->set_key('permission', 'INDEX');
			$this->permissions->create();
			// create permission name table
			$permission_names = $this->model->table('permission_names');
			$permission_names->set_auto_increment_column('id', false, 'primary');
			$permission_names->set_column('name', 'varchar', 30);
			$permission_names->set_column('permission_id', 'int');
			$permission_names->create();
			// create the root permission name
			$permission_names->set('name', 'Root User');
			$permission_names->set('permission_id', 0);
			$permission_names->save();
			// create a root permission
			$this->permissions->set('table_name', 'admins');
			$this->permissions->set('permission', 0);
			$this->permissions->set('root', 1);
			$this->permissions->save();
			$this->permissions->set('table_name', 'permissions');
			$this->permissions->set('permission', 0);
			$this->permissions->set('root', 1);
			$this->permissions->save();
			$this->permissions->set('table_name', 'permission_names');
			$this->permissions->set('permission', 0);
			$this->permissions->set('root', 1);
			$this->permissions->save();
			$this->permissions->set('table_name', 'extended_cms');
			$this->permissions->set('permission', 0);
			$this->permissions->set('root', 1);
			$this->permissions->save();
			$this->permissions->set('table_name', 'table_desc');
			$this->permissions->set('permission', 0);
			$this->permissions->set('root', 1);
			$this->permissions->save();
			$this->permissions->set('table_name', 'sessions');
			$this->permissions->set('permission', 0);
			$this->permissions->set('root', 1);
			$this->permissions->save();
			$this->permissions->set('table_name', 'languages');
			$this->permissions->set('permission', 0);
			$this->permissions->set('root', 1);
			$this->permissions->save();
			// none table pages
			$this->permissions->set('table_name', 'menu');
			$this->permissions->set('permission', 0);
			$this->permissions->set('root', 1);
			$this->permissions->save();
			$this->permissions->set('table_name', 'cimgmaneger');
			$this->permissions->set('permission', 0);
			$this->permissions->set('root', 1);
			$this->permissions->save();
			$this->permissions->set('table_name', 'permission_manager');
			$this->permissions->set('permission', 0);
			$this->permissions->set('root', 1);
			$this->permissions->save();
			// create extended cms table
			$ext = $this->model->table('extended_cms');
			$ext->set_auto_increment_column('id', false, 'primary');
			$ext->set_column('lang_id', 'int');
			$ext->set_column('target', 'varchar');
			$ext->set_column('path', 'varchar');
			$ext->set_column('name', 'varchar');
			$ext->create();
			// create extended cms pages
			$ext->set('lang_id', 1);
			$ext->set('target', '_self');
			$ext->set('path', '/cimgmanager/');
			$ext->set('name', 'Content Images Management');
			$ext->save();
			$ext->set('lang_id', 1);
			$ext->set('target', '_self');
			$ext->set('path', '/menu/');
			$ext->set('name', 'Data Table Management');
			$ext->save();
			$ext->set('lang_id', 1);
			$ext->set('target', '_self');
			$ext->set('path', '/permission_manager/');
			$ext->set('name', 'CMS User Management');
			$ext->save();
			// create sessions table
			$s = new SessionModel();
			$s->create();
			// create language table
			$langs = $this->model->table('languages');
			$langs->set_auto_increment_column('id', false, 'primary');
			$langs->set_column('name', 'varchar', 30);
			$langs->set_column('lang_id', 'int');
			$langs->create();
			// create English language
			$langs->set('name', 'English');
			$langs->set('lang_id', 1);
			$langs->save();
		}
	}
	
	public function get_table_categories($permission){
		$this->tables->join('permissions');
		$this->tables->select('table_desc.category');
		$this->tables->where('permissions.permission = ?', $permission);
		$this->tables->and('permissions.table_name = table_desc.table_name');
		$this->tables->group('table_desc.category');
		return $this->tables->find_all();
	}
	
	public function get_tables($permission, $category = false, $from = false, $to = false){
		$this->permissions->join('table_desc');
		if ($category){
			$this->permissions->where('table_desc.category = '.$this->permissions->escape($category));
			$this->permissions->and('permissions.table_name = table_desc.table_name');
		}
		else {
			$this->permissions->where('permissions.table_name = table_desc.table_name');
		}
		$this->permissions->and('permissions.permission = '.$this->permissions->escape($permission));
		$this->permissions->order('table_desc.modtime', 'DESC');
		if ($from !== false && $to !== false){
			$this->permissions->limit($from, $to);
		}
		return $this->permissions->find_all();
	}
	
	public function get_table_total($permission, $category = false){
		$this->permissions->select('count(table_desc.table_name) as TOTAL');
		$this->permissions->join('table_desc');
		if ($category){
			$this->permissions->where('table_desc.category = ?', $category);
			$this->permissions->and('permissions.table_name = table_desc.table_name');
		}
		else {
			$this->permissions->where('permissions.table_name = table_desc.table_name');
		}
		$this->permissions->and('permissions.permission = ?', $permission);
		return $this->permissions->find();
	}	
}
?>