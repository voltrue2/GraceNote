<?php 

class ORM {
	
	private $sql = false;
	private $recursive_limit = 10; // don't think we need this 2011/08/11
	private $inflate_with = false;
	private $returning = false;
	private $table = false;
	private $type = false;
	private $column = false;
	private $columns = false;
	private $keys = false;
	private $select_data = false;
	private $conds = false;
	private $set_data = false;
	private $condition_data = false;
	private $recursive = false;
	private $assoc_data = false;
	private $assoc_tables = false;
	private $group_data = false;
	private $order_data = false;
	private $having_cond = false;
	private $limit_data = false;
	private $enable_cache = false; // we might remove this 2011/08/03
	private $use_cache = false;
	private $sort_order = '';
	private $quote = '"';
	private $mysql = 'mysql';
	private $psql = 'pgsql';
	private $collation = 'utf8';
	private $data_types;
	private $type_formats = false;
	private $escaped = false;
	private $all = false;
	private $inflate_threshhold = 5000; // if the result array exceeds this amount, the inflate method switches from exec_inflate to exec_inflate_each
	private $join_types = array('LEFT' => 1, 'RIGHT' => 1, 'INNER' => 1, 'OUTER' => 1, 'LEFT OUTER' => 1, 'LEFT INNER' => 1);
	
	public function ORM($connection, $db_type, $table){
		$this->sql = $connection;
		$this->type = $db_type;
		$this->table = $table;
		$this->build_data_types();
	}
	
	/***********************************
		General Methods
	***********************************/
	public function debug($echo = true){
		$this->sql->debugger(true, $echo);
	}
	
	public function debug_cache($echo = true){
		$this->sql->show_cache($echo);
	}
	
	public function name(){
		return $this->table;
	}
	
	// recursive select for foriegn keys
	public function recursive($limit = 0){
		$this->recursive = true;
		if ($limit){
			$this->recursive_limit = $limit;
		}
	}
		
	public function set_inflate_threshhold ($n) {
		$this->inflate_threshhold = $n;
	}
	
	// set up for inflate data retreival
	// $preconditions : Associative Array  
	// e.g. $preconditions = array('status' => 1, 'count' => 10) translates to status = 1 AND count = 10
	// e.g. $preconditions = array('status' => array('!=', 1), 'count' => array('>', 10)) translates to status != 1 AND count > 10
	public function inflate($table, $inflating_column, $src_table, $src_column, $preconditions = false){
		if (is_object($table) && method_exists($table, 'name')){
			$table = $table->name();
		}
		else if (is_object($src_table) && !method_exists($src_table, 'name')){
			return false;
		}
		if (is_object($src_table) && method_exists($src_table, 'name')){
			$src_table = $src_table->name();
		}
		else if (is_object($src_table) && !method_exists($src_table, 'name')){
			return false;
		}
		$this->inflate_with[$table][$inflating_column] = array($src_table => $src_column, 'preconds' => $preconditions);
		return $src_table;
	}
	
	// manually set/get cache with custom key and executes find_all
	public function cfind_all($key_src = null){
		// no cache key source -> use built-in cache
		if (!$key_src){
			$this->cache();
			return $this->find_all();
		}
		// generate cache key
		$key = $this->create_cache_key($key_src);
		// try to get cached data
		$res = $this->sql->get_cache($key);
		if (!$res){
			// execute sql
			$value = $this->find_all();
			// set data to cache
			$this->sql->set_cache($key, $value);
			// return the original data
			return $value;
		}
		else {
			// return the cached data
			return $res;
		}
	}

	// manually set/get cache with custom key and executes find
	public function cfind($key_src = null){
		// no cache key source -> use built-in cache
		if (!$key_src){
			$this->cache();
			return $this->find();
		}
		// generate cache key
		$key = $this->create_cache_key($key_src);
		// try to get cached data
		$res = $this->sql->get_cache($key);
		if (!$res){
			// execute sql
			$value = $this->find();
			// set data to cache
			$this->sql->set_cache($key, $value);
			// return the original data
			return $value;
		}
		else {
			// return the cached data
			return $res;
		}
	}

	private function create_cache_key($key){
		$t = '';
		if (!empty($this->assoc_tables)){
			$t = implode(':', $this->assoc_tables);
		}
		return $this->table.$t.':'.$key;
	}

	public function cache($enable = true){
		$this->use_cache = $enable;
	}

	public function delete_cache($key = false){
		if (!$key){
			// delete all cache data that containes this table
			$key = $this->table;
		}
		$this->sql->delete_cache($key);
	}
	
	public function database(){
		if ($this->type == $this->mysql){
			$sql = "SHOW DATABASES";
		}
		else if ($this->type == $this->psql){
			$sql = "SELECT datname FROM pg_database";
		}
		return $this->sql->getAll($sql);
	}
	
	public function show(){
		if ($this->type == $this->mysql){
			$sql = "DESCRIBE ".$this->table;
		}
		else if ($this->type == $this->psql){
			$sql = "SELECT column_name AS Field, data_type AS Type, column_default AS Default FROM information_schema.columns WHERE table_name = '".$this->table."'";
		}
		// cache this no matter what
		$res = $this->sql->get_cache($sql);
		if (!$res){
			if (DISPLAY_ERRORS){
				$timer = new Timer(1);
			}
			$res = $this->sql->getAll($sql);
			if (DISPLAY_ERRORS){
				Message::register('<strong>'.$sql.'</strong>&nbsp;took&nbsp;<span style="color: #FF0000;">'.round($timer->get() * 1000, 4).'&nbsp;ms&nbsp;</span>to&nbsp;execute.');
			}
			if ($res){
				foreach ($res as $i => $item){
					if (isset($this->type_formats[$item['type']])){
						$res[$i]['type'] = $this->type_formats[$item['type']];
					}
				}
			}
			$key = $this->sql->cache_key_gen($sql, false);
			$this->sql->set_cache($sql, $res);
		}
		return $res;
	}

