<?php 
Load::core('orm/Model');
//Load::lib('DBF');

class ContentModel {

	private $dbf;
	//private $joined_dbf = false;
	private $model;
	private $table_name;
	private $joined_tables = false;
	private$table;
	private $lang_id = false;
	
	public function ContentModel($table_name, $lang_id){
		$this->lang_id = $lang_id;
		if (is_array($table_name) && !empty($table_name)){
			$c = 0;
			foreach ($table_name as $i => $table) {
				if ($c == 0){
					$this->table_name = $table;
				}
				else {
					$this->joined_tables[] = $table;
					//$this->joined_dbf[] = new DBF($table);
				}
				$c++;
			}
			
		}
		else {
			$this->table_name = $table_name;
		}
		//$this->dbf = new DBF($this->table_name);
		$this->model = new Model('admin');
		$this->table = $this->model->table($this->table_name);
	}
	
	public function get(){
		//try dbf first
		/*
		$res = $this->dbf->get($this->lang_id);
		if ($this->joined_dbf){
			foreach ($this->joined_dbf as $dbf){
				$r = $dbf->get($this->lang_id);
				if (!empty($res) && !empty($r)){
					$res = array_merge($res, $r);
				}
			}
		}*/
		if (DISPLAY_ERRORS){
			$timer = new Timer(1);
		}
		$res = false;
		if (!$res){
			// now from db
			$this->table->cache();
			$this->table->cond($this->table_name.'.lang_id = ?', $this->lang_id);
			if ($this->joined_tables){
				foreach ($this->joined_tables as $table){
					$this->table->join($table);
					$this->table->cond($this->table_name.'.lang_id = '.$table.'.lang_id');
				}
			}
			$res = $this->table->find();
		}
		if (DISPLAY_ERRORS){
			Message::register('<span style="color: #0000CC;">Contents Retrieval for <strong>'.$this->table_name.'</strong> : <span style="color: #FF0000;">[ '.round($timer->get() * 1000, 4).' ms ]</span></span>');
		}
		return $res;
	}
	
	public function get_all($order = false, $lang = false){
		/*
		$res = $this->dbf->get($this->lang_id);
		if ($this->joined_dbf){
			foreach ($this->joined_dbf as $dbf){
				$r = $dbf->get($this->lang_id);
				if (!empty($res) && !empty($r)){
					$res = array_merge($res, $r);
				}
			}
		}
		*/
		if (DISPLAY_ERRORS){
			$timer = new Timer(1);
		}
		$res = false;
		if (!$res){
			if (isset($order[0]) && isset($order[1])){
				$this->table->order($order[0], $order[1]);
			}
			if ($this->lang_id){
				$this->table->column('lang_id');
				$res = $this->table->find_all($this->lang_id);
			}
			else {
				$res = $this->table->find_all();
			}
		}
		if (DISPLAY_ERRORS){
			Message::register('<span style="color: #0000CC;">Contents Retrieval for <strong>'.$this->table_name.'</strong> : <span style="color: #FF0000;">[ '.round($timer->get() * 1000, 4).' ms ]</span></span>');
		}
		return $res;
	}
}
?>
