<?php 
Load::core('orm/Model');

class EditModel {
	
	private $model;
	
	public function EditModel(){
		$this->model = new Model('admin');
	}
	
	public function table($table_name){
		return $this->model->table($table_name);
	}
	
	public function transaction(){
		$this->model->transaction();
	}
	
	public function rollback(){
		$this->model->rollBack();
	}
	
	public function commit(){
		$this->model->commit();
	}
}
?>