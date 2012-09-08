<?php 
Load::core('orm/Model');

class SelectModel extends Base{

	private $model;

	public function SelectModel(){
		$this->model = new Model('ref');
	}
	
	public function table($table){
		return $this->model->table($table);
	}

}
?>