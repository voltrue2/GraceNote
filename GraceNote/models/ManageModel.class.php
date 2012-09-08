<?php 
Load::core('orm/Model');

class ManageModel {

	private $model;
	private $table;
	private $tables;
	private $table_name;
	private $langs;
	
	public function ManageModel($table){
		$this->table_name = $table;
		$this->model = new Model('admin');
		$this->table = $this->model->table($table);
		$this->tables = $this->model->table('table_desc');
		$this->langs = $this->model->table('languages');
	}
	
	public function get_langs(){
		return $this->langs->find_all();
	}
	
	public function exec_select($sql, $params = false){
		return $this->table->get($sql, $params);
	}
	
	public function check_permission($permission_code){
		$permission = $this->model->table('permissions');
		$permission->cond('table_name = '.$permission->escape($this->table_name));
		$permission->cond('permission = '.$permission->escape($permission_code));
		return $permission->find();
	}
	
	public function set_column($name, $type, $default, $primary){
		$this->table->set_column($name, $type, $default, $primary);
	}
	
	public function set_multi_lingual($checkbox){
		if ($checkbox == 'on'){
			$this->table->set_column('lang_id', 'int');
		}
	}
	
	public function set_table_category($category){
		if ($category){
			$this->tables->set('category', $category);
		}
	}
	
	public function create(){
		try {
			$this->model->transaction();
			$this->table->set_auto_increment_column('id', false, 'primary');
			$res = $this->table->create();
			// create table description
			$this->tables->set('table_name', $this->table_name);
			$this->tables->set('description', 'Data table description. Please edit this text later.');
			$this->tables->save();
			// create root permission
			$permission = $this->model->table('permissions');
			$permission->set('table_name', $this->table_name);
			$permission->set('permission', 0);
			$permission->set('root', 1);
			$permission->save();
			$this->model->commit();
			return $res;
		}
		catch (Exception $error) {
			$this->model->rollback();
			return null;
		}
	}
	
	public function rename_column($from, $to){
		$res = $this->table->rename_column($from, $to);
		$this->table->delete_cache();
		return $res;
	}
	
	public function change_data_type($column, $type){
		$res = $this->table->change_data_type($column, $type);
		$this->table->delete_cache();
		return $res;
	}
	
	public function rename($from, $to){
		try {
			$this->model->transaction();
			// update the permission
			$permission = $this->model->table('permissions');
			$permission->set('table_name', $to);
			$permission->cond('table_name = ?', $from);
			$permission->delete_cache();
			$permission->save();
			// update the table_desc
			$this->tables->set('table_name', $to);
			$this->tables->cond('table_name = ?', $from);
			$this->tables->save();
			// rename the table
			$res = $this->table->rename($to);
			$this->table->delete_cache();
			$this->model->commit();
			return $res;
		}
		catch (Exception $error) {
			$this->model->rollback();
			return null;
		}
	}
	
	public function drop(){
		try {
			$this->model->transaction();
			// drop the table
			$this->table->drop();
			// delete table description
			$this->tables->cond('table_name = ?', $this->table_name);
			$this->tables->delete();
			// delete permission
			$permissions = $this->model->table('permissions');
			$permissions->cond('table_name = ?', $this->table_name);
			$res = $permissions->delete();
			$this->table->delete_cache();
			$this->model->commit();
			return $res;
		}
		catch (Exception $error) {
			$this->model->rollback();
			return null;
		} 
	}	
	
	public function data_types(){
		return $this->table->data_types();
	}
	
	public function describe(){
		$res = $this->table->show();
		if ($res){
			$table = array();
			foreach ($res as $item){
				$table[] = $item;
			}
			return $table;
		}
		else {
			return false;
		}
	}
	