	public function foreign_keys($alt_table = false){
		$table = $this->table;
		if ($alt_table){
			$table = $alt_table;
		}
		if (is_array($table)){
			$tmp = $table;
			$table = '';
			$total = count($tmp) - 1;
			foreach ($tmp as $i => $item){
				if ($i == $total){
					$table .= "'".$item."'";
				}
				else {
					$table .= "'".$item."', ";
				}
			}
		}
		else {
			$table = "'".$table."'";
		}
		if ($this->type == $this->mysql){
			$sql = "SELECT TABLE_NAME AS table_name, 
				COLUMN_NAME AS column_name, 
				CONSTRAINT_NAME AS constraint_name, 
				REFERENCED_TABLE_NAME AS foreign_table_name, 
				REFERENCED_COLUMN_NAME AS foreign_column_name
				FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
				WHERE TABLE_NAME IN (".$table.")";
		}
		else if ($this->type == $this->psql){
			$sql = "SELECT  tc.constraint_name,
				tc.table_name,
				kcu.column_name,
				ccu.table_name AS foreign_table_name,
				ccu.column_name AS foreign_column_name
				FROM information_schema.table_constraints AS tc 
				JOIN information_schema.key_column_usage AS kcu ON tc.constraint_name = kcu.constraint_name
				JOIN information_schema.constraint_column_usage AS ccu ON ccu.constraint_name = tc.constraint_name
				WHERE constraint_type = 'FOREIGN KEY'
				AND tc.table_name IN (".$table.")";
		}
		// cache no matter what
		$key = $this->sql->cache_key_gen($sql, false);
		$res = $this->sql->get_cache($key);
		if (!$res){
			if (DISPLAY_ERRORS){
				$timer = new Timer(1);
			}
			$res = $this->sql->getAll($sql);
			if (DISPLAY_ERRORS){
				Message::register('<strong>'.$sql.'</strong>&nbsp;took&nbsp;<span style="color: #FF0000;">'.round($timer->get() * 1000, 4).'&nbsp;ms&nbsp;</span>to&nbsp;execute.');
			}
			$key = $this->sql->cache_key_gen($sql, false);
			$this->sql->set_cache($key, $res);
		}
		return $res;
	}
	
	public function data_types(){
		return $this->data_types[$this->type];
	}
	
	public function year($t){
		return $this->time_func($t, 'YEAR');
	}
	
	public function month($t){
		return $this->time_func($t, 'MONTH');
	}
	
	public function day($t){
		return $this->time_func($t, 'DAY');
	}
	
	public function hour($t){
		return $this->time_func($t, 'HOUR');
	}
	
	public function minute($t){
		return $this->time_func($t, 'MINUTE');
	}
	
	public function second($t){
		return $this->time_func($t, 'SECOND');
	}
	
	public function column($c){
		$this->column = $c;
	}
	
	public function charset($c){
		$this->collation = $c;
	}
	
	/***********************************
		   Query Methods
	***********************************/
	// 'column' OR 'column AS name'
	public function select($value){
		$this->select_data[] = $value;
	}

	public function where(){
                $args = func_get_args();
                $cond = $args[0];
                if (count($args) > 1){
                        $params = array_slice($args, 1);
                }
                else {
                        $params = null;
                }
                $this->_condition('WHERE', $cond, $params);
        }

	public function __call($method, $args = null){
                call_user_func_array(array($this, '_'.$method), $args);
        }

	private function _and(){
		$args = func_get_args();
		$cond = $args[0];
		if (count($args) > 1){
			$params = array_slice($args, 1);
		}
		else {
			$params = null;
		}
		$this->_condition('AND', $cond, $params);	
	}

	private function _or(){
                $args = func_get_args();
                $cond = $args[0];
                if (count($args) > 1){
                        $params = array_slice($args, 1);
                }
                else {
                        $params = null;
                }
                $this->_condition('OR', $cond, $params);
        }
	
	private function _condition($clause, $cond, $params){
		// handle parameters for escaped values
		if ($params){
			if (!isset($this->conds['parameters'])){
                        	 $this->conds['parameters'] = array();
                	}       
                	foreach ($params as $value){
				if (is_array($value)){
                        		if (!empty($value)){
                                        	foreach ($value as $item){
							$this->conds['parameters'][] = $item;
                                		}
					}
					else {
						$this->conds['parameters'][] = false;
					}       
                        	}       
                		else {
                        		$this->conds['parameters'][] = $value;
                		}
			}
		}
		// handle conditional statement
		if (!isset($this->conds['query'])){
                        $this->conds['query'] = '';
                }
		$this->conds['query'] .= ' '.$clause.' '.$cond;	
	}

	public function escape(){
		$args = func_get_args();
		if (count($args) > 1){
			$value = $args;
		}
		else {
			$value = $args[0];	
		}
		$this->escaped = true;
		if (!isset($this->conds['parameters'])){
			$this->conds['parameters'] = array();
		}
		if (is_array($value)){
			if (!empty($value)){
				$e = '(';
				$c = 0;
				$t = count($value) - 1;
				foreach ($value as $i => $item){
					$tail = ',';
					if ($c == $t){
						$tail = '';
					}
					$c++;
					$e .= '?'.$tail;
					$this->conds['parameters'][] = $item;
				}
				return $e.')';
			}
			else{
				$this->conds['parameters'][] = false;
				return '(?)';
			}
		}
		else {
			$this->conds['parameters'][] = $value;
			return '?';
		}
	}
	
