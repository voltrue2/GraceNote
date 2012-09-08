<?php 
Load::core('orm/SQL');
Load::core('orm/ORM');

class Model {
	
	private $sql = false;
	private $type = false;
	private $tables = false;
	private $cache_exp = false;
	private $enable_cache = false;
	
	public function Model($db_conf_key){
		$this->sql = new SQL($db_conf_key); // create connection to DB
		$this->type = $this->sql->database_type(); // retrieve DB type
		$this->tables = array(); // initalize table -> in PHP this is not necessary
	}
	
	public function cache($expire = 0){
		$this->enable_cache = true;
		$this->cache_exp = $expire;
	}
	
	public function flush(){
		$this->sql->flush();
	}
	
	public function table($table){
		if (isset($this->tables[$table])){
			$table = $this->tables[$table];
			if ($this->enable_cache){
				$table->cache($this->cache_exp);
			}
			return $table;
		}
		else if ($table){
			$table = $this->tables[$table] = new ORM($this->sql, $this->type, $table);
			if ($this->enable_cache){
				$table->cache($this->cache_exp);
			}
			return $table;
		}
		else {
			return false;
		}
	}
	
	
	/***********************************
	             Transaction
	***********************************/
	public function transaction(){
		$this->sql->transaction();
	}
	
	public function rollback(){
		$this->sql->rollBack();
	}
	
	public function commit(){
		$this->sql->commit();
	}
}
?>