	public function get_list($column = false, $search = false, $from = false, $to = false){
		if ($column && $search){
			if ($column == 'all'){
				$fields = $this->table->show();
				foreach ($fields as $f){
					if ($f['type'] == 'varchar' || $f['type'] == 'text'){
						$this->table->cond($f['field']." LIKE ".$this->table->escape('%'.$search.'%'), "OR");
					}
					if ($f['type'] == 'int' && is_numeric($search)){
						$this->table->cond($f['field']." = ?", $search, "OR");
					}
				}
			}
			else {
				if (!is_numeric($search)){
					$this->table->cond($column." LIKE ".$this->table->escape('%'.$search.'%'));
				}
				$this->table->cond($column." = ?", $search, "OR");
			}
		}
		$this->table->order('modtime', 'DESC');
		if ($from !== false && $to !== false){
			$this->table->limit($from, $to);
		}
		$list = $this->table->find();
		if (!isset($list[0])){
			return array($list);
		}
		else {
			return $list;
		}
	}
	
	public function get_list_total($column = false, $search = false){
		if ($column && $search){
			if ($column == 'all'){
				$fields = $this->table->show();
				foreach ($fields as $f){
					if ($f['type'] == 'varchar' || $f['type'] == 'text'){
						$this->table->cond($f['field']." LIKE ".$this->table->escape('%'.$search.'%'), "OR");
					}
					if ($f['type'] == 'int' && is_numeric($search)){
						$this->table->cond($f['field']." = ?", $search, "OR");
					}
				}
			}
			else {
				if (!is_numeric($search)){
					$this->table->cond($column." LIKE ".$this->table->escape('%'.$search.'%'));
				}
				$this->table->cond($column." = ?", $search, "OR");
			}
		}
		$this->table->select('count(*) as total');
		$res = $this->table->find();
		if (isset($res['total'])){
			return $res['total'];
		}
		else {
			return false;
		}
	}
	
	public function get($id){
		$this->table->cond($this->table_name.'.id = ?', $id);
		$res = $this->table->find();
		return $res;
	}
	
	public function get_desc(){
		$this->tables->cond('table_name = ?', $this->table_name);
		return $this->tables->find();
	}
	
	public function delete($id){
		$this->table->debug(false);
		$this->table->cond('id = ?', $id);
		$res = $this->table->delete();
		$this->table->delete_cache();
		return $res;
	}
	
	public function update($id, $params){
		foreach ($params as $key => $value){
			$this->table->set($key, $value);
		}
		$this->table->cond('id = ?', $id);
		$res = $this->table->save();
		$this->table->delete_cache();
		return $res;
	}
	
	public function insert($params){
		foreach ($params as $key => $value){
			$this->table->set($key, $value);
		}
		$res = $this->table->save();
		$this->table->delete_cache();
		return $res;
	}
	
	public function alter_table($name, $type, $default, $action){
		if ($action == 'add'){
			$this->table->set_column($name, $type, false, $default);
			$res = $this->table->add();
		}
		else if ($action == 'remove'){
			$this->table->set_column($name);
			$res = $this->table->remove();
		}
		$this->table->delete_cache();
		return $res;
	}
	
	public function save_table_desc($description, $category){
		$this->tables->cond('table_name = ?', $this->table_name);
		$this->tables->set('description', $description);
		$this->tables->set('category', $category);
		$res = $this->tables->save();
	}
	
	public function save_table_meta($column, $value){
		$this->tables->select('column_meta');
		$this->tables->cond('table_name = ?', $this->table_name);
		$meta = $this->tables->find();
		if ($meta['column_meta']){
			$data = unserialize($meta['column_meta']);
			$data[$column] = $value;
		}
		else {
			$data[$column] = $value;
		}
		$this->tables->cond('table_name = ?', $this->table_name);
		$this->tables->set('column_meta', serialize($data));
		return $this->tables->save();
	}
	
	public function read_table_meta(){
		$this->tables->select('column_meta');
		$this->tables->cond('table_name = ?', $this->table_name);
		$meta = $this->tables->find();
		if ($meta['column_meta']){
			return unserialize($meta['column_meta']);
		}
		else {
			return false;
		}
	}
}
?>