	// depricated as of Aug/11th/2011
	public function cond($cond, $values = false, $clause = 'and'){
		if ($this->escaped){
			// escaped already -> we will use the 2nd argument as $clause
			if (!$values){
				$values = 'and';
			}
			$values = trim(strtolower($values));
			if ($values == 'and' || $values == 'or'){
				$clause = $values;
			}
		}
		if (!isset($this->conds['query'])){
			$key = 0;
			$clause = ' where';
		}
		else {
			$key = count($this->conds);
		}
		if (!isset($this->conds['query'])){
			$this->conds['query'] = '';
		}
		$this->conds['query'] .= strtoupper($clause).' '.$cond.' ';
		if (!$this->escaped){
			if (!isset($this->conds['parameters'])){
				$this->conds['parameters'] = array();
			}
			if (is_array($values) && !empty($values)){
				foreach ($values as $value){
					if ($value !== false){
						$this->conds['parameters'][] = $value;
					}
				}
			}
			else {
				if ($values !== false){
					$this->conds['parameters'][] = $values;
				}
			}
		}
		$this->escaped = false;
	}

	// 'table name'
	public function join($value, $type = null, $cond = null){
		$table = $value;
		$type = strtoupper($type);
		if (!isset($this->join_types[$type])){
			$type = null;
		}
		if ($cond){
			$value = ' '.$type.' JOIN '.$value.' '.$cond.' ';
		}
		else {
			$value = $value;
		}
		// escape parameters
		$params = func_get_args();
		if (count($params) > 3){
			if (!isset($this->conds['parameters'])){
				$this->conds['parameters'] = array();
			}
			foreach ($params as $i => $p){
				if ($i > 2){
					if (is_array($p)){
						foreach ($p as $v){
							$this->conds['parameters'][] = $v;
						}
					}
					else {
						$this->conds['parameters'][] = $p;
					}
				}
			}
		}
		$this->assoc_data[] = $value;
		$this->assoc_tables[] = $table;
		return $table;
	} 
	 
	public function group($value){
		$this->group_data[] = $value;
	} 
	
	// second argument = DESC or ASC
	public function order($value, $order = false){
		$this->order_data[] = $value;
		if ($order){
			$this->sort_order = $order;
		}
	} 
	
	public function having($c){
		$this->having_cond = ' HAVING '.$c.' ';
		$params = func_get_args();
		if (count($params) > 1){
			if (!isset($this->conds['parameters'])){
				$this->conds['parameters'] = array();
			}
			foreach ($params as $i => $p){
				if ($i > 0){
					if (is_array($p)){
						foreach ($p as $v){
							$this->conds['parameters'][]= $v;
						}
					}
					else {
						$this->conds['parameters'][] = $p;
					}
				}
			}
		}
	}
	
	public function limit($f, $t){
		if ($this->type == $this->mysql){
			$this->limit_data = ' LIMIT '.$f.', '.$t;
		}
		else if ($this->type == $this->psql){
			$this->limit_data = 'LIMIT '.$t.' OFFSET '.$f;
		}
	}

	private function eval_clause($clause){
		$clause = strtolower(str_replace(' ', '', $clause));
		if ($clause != 'and' && $clause != 'or'){
			// default
			$clause = 'and';
		}
		if (empty($this->condition_data)){
			$clause = 'where';
		}
		return ' '.$clause.' ';
	}  
	
	/***********************************
	          Data Type Methods
	***********************************/
	
	/***********************************
	       Excecute Raw SQL Methods
	***********************************/
	public function get($sql, $params = null){
		$res = $this->sql->getAll($sql, $params);
		return $this->push($res);
	}
	
	public function send($sql, $params = null){
		if ($this->enable_cache){
			$this->sql->delete_cache();	
		}
		return $this->exec_send($sql, $params);
	}
	
	/***********************************
		Select Method
	***********************************/
	// returns numberic multi-dimensional array for multiple results OR returns associative array for a single result
	public function find($value = false){
		$res = $this->get_results($value);
		return $this->push($res);	
	}
	
	// returns numberic multi-dimensional array no matter what
	public function find_all($value = false){
		$this->all = true;
		return $this->get_results($value, true);
	}
	
	private function get_results($value){
		if (DISPLAY_ERRORS){
			$timer = new Timer(1);
		}
		if (!$value){
			// construct query select
			$select = $this->get_select();
			// construct table association
			$assoc = $this->get_association();
			// construct group by
			$group = $this->get_group();
			// construct order 
			$order = $this->get_order();
			// limit 
			$limit = $this->limit_data;
		}
		else {
			$select = $this->get_select();
			if (!$select){
				$select = '*';
			}
			if (!$this->column){
				$this->column = 'id';
			}
			$conds = " WHERE ".$this->column." = ?";
			$params = $value;
			$assoc = '';
			$group = '';
			$order = '';
			$limit = '';
		}
		if ($this->conds){
			$conds = '';
			$params = null;
			if (isset($this->conds['query'])){
				$conds = $this->conds['query'];
			}
			if (isset($this->conds['parameters'])){
				$params = $this->conds['parameters'];
			}
		}
		else if (!isset($conds) && !isset($params)){
			$conds = '';
			$params = false;
		}
		$having = '';
		if ($this->having_cond){
			$having = $this->having_cond;
		}	
		$sql = "SELECT ".$select." FROM ".$this->table.$assoc.$conds.$group.$order.$having.$limit;
		// check cache
		$res = false;		
		if ($this->use_cache){
			$key = $this->sql->cache_key_gen($sql, $params);
			$res = $this->sql->get_cache($key);
			if ($res){
				$cache_get = true;
			}
		}
		if (!$res){
			$res = $this->sql->getAll($sql, $params);
			// set cache
			if ($this->use_cache){
				$this->sql->cache_key_gen($sql, $params);
				$this->sql->set_cache($key, $res);
			} 
		}
		// check for foreign key recursive selects
		if ($this->recursive && $res){
			if ($this->assoc_data){
				$tables = array_merge(array($this->table), $this->assoc_data);
			}
			else {
				$tables = $this->table;
			}
			$fk = $this->foreign_keys($tables);
			if ($fk){
				// we have some foreign keys
				$this->recursive_get($fk, $res);
			}
		}
		// check for inflate retreival
		if ($this->inflate_with){
			$res = $this->inflate_get($res, $this->inflate_with);
		}
		$this->flush();
		if (DISPLAY_ERRORS && !isset($cache_get)){
			Message::register('<strong>'.$sql.'</strong>&nbsp;took&nbsp;<span style="color: #FF0000;">'.round($timer->get() * 1000, 4).'&nbsp;ms&nbsp;</span>to&nbsp;execute.');
		}
		return $res;
	}

	private function recursive_get($fk, &$res, $loop = 0){
		if ($loop >= $this->recursive_limit){
			// exceeded the limit
			return;
		}
		if ($fk && $res){
			// go through the list of results
			foreach($res as $i => $item){
				// check for the foreign keys
				foreach ($fk as $j => $f){
					$column = $f['column_name'];
					$fcolumn = $f['foreign_column_name'];
					$ftable = $f['foreign_table_name'];					
					if ($ftable && isset($item[$fcolumn])){
						// get associated data
						if (isset($item[$column])){
							$sql = "SELECT * FROM ".$ftable." WHERE ".$fcolumn." = ".$item[$column];
							if (DISPLAY_ERRORS){
								$timer = new Timer(1);
							}
							$ret = $this->get($sql);
							if (DISPLAY_ERRORS){
								Message::register('<strong>'.$sql.'</strong>&nbsp;took&nbsp;<span style="color: #FF0000;">'.round($timer->get() * 1000, 4).'&nbsp;ms&nbsp;</span>to&nbsp;execute.');
							}
							$next_fk = $this->foreign_keys($ftable);
							// get more 
							if ($next_fk && $ftable){
								$ret = array(0 => $ret);
								$loop++;
								$this->recursive_get($next_fk, $ret, $loop);
							}
							if (!$this->all && count($ret) == 1){
								$ret = $ret[0];
							}
							$res[$i][$column] = $ret;
						}
					}
				}
			}
		}
	}
	
	/*****************************************************************
	- Inflates a given list of data with chosen columns and tables
	$with: Array > $with[column to inflate] => array(
				target table => target column
			)
	*****************************************************************/
	private function inflate_get($list, $with){
		if ($this->assoc_data){
			$tables = array_merge(array($this->table), $this->assoc_tables);
		}
		else {
			$tables = array($this->table);
		}
		$res_amount = count($list);
		foreach ($tables as $table){
			if (isset($with[$table])){
				if ($res_amount > $this->inflate_threshhold) {
					$list = $this->exec_inflate_each($list, $with, $table);				
				}
				else {
					$list = $this->exec_inflate($list, $with, $table);
				}
			}
		}
		return $list;
	}
	
	/*****************************************************************
	Note: This method is slower compare to exec_inflate but uses
	      far less memory thus more suitable to handle large amount of
	      data. For small amount of data use exe_inflate method instead
	Caution: This method exectues an SQL query per row therefore
    	 	 the work load of DB server(s) and connection may be very high
	*****************************************************************/
	private function exec_inflate_each ($list, $with, $target_table) {
		// prepare the inflate query
		$resource = $with[$target_table];
		$queries = array();
		foreach ($resource as $src_column => $att) { 
			$optional_conds = '';
			$c = 0;
			foreach ($att as $key => $value) {
				if (!is_array($value) && $key !== 'preconds') {
					// reference table
					$ref_table = $key;
					$ref_column = $value;
					$queries[$src_column]['table'] = $ref_column;
				}
				else {
					// optional conditions
					foreach ($value as $col => $cond) {
						if (isset($cond[0])) {
							$op = ' ' . $cond[0] . ' ? ';
						}
						else {
							$op = ' = ? ';
						}
						$queries[$src_column]['params'][] = $cond[1];
						$glue = ' AND ';
						if (!$c) {
							$glue = '';
							$c = 1;
						}
						$optional_conds .= $glue . $col . $op;
					}
				}
			}
			if ($optional_conds) {
				$ref_column = 'AND ' . $ref_column;
			}	
			$queries[$src_column]['sql'] = 'SELECT * FROM ' . $ref_table . ' WHERE ' . $optional_conds . $ref_column . ' = ? ';
		}
		// inflate the data 
		foreach ($list as $i => $item) {
			// search through columns
			foreach ($item as $col => $v) {
				if (isset($queries[$col])) {
					$table = $queries[$col]['table'];
					$sql = $queries[$col]['sql'];
					$params = $queries[$col]['params'];
					$params[] = $v;
					$res = null;
					if ($this->use_cache){
						$res = $this->sql->get_cache($sql);
					}
					if (!$res) {
						if (DISPLAY_ERRORS){
							$timer = new Timer(1);
						}
						$res = $this->get($sql, $params);	
						if (DISPLAY_ERRORS){
							Message::register('<strong>'.$sql.'</strong>&nbsp;took&nbsp;<span style="color: #FF0000;">'.round($timer->get() * 1000, 4).'&nbsp;ms&nbsp;</span>to&nbsp;execute.');
						}
						// set cache
						if ($this->use_cache && $res) {
							$key = $this->sql->cache_key_gen($sql, false);
							$this->sql->set_cache($sql, $res);
						}
					}
					// check recursively
					if (isset($with[$table])) {
						$res = $this->exec_inflate_each($res, $with, $table);
					}
					if ($res) {
						$list[$i][$col] = $res;
					}
				}
			}	
		}
		return $list;
	}
	/*****************************************************************
	Note: This method is faster with small amount of data but CAN NOT 
	      handle large amount of data > for large amount of data 
	      use exec_inflate_each method instead
	Caution: This method is very memory intense therefore should never 
		 be used to handle large amount of data
	*****************************************************************/	
	private function exec_inflate($list, $with, $target_table){
		$single = false;
		if (!isset($list[0])){
			$list = array($list);
			$single = true;
		}
		$resource = false;
		foreach ($list as $item){
			foreach ($with[$target_table] as $column => $att){
				if (isset($item[$column])){
					if (isset($resource[$column]['value'])){
						if (array_search($item[$column], $resource[$column]['value']) === false){
							$resource[$column]['value'][] = $item[$column];
						}
					}
					else {
						$resource[$column]['value'][] = $item[$column];
					}
					$resource[$column]['attributes'] = $att;
				}
			}
		}
		// create query
		if ($resource){
			foreach ($resource as $column => $item){
				$num = count($item['value']);
				$vals = '(';
				for ($i = 0; $i < $num; $i++){
					$tail = ',';
					if ($i == $num - 1){
						$tail = '';
					}
					$vals .= '?'.$tail;
				}
				$vals .= ')';
				$preconds = '';
				if ($item['attributes']['preconds']){
					$pt = count($item['attributes']['preconds']) - 1;
					$pc = 0;
					foreach ($item['attributes']['preconds'] as $field => $val){
						if ($pc == 0){
							$preconds = ' AND ';
						}
						if (is_array($val)){
							$cps = $field.' '.$val[0].' ?';
							$item['value'][] = $val[1];
						}
						else {
							$cps = $field.' = ?';
							$item['value'][] = $val;
						}
						$ptail = ' AND ';
						if ($pt == $pc){
							$ptail = '';
						}
						$preconds .= $cps.$ptail;
						$pc++;
					}
				}
				foreach ($item['attributes'] as $table_name => $target_column){
					if ($table_name != 'preconds'){
						$sql = "SELECT * FROM ".$table_name." WHERE ".$target_column." IN ".$vals.$preconds;
						// check cache
						$res = false;		
						if ($this->use_cache){
							$res = $this->sql->get_cache($sql);
						}
						if (!$res){
							if (DISPLAY_ERRORS){
								$timer = new Timer(1);
							}
							$res = $this->get($sql, $item['value']);
							if (DISPLAY_ERRORS){
								Message::register('<strong>'.$sql.'</strong>&nbsp;took&nbsp;<span style="color: #FF0000;">'.round($timer->get() * 1000, 4).'&nbsp;ms&nbsp;</span>to&nbsp;execute.');
							}
							// set cache
							if ($this->use_cache){
								$key = $this->sql->cache_key_gen($sql, false);
								$this->sql->set_cache($sql, $res);
							} 
						}
						if ($res){
							if (!isset($res[0])){
								$res = array($res);
							}
							$next = $table_name;
							if (isset($with[$next])){
								$res = $this->exec_inflate($res, $with, $next);
							}
							foreach ($res as $i => $item){
								if (!is_array($item[$target_column])){
									$id = (string)$item[$target_column];
									$resource[$column]['results'][$id][] = $item;
								}
							}
						}
					}
				}
			}
			// distribute the results
			foreach ($list as $i => $item){
				foreach ($resource as $column => $data){
					if (isset($item[$column])){
						if (isset($data['results'][$item[$column]])){
							$res_data = $data['results'][$item[$column]];
							if (count($res_data) === 1) {
								$res_data = $res_data[0];
							}
							$list[$i][$column] = $res_data;
						}
					}
				}
			}
		}
		if ($single){
			$list = $list[0];
		}
		return $list;
	}

	/***********************************
	     Insert/Update/Delete Methods
	***********************************/
	public function set($column, $value){
		$this->set_data[] = array('column' => $column, 'value' => $value);
	}
	
	public function save(){
		if ($this->conds){
			$conds = $this->conds;
		}
		else {
			$conds = array('query' => false);
		}
		$assoc = $this->get_association();
		if ($conds['query']){
			// update
			$cond_query = $conds['query'];
			$params = $conds['parameters'];
			$set = $this->get_update_set();
			$sql = "UPDATE ".$assoc.$this->table." SET ".$set['statement'].", modtime = NOW() ".$cond_query;
			if ($set['parameters']){
				$params = array_merge($set['parameters'], $params);
			}
		}
		else {
			// insert
			$set = $this->get_insert_set();
			$params = $set['parameters'];
			$sql = "INSERT INTO ".$this->table." ".$set['statement'];
			if ($this->type == $this->psql){
				$table_data = $this->show();
				if ($table_data){
					foreach ($table_data as $item){
						if (strpos($item['default'], 'nextval') !== false){
							$this->returning = $item['field'];
							break;
						}
					}
				}
			}
			if ($this->returning){
				$sql .= ' RETURNING '.$this->returning;
			}
		}
		$this->remove_cache();
		return $this->exec_send($sql, $params);
	}
	
	public function update(){
		if ($this->conds){
			$conds = $this->conds;
		}
		$assoc = $this->get_association();
		if ($conds['query']){
			// update
			$cond_query = $conds['query'];
			$params = $conds['parameters'];
			$set = $this->get_update_set();
			$sql = "UPDATE ".$assoc.$this->table." SET ".$set['statement'].", modtime = NOW() ".$cond_query;
			if ($set['parameters']){
				$params = array_merge($set['parameters'], $params);
			}
		}
		else {
			// update
			$cond_query = '';
			$set = $this->get_update_set();
			$sql = "UPDATE ".$assoc.$this->table." SET ".$set['statement'].", modtime = NOW() ".$cond_query;
			$params = $set['parameters'];
		}
		$this->remove_cache();
		return $this->exec_send($sql, $params);
	}
	
	public function delete(){
		if ($this->conds){
			$res = $this->conds;
		}
		if ($res['query']){
			$sql = "DELETE FROM ".$this->table.$res['query'];
			$res = $this->exec_send($sql, $res['parameters']);
			$this->remove_cache();
			return $res;
		}
		else {
			return false;
		}
	}
	
	private function get_update_set(){
		if ($this->set_data){
			$str = '';
			$params = false;
			$total = count($this->set_data) - 1;
			foreach ($this->set_data as $i => $item){
				$str .= $item['column'].' = ?'.$this->tail($i, $total);
				$params[] = $item['value'];
			}
			return array('statement' => $str, 'parameters' => $params);
		}
	}

	private function get_insert_set(){
		if ($this->set_data){
			$cols = ' (';
			$vals = ' VALUES(';
			$params = false;
			$total = count($this->set_data) - 1;
			foreach ($this->set_data as $i => $item){
				$tail = $this->tail($i, $total);
				$cols .= $item['column'].$tail;
				$vals .= '?'.$tail;
				$params[] = $item['value'];
			}
			$cols .= ')';
			$vals .= ')';
			$str = $cols.$vals;
			return array('statement' => $str, 'parameters' => $params);
		}
	}
	
	/***********************************
	     Create/Alter/Drop Methods
	              (Table)
	***********************************/
	public function set_auto_increment_column($name, $size = null, $primary = null){
		if ($this->type == $this->mysql){
			// mysql
			$c = $name." INT";
			if ($size){
				$c .= "(".$size.") ";
			}
			$c .= " NOT NULL AUTO_INCREMENT ";
			if ($primary){
				$c .= " PRIMARY KEY";
			}
		}
		else if ($this->type == $this->psql){
			// postgresql
			$c = $name." SERIAL";
			if ($size){
				$c .= "(".$size.")";
			}
			if ($primary){
				$c .= " PRIMARY KEY";
			}
		}
		$this->columns[] = $c;
	}
	
	public function set_column($name, $type = false, $size = false, $default = false, $primary = false){
		if (!isset($this->data_types[$this->type][$type])){
			$type = "";
		}
		else {
			$type = $this->data_types[$this->type][$type];
			if ($size !== false){
				$type .= "(".$size.") ";
			}
		}
		if ($default !== false){
			$type .= " NOT NULL ";
			$type .= " DEFAULT ".$default." ";
		}
		$c = $name." ".$type;
		if ($primary){
			$c .= " PRIMARY KEY";
		}
		$this->columns[] = $c;
	}
	
	public function set_key($column, $key_type = ''){
		if ($this->type == $this->mysql && $key_type){
			// PRIMARY KEY, UNIQUE, INDEX
			$this->keys[] = $key_type.'('.$column.')';
		}
		else if ($this->type == $this->psql){
			// UNIQUE
			$this->keys[] = "CREATE ".$key_type." ".$column."_index ON ".$this->table." (".$column.")";
		}
	}
	
	/* example
	$test = new ORM('sql object', 'db', 'test');
	$test->auto_increment_column('id', false, 'primary key');
	$test->set_column('title', 'varchar', 100, '');
	$test->set_column('body', 'text');
	$test->create();
	-> resultes in postgresql
	 CREATE TABLE test (id SERIAL PRIMARY KEY, title varchar(100) NOT NULL , body text NOT NULL , modtime timestamp DEFAULT current_timestamp);
	*/
	public function create($mysql_engine = false){
		if ($mysql_engine){
			if (!$mysql_engine){
				$mysql_engine = 'InnoDB'; // default
			}
			// MyISAM or InnoDB
			$mysql_engine = "ENGINE=".$mysql_engine;
		}
		else {
			$mysql_engine = "";
		}
		if ($this->type == $this->mysql){
			// mysql charset
			$mysql_collation = " CHARSET=".$this->collation;
			// mysql table check
			$mysql_table_check = "IF NOT EXISTS";
		}
		else {
			$mysql_collation = "";
			$mysql_table_check = "";
		}
		// build columns
		if ($this->columns){
			$c = '';
			$total = count($this->columns);
			foreach ($this->columns as $i => $item){
				$c .= $item.$this->tail($total, $i);
			}
			// append modtime column
			$c .= $this->create_modtime();
		}
		// set keys mysql
		if ($this->keys && $this->type == $this->mysql){
			$c .= ', ';
			$total = count($this->keys) - 1;
			foreach ($this->keys as $i => $key){
				$c .= $key.$this->tail($total, $i);
			}
		}
		$sql = "CREATE TABLE ".$mysql_table_check." ".$this->table." (".$c.") ".$mysql_engine.$mysql_collation;
		$res = $this->exec_send($sql);
		// set keys postgresql
		if ($this->keys && $this->type == $this->psql){
			foreach ($this->keys as $query){
				$this->exec_send($query);
			}
		}
		if ($this->enable_cache){
			$this->sql->delete_cache();	
		}
		return $res;
	}
	
	// add columns
	public function add(){
		$c = 0;
		// build columns
		if ($this->columns){
			$total = count($this->columns);
			foreach ($this->columns as $i => $item){
				$query = "ALTER TABLE ".$this->table." ADD COLUMN ".$item;
				$res = $this->exec_send($query);
				if ($res){
					$c++;
				}
			}
		}
		// set keys postgresql
		if ($this->keys && $this->type == $this->psql){
			foreach ($this->keys as $query){
				$this->exec_send($query);
			}
		}
		if ($this->enable_cache){
			$this->sql->delete_cache();	
		}
		return $c;
	}
	
	// drop columns
	public function remove(){
		// build columns
		if ($this->columns){
			$total = count($this->columns);
			$c = 0;
			foreach ($this->columns as $i => $item){
				$query = "ALTER TABLE ".$this->table." DROP COLUMN ".substr($item, 0, strpos($item, ' '))." CASCADE";
				$res = $this->exec_send($query);
				if ($res){
					$c++;
				}
			}
		}
		if ($this->enable_cache){
			$this->sql->delete_cache();	
		}
		return $c;
	}
	
	// rename table -> tested postgresql only as of 2011/04/29
	public function rename($name){
		if ($this->type == $this->mysql){
			$query = "RENAME TABLE ".$this->table." TO ".$name;
		}
		else if ($this->type == $this->psql){
			$query = "ALTER TABLE ".$this->table." RENAME TO ".$name;
		}
		$res = $this->exec_send($query);
		$this->delete_cache();
		return $res;
	}
	
	// rename column
	public function rename_column($column_from, $column_to){
		if ($this->type == $this->mysql){
			// MySQL untested as of 2011/04/29
			$fields = $this->show();
			$column_attributes = '';
			if ($fields){
				foreach ($fields as $i => $item){
					if ($item['Field'] == $column_from){
						$column_attributes = $item['Type'];
					}
				}
			}
			$query = "ALTER TABLE ".$this->table." CHANGE ".$column_from." ".$column_to." ".$column_attributes;
		}
		else if ($this->type == $this->psql){
			$query = "ALTER TABLE ".$this->table." RENAME COLUMN ".$column_from." TO ".$column_to;
		}
		$res = $this->exec_send($query);
		if ($this->enable_cache){
			$this->sql->delete_cache();	
		}
		return $res;
	}
	
	// change data type of a column -> tested postgresql only as of 2011/04/29
	public function change_data_type($column, $data_type){
		$query = "ALTER TABLE ".$this->table." ALTER COLUMN ".$column." TYPE ".$data_type;
		$res = $this->exec_send($query);
		if ($this->enable_cache){
			$this->sql->delete_cache();	
		}
		return $res;
	}
	
	// drop table 
	public function drop(){
		$q = "DROP TABLE ".$this->table;
		if ($this->enable_cache){
			$this->sql->delete_cache();	
		}
		return $this->exec_send($q);
	}
	
	private function create_modtime(){
		if ($this->type == $this->mysql){
			return "modtime TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP";
		}
		else if ($this->type == $this->psql){
			return "modtime timestamp DEFAULT current_timestamp";
		}
	}
	
	/***********************************
		General Private Methods
	***********************************/
	private function time_func($t, $type){
		if ($this->type == $this->mysql){
			return $type."(".$t.")";	
		}
		else if ($this->type == $this->psql){
			return "EXTRACT(".$type." FROM ".$t.")";
		}
	}
	
	private function get_select(){
		if ($this->select_data){
			$str = '';
			$total = count($this->select_data) - 1;
			foreach ($this->select_data as $i => $value){
				$str .= $value.$this->tail($i, $total);
			}
			return $str;
		}
		else {
			return '*';
		}
	}

	private function get_association(){
		if ($this->assoc_data){
			$str = "";
			$total = count($this->assoc_data) - 1;
			foreach ($this->assoc_data as $i => $item){
				if (strpos($item, 'JOIN') !== false){
					$str .= $item." ";
				}
				else {
					$str .= ", ".$item;
				}
			}
			return $str;
		}
		else {
			return "";
		}
	}
	
	private function get_group(){
		if ($this->group_data){
			$str = " GROUP BY ";
			$total = count($this->group_data) - 1;
			foreach ($this->group_data as $i => $item){
				$str .= $item.$this->tail($i, $total);
			}
			return $str;
		}
		else {
			return '';
		}
	}
	
	private function get_order(){
		if ($this->order_data){
			$str = " ORDER BY ";
			$total = count($this->order_data) - 1;
			foreach ($this->order_data as $i => $item){
				$str .= $item.$this->tail($i, $total);
			}
			return $str.' '.$this->sort_order.' ';
		}
		else {
			return '';
		}
	}
	
	private function tail($i, $total){
		if ($i == $total){
			$tail = ' ';
		}
		else {
			$tail = ', ';
		}
		return $tail;
	}
	
	private function push($res){
		if ($res){
			if (isset($res[1])){
				return $res;
			}
			else {
				return $res[0];
			}
		}
		else {
			return false;
		}	
	}
	
	private function remove_cache(){
		if ($this->use_cache){
			$this->sql->delete_cache($this->table);	
		}
	}

	private function exec_send($sql, $params = null){
                $res = $this->sql->send($sql, $params);
                $last_id = false;
                if ($this->type == $this->mysql){
                        $last_id = $res['statement']->lastInsertId();
                }
                else if ($this->type == $this->psql){
                        $r = $res['statement']->fetch(PDO::FETCH_ASSOC);
                        if ($r){
                                if (isset($r[$this->returning])){
                                        $last_id = $r[$this->returning];
                                }
                        }
                }
                if (DISPLAY_ERRORS){
			Message::register('<strong>'.$sql.'</strong>');
		}
                $this->flush();
                if ($res['rows']){
                        return array('rows' => $res['rows'], 'last_id' => $last_id);
                }
                else {
                        return false;
                }
        }

	
	private function flush(){
		$this->set_data = false;
		$this->column = false;
		$this->columns = false;
		$this->keys = false;
		$this->select_data = false;
		$this->condition_data = false;
		$this->conds = false;
		$this->assoc_data = false;
		$this->assoc_tables = false;
		$this->group_data = false;
		$this->order_data = false;
		$this->having_cond = false;
		$this->limit_data = false;
		$this->sort_order = '';
		$this->recursive = false;
		$this->returning = false;
		$this->inflate_with = false;
		$this->escaped = false;
		$this->all = false;
		$this->use_cache = false;
		$this->inflate_threshhold = false;
	}
	
	private function build_data_types(){
		$this->data_types = array();
		// MYSQL
		$this->data_types[$this->mysql] = array();
		// text types
		$this->data_types[$this->mysql]['char'] = 'CHAR'; // 0 to 255
		$this->data_types[$this->mysql]['varchar'] = 'VARCHAR'; // 0 to 255
		$this->data_types[$this->mysql]['tinytext'] = 'TINYTEXT'; // 0 to 255
		$this->data_types[$this->mysql]['text'] = 'TEXT'; // 0 to 65535
		$this->data_types[$this->mysql]['blob'] = 'BLOB'; // 0 to 65535
		$this->data_types[$this->mysql]['mediumtext'] = 'MEDIUMTEXT'; // 0 to 16777215
		$this->data_types[$this->mysql]['mediumblob'] = 'MEDIUMBLOB'; // 0 to 16777215
		$this->data_types[$this->mysql]['longtext'] = 'LONGTEXT';  // 0 to 4294967295
		$this->data_types[$this->mysql]['longblob'] = 'LONGBLOB';  // 0 to 4294967295
		// number types
		$this->data_types[$this->mysql]['tinyint'] = 'TINYINT'; // -128 to 127
		$this->data_types[$this->mysql]['smallint'] = 'SMALLINT'; // 0 to 255
		$this->data_types[$this->mysql]['mediumint'] = 'MEDIUMINT'; // -32768 to 32767
		$this->data_types[$this->mysql]['int'] = 'INT'; // -8388608 to 8388607
		$this->data_types[$this->mysql]['bigint'] = 'BIGINT'; // -9223372036854775808 to 9223372036854775807 normal 0 to 18446744073709551615 unsigned
		$this->data_types[$this->mysql]['float'] = 'FLOAT';
		$this->data_types[$this->mysql]['double'] = 'DOUBLE';
		$this->data_types[$this->mysql]['decimal'] = 'DECIMAL';
		// date types
		$this->data_types[$this->mysql]['date'] = 'DATE'; // YYYY-MM-DD
		$this->data_types[$this->mysql]['datetime'] = 'DATETIME'; // YYYYY-MM-DD HH:MM:SS
		$this->data_types[$this->mysql]['timestamp'] = 'TIMESTAMP'; // YYYYMMDDHHMMSS
		$this->data_types[$this->mysql]['time'] = 'TIME'; // HH:MM:SS
		// misc types
		$this->data_types[$this->mysql]['enum'] = 'ENUM'; // e.i. ENUM('x', 'y', ....)
		$this->data_types[$this->mysql]['set'] = 'SET';
		// POSTGRESQL
		$this->data_types[$this->psql] = array();
		// text types
		$this->data_types[$this->psql]['bit'] = 'bit';
		$this->data_types[$this->psql]['varbit'] = 'varbit';
		$this->data_types[$this->psql]['char'] = 'char';
		$this->data_types[$this->psql]['varchar'] = 'varchar';
		$this->data_types[$this->psql]['text'] = 'text';
		$this->data_types[$this->psql]['tsquery'] = 'tsquery'; // text search query
		$this->data_types[$this->psql]['tsvector'] = 'tsvector'; // text search document
		// number types
		$this->data_types[$this->psql]['int'] = 'int';
		//$this->data_types[$this->psql]['bigint'] = 'int8';
		//$this->data_types[$this->psql]['smallint'] = 'int2';
		$this->data_types[$this->psql]['serial'] = 'serial'; // auto incrementing integer
		//$this->data_types[$this->psql]['bigserial'] = 'serial8'; // auto incrementing integer
		$this->data_types[$this->psql]['decimal'] = 'numberic'; 
		$this->data_types[$this->psql]['float'] = 'float4'; 
		//$this->data_types[$this->psql]['double'] = 'float8'; 
		// date types
		$this->data_types[$this->psql]['date'] = 'date';
		$this->data_types[$this->psql]['time'] = 'time';
		$this->data_types[$this->psql]['timestamp'] = 'timestamp';
		// data type format
		$this->type_formats['integer'] = 'int';
		$this->type_formats['int'] = 'int';
		$this->type_formats['character varying'] = 'varchar';
		$this->type_formats['varchar'] = 'varchar';
		$this->type_formats['timestamp'] = 'timestamp';
		$this->type_formats['timestamp without time zone'] = 'timestamp';   
	}
}
?>
