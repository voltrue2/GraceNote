<?php 

class QueryBuilder {
	
	private $read = null;
	private $write = null;
	private $inflateWith = false;
	private $returning = false;
	private $table = false;
	private $type = false;
	private $column = false;
	private $columns = false;
	private $keys = false;
	private $selectData = false;
	private $conds = false;
	private $setData = false;
	private $conditionData = false;
	private $recursive = false;
	private $assocData = false;
	private $assocTables = false;
	private $groupData = false;
	private $orderData = false;
	private $havingCond = false;
	private $limitData = false;
	private $useCache = false;
	private $sortOrder = '';
	private $quote = '"';
	private $mysql = 'mysql';
	private $psql = 'pgsql';
	private $collation = 'utf8';
	private $dataTypes;
	private $dataSize;
	private $typeFormats = false;
	private $escaped = false;
	private $all = false;
	private	$selectLock = '';
	private $inflateThreshhold = 5000; // if the result array exceeds this amount, the inflate method switches from exec_inflate to exec_inflate_each
	private $joinTypes = array('LEFT' => 1, 'RIGHT' => 1, 'INNER' => 1, 'OUTER' => 1, 'LEFT OUTER' => 1, 'LEFT INNER' => 1);
	
	public function QueryBuilder($readConf, $writeConf, $table){
		try {
			$this->read = new SqlRead($readConf);
			$this->write = new SqlWrite($writeConf);
			$r = $this->read->getDbType();
			$w = $this->write->getDbType();
			if ($r !== $w) {
				throw new Exception('Orm::constructor > read db and write db do not match >> ' . $r . ' - ' . $w);
			}
			$this->type = $r;
			$this->table = $table;
			$this->buildDataTypes();
		} catch (Exception $e) {
			Log::error('Orm Failed to construct', $e->getMessage());
		}
	}
	
	/***********************************
		General Methods
	***********************************/	
	public function name(){
		return $this->table;
	}
	
	// recursive select for foriegn keys
	public function recursive($limit = 0){
		$this->recursive = true;
		if ($limit){
			$this->recursiveLimit = $limit;
		}
	}
		
	public function setInflateThreshhold ($n) {
		$this->inflateThreshhold = $n;
	}
	
	public function transaction() {
		$this->write->transaction();
	}

	public function inTransaction() {
		return $this->write->inTransaction();
	}
	
	public function commit() {
		$this->write->commit();
	}
	
	public function rollBack() {
		$this->write->rollBack();
	}
	
	// set up for inflate data retreival
	// $selects: Array e.g. array('id AS my_id', 'name'...)
	// $preconditions : Associative Array  
	// e.g. $preconditions = array('status' => 1, 'count' => 10) translates to status = 1 AND count = 10
	// e.g. $preconditions = array('status' => array('!=', 1), 'count' => array('>', 10)) translates to status != 1 AND count > 10
	public function inflate($table, $inflatingColumn, $srcTable, $srcColumn, $selects = null, $preconditions = false){
		if (is_object($table) && method_exists($table, 'name')){
			$table = $table->name();
		}
		else if (is_object($srcTable) && !method_exists($srcTable, 'name')){
			return false;
		}
		if (is_object($srcTable) && method_exists($srcTable, 'name')){
			$srcTable = $srcTable->name();
		}
		else if (is_object($srcTable) && !method_exists($srcTable, 'name')){
			return false;
		}
		$this->inflateWith[$table][$inflatingColumn] = array($srcTable => $srcColumn, 'selects' => $selects, 'preconds' => $preconditions);
		return $srcTable;
	}

	public function databases($useCache = true){
		$res = $this->read->showDatabases($useCache);
		if ($res) {
			return $res->getMany();
		}
		return array();
	}
	
	public function describe($useCache = true){
		$res = $this->read->describeTable($this->table, $useCache);
		if ($res) {
			$descSrc = $res->getMany();
			$desc = array();
			$t = ($this->type === $this->mysql) ? 'Type' : 'type';
			$f = ($this->type === $this->mysql) ? 'Field' : 'field';
			foreach ($descSrc as $item) {
				$type = preg_replace('/\((.+?)\)/', '', $item[$t]);
				if (isset($this->typeFormats[$item[$t]])) {
					$type = $this->typeFormats[$item[$t]];
				}
				$desc[] = array('field' => $item[$f], 'type' => $type);
			}
			return $desc;
		}
		return null;
	}
	
	public function tables($useCache = true) {
		$res = $this->read->showTables($useCache);
		if ($res) {
			return $res->getMany();
		}
		return array();
	}

	public function getForeignKeys($altTable = false){
		$table = $this->table;
		if ($altTable){
			$table = $altTable;
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
			$tables = array('key_column_usage');
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
			$tables = array(
				'table_constrains',
				'key_column_usage',
				'constrain_column_usage'
			);
		}
		$res = $this->read->run($sql, null, $tables);
		if ($res) {
			return $res->getMany();
		}
		return null;
	}
	
	public function getDataTypes(){
		return $this->dataTypes[$this->type];
	}
	
	public function getDataFormats() {
		return $this->typeFormats;
	}
	
	public function year($t){
		return $this->timeFunc($t, 'YEAR');
	}
	
	public function month($t){
		return $this->timeFunc($t, 'MONTH');
	}
	
	public function day($t){
		return $this->timeFunc($t, 'DAY');
	}
	
	public function hour($t){
		return $this->timeFunc($t, 'HOUR');
	}
	
	public function minute($t){
		return $this->timeFunc($t, 'MINUTE');
	}
	
	public function second($t){
		return $this->timeFunc($t, 'SECOND');
	}
	
	// used with $this->group method e.g. $table_name->group( $table_name->rand() );
	public function rand() {
		if ($this->type == $this->mysql) {
			return ' RAND() ';
		} else if ($this->type == $this->psql){
			return ' RANDOM() ';
		} else {
			return '';
		}
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
		$this->selectData[] = $value;
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
		call_user_func_array(array($this, '_' . $method), $args);
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

	// 'table name'
	public function join($value, $type = null, $cond = null){
		$table = $value;
		$type = strtoupper($type);
		if (!isset($this->joinTypes[$type])){
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
		$this->assocData[] = $value;
		$this->assocTables[] = $table;
		return $table;
	} 
	 
	public function group($value){
		$this->groupData[] = $value;
	} 
	
	// second argument = DESC or ASC
	public function order($value, $order = false){
		$this->orderData[] = $value;
		if ($order){
			$this->sortOrder = $order;
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
			$this->limitData = ' LIMIT '.$f.', '.$t;
		}
		else if ($this->type == $this->psql){
			$this->limitData = ' LIMIT '.$t.' OFFSET '.$f;
		}
	}

	private function evalClause($clause){
		$clause = strtolower(str_replace(' ', '', $clause));
		if ($clause != 'and' && $clause != 'or'){
			// default
			$clause = 'and';
		}
		if (empty($this->conditionData)){
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
	public function get($sql, $params = null, $tables = null, $useCache = true){
		$res = $this->read->run($sql, $params, $tables, $useCache);
		if ($res) {
			return $res->getMany();
		}
		return null;
	}
	
	public function send($sql, $params = null){
		return $this->write->save(array($this->table), $sql, $params);
	}
	
	/***********************************
		Select Method
	***********************************/
	
	public function getOne($index = 0, $useCache = true, $debug = false) {
		return $this->getResults($index, $useCache, $debug);
	}

	public function getMany($useCache = true, $debug = false) {
		return $this->getResults(null, $useCache, $debug);
	}

	public function getData($useCache = true, $debug = false) {
		$res = $this->getResults(null, $useCache, $debug);
		return new SqlData($res);
	}

	private function getResults($index, $useCache, $debug = false){
		// construct query select
		$select = $this->getSelect();
		// construct table association
		$assoc = $this->getAssociation();
		// construct group by
		$group = $this->getGroup();
		// construct order 
		$order = $this->getOrder();
		// limit 
		$limit = $this->limitData;
		if ($this->conds){
			$conds = '';
			$params = null;
			if (isset($this->conds['query'])){
				$conds = $this->conds['query'];
			}
			if (isset($this->conds['parameters'])){
				$params = $this->conds['parameters'];
			}
		} else if (!isset($conds) && !isset($params)){
			$conds = '';
			$params = false;
		}
		$having = '';
		if ($this->havingCond){
			$having = $this->havingCond;
		}	
		$sql = "SELECT ".$select." FROM ".$this->table.$assoc.$conds.$group.$order.$having.$limit.$this->selectLock;
		if ($debug) {
			// do not execute the query
			Log::debug('[QUERYBUILDER] getResults > debug >> ', $sql, $params);
			$this->flush();
			return true;
		}
		// exectue select query
		$tables = $this->assocData;
		$tables[] = $this->table;
		$res = $this->read->run($sql, $params, $tables, $useCache);
		if (!$res) {
			// found nothing
			$this->flush();
			return null;
		}
		if ($index === null) {
			$res = $res->getMany();
		} else {
			$res = $res->getOne($index);
		}
		if (empty($res)) {
			$this->flush();
			return $res;
		}
		// check for foreign key recursive selects
		if ($this->recursive && $res){
			if ($this->assocData){
				$tables = array_merge(array($this->table), $this->assocData);
			}
			else {
				$tables = $this->table;
			}
			$fk = $this->getForeignKeys($tables);
			if ($fk){
				// we have some foreign keys
				$this->recursiveGet($fk, $res);
			}
		}
		// check for inflate retreival
		if ($this->inflateWith){
			$res = $this->inflateGet($res, $this->inflateWith, $useCache);
		}
		$this->flush();
		if ($index !== null) {
			$res = (isset($res[$index])) ? $res[$index] : $res;
		}
		return $res;
	}

	private function recursiveGet($fk, &$res, $loop = 0){
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
							$ret = $this->get($sql, null, array($ftable));
							$next_fk = $this->getForeignKeys($ftable);
							// get more 
							if ($next_fk && $ftable){
								$ret = array(0 => $ret);
								$loop++;
								$this->recursiveGet($next_fk, $ret, $loop);
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
	private function inflateGet($list, $with, $useCache){
		if ($this->assocData){
			$tables = array_merge(array($this->table), $this->assocTables);
		}
		else {
			$tables = array($this->table);
		}
		$resAmount = count($list);
		foreach ($tables as $table){
			if (isset($with[$table])){
				if ($resAmount > $this->inflateThreshhold) {
					// TODO: for now execInflateEach may have a bug 2012.06.26
					//$list = $this->execInflateEach($list, $with, $table);
					$list = $this->execInflate($list, $with, $table, $useCache);				
				}
				else {
					$list = $this->execInflate($list, $with, $table, $useCache);
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
	/*
	private function execInflateEach ($list, $with, $target_table) {
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
						$res = $this->get($sql, $params, array($table));	
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
	*/
	/*****************************************************************
	Note: This method is faster with small amount of data but CAN NOT 
	      handle large amount of data > for large amount of data 
	      use exec_inflate_each method instead
	Caution: This method is very memory intense therefore should never 
		 be used to handle large amount of data
	*****************************************************************/	
	private function execInflate($list, $with, $targetTable, $useCache){
		$single = false;
		if (!isset($list[0])){
			$list = array($list);
			$single = true;
		}
		$resource = false;
		foreach ($list as $item){
			foreach ($with[$targetTable] as $column => $att){
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
				$selects = '*';
				if ($item['attributes']['selects']) {
					$selects = implode(', ', $item['attributes']['selects']);
				}
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
				unset($item['attributes']['selects']);
				unset($item['attributes']['preconds']);
				foreach ($item['attributes'] as $tableName => $targetColumn){
					$sql = "SELECT " . $selects . " FROM ".$tableName." WHERE ".$targetColumn." IN ".$vals.$preconds;
					$res = $this->get($sql, $item['value'], array($tableName), $useCache);
					if ($res){
						$next = $tableName;
						if (isset($with[$next])){
							$res = $this->execInflate($res, $with, $next, $useCache);
						}
						foreach ($res as $i => $item){
							if (!is_array($item[$targetColumn])){
								$id = (string)$item[$targetColumn];
								$resource[$column]['results'][$id][] = $item;
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
							$resData = $data['results'][$item[$column]];
							$list[$i][$column] = $resData;
						}
					}
				}
			}
		}
		return $list;
	}

	/***********************************
	     Insert/Update/Delete Methods
	***********************************/
	public function set($column, $value){
		$this->setData[] = array('column' => $column, 'value' => $value);
	}

	// mysql(innoDB) select locks > requires to b ein transaction
	public function lockInSharedMode() {
		if ($this->write->inTransaction()) {
			$this->selectLock = ' LOCK IN SHARED MODE';
			return true;
		}
		Log::warn('[QUERYBUILDER] "lockInSharedMode" expects to be used in a transaction');
		return false;
	}
	
	// mysql(innoDB) select locks > requires to b ein transaction
	public function forUpdate() {
		if ($this->write->inTransaction()) {
			$this->selectLock = ' FOR UPDATE';
			return true;
		}
		Log::warn('[QUERYBUILDER] "forUpdate" expects to be used in a transaction');
		return false;
	}

	public function save($returningColumn = null, $debug = false){
		if ($this->conds){
			$conds = $this->conds;
		}
		else {
			$conds = array('query' => false);
		}
		$assoc = $this->getAssociation();
		if ($conds['query']){
			// update
			$cond_query = $conds['query'];
			$params = $conds['parameters'];
			$set = $this->getUpdateSet();
			$sql = "UPDATE ".$assoc.$this->table." SET ".$set['statement'].$condQuery;
			if ($set['parameters']){
				$params = array_merge($set['parameters'], $params);
			}
		}
		else {
			// insert
			$set = $this->getInsertSet();
			$params = $set['parameters'];
			$sql = "INSERT INTO ".$this->table." ".$set['statement'];
			if ($this->type == $this->psql){
				$this->returning = $returningColumn;
			}
			if ($this->returning){
				$sql .= ' RETURNING '.$this->returning;
			}
		}
		if ($debug) {
			// no not execute
			Log::debug('[QUERYBUILDER] save > debug >> ', $sql, $params);
			$this->flush();
			return true;
		}
		return $this->execSend($sql, $params);
	}
	
	public function update($debug = false) {
		if ($this->conds){
			$conds = $this->conds;
		}
		$assoc = $this->getAssociation();
		if ($conds['query']){
			// update
			$condQuery = $conds['query'];
			$params = $conds['parameters'];
			$set = $this->getUpdateSet();
			$sql = "UPDATE ".$assoc.$this->table." SET ".$set['statement'].$condQuery;
			if ($set['parameters']){
				$params = array_merge($set['parameters'], $params);
			}
		}
		else {
			// update
			$cond_query = '';
			$set = $this->getUpdateSet();
			$sql = "UPDATE ".$assoc.$this->table." SET ".$set['statement'].$condQuery;
			$params = $set['parameters'];
		}
		if ($debug) {
			// do not execute
			Log::debug('[QUERYBUILDER] save > debug >> ', $sql, $params);
			$this->flush();
			return true;
		}
		return $this->execSend($sql, $params);
	}
	
	public function delete($debug = false){
		if ($this->conds){
			$res = $this->conds;
		}
		if ($res['query']){
			$sql = "DELETE FROM ".$this->table.$res['query'];
			if ($debug) {
				// do not execute
				Log::debug('[QUERYBUILDER] delete > debug >> ', $sql, $res['parameters']);
				$this->flush();
				return true;
			}
			$res = $this->execSend($sql, $res['parameters']);
			return $res;
		}
		else {
			return false;
		}
	}
	
	private function getUpdateSet(){
		if ($this->setData){
			$str = '';
			$params = false;
			$total = count($this->setData) - 1;
			foreach ($this->setData as $i => $item){
				$str .= $item['column'].' = ?'.$this->tail($i, $total);
				$params[] = $item['value'];
			}
			return array('statement' => $str, 'parameters' => $params);
		}
	}

	private function getInsertSet(){
		if ($this->setData){
			$cols = ' (';
			$vals = ' VALUES(';
			$params = false;
			$total = count($this->setData) - 1;
			foreach ($this->setData as $i => $item){
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
	public function setAutoIncrementColumn($name, $size = null, $primary = null){
		if ($this->type == $this->mysql){
			// mysql
			$c = $name." INT";
			if ($size){
				$c .= "(".$size.") ";
			} else {
				$c .= '(255) ';
			}
			$c .= " NOT NULL AUTO_INCREMENT ";
			$c .= " PRIMARY KEY";
		} else if ($this->type == $this->psql){
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
	
	public function setColumn($name, $type = false, $size = false, $default = false, $primary = false){
		if (!isset($this->dataTypes[$this->type][strtolower($type)])){
			$type = "";
		} else {
			$type = $this->dataTypes[$this->type][strtolower($type)];
			if ($size !== false){
				$type .= "(".$size.") ";
			} else if (isset($this->dataSize[$this->mysql][strtolower($type)])) {
				$type .= '(' . $this->dataSize[$this->mysql][strtolower($type)] . ') ';
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
	
	public function setKey($column, $keyType = ''){
		if ($this->type == $this->mysql && $key_type){
			// PRIMARY KEY, UNIQUE, INDEX
			$this->keys[] = $keyType.'('.$column.')';
		} else if ($this->type == $this->psql){
			// UNIQUE
			$this->keys[] = "CREATE ".$keyType." ".$column."_index ON ".$this->table." (".$column.")";
		}
	}
	
	/* example
	$test = new QueryBuilder('sql object', 'db', 'test');
	$test->auto_increment_column('id', false, 'primary key');
	$test->set_column('title', 'varchar', 100, '');
	$test->set_column('body', 'text');
	$test->create();
	-> resultes in postgresql
	 CREATE TABLE test (id SERIAL PRIMARY KEY, title varchar(100) NOT NULL , body text NOT NULL , modtime timestamp DEFAULT current_timestamp);
	*/
	public function create($mysqlEngine = false){
		if (!$mysqlEngine && $this->type === $this->mysql){
			$mysqlEngine = 'InnoDB'; // default
		}
		if ($mysqlEngine){
			// MyISAM or InnoDB
			$mysqlEngine = "ENGINE=".$mysqlEngine;
		} else {
			$mysqlEngine = "";
		}
		if ($this->type == $this->mysql){
			// mysql charset
			$mysqlCollation = " CHARSET=".$this->collation;
			// mysql table check
			$mysqlTableCheck = "IF NOT EXISTS";
		} else {
			$mysqlCollation = "";
			$mysqlTableCheck = "";
		}
		// build columns
		if ($this->columns){
			$c = '';
			$total = count($this->columns) - 1;
			foreach ($this->columns as $i => $item){
				$c .= $item.$this->tail($total, $i);
			}
		}
		// set keys mysql
		if ($this->keys && $this->type == $this->mysql){
			$c .= ', ';
			$total = count($this->keys) - 1;
			foreach ($this->keys as $i => $key){
				$c .= $key.$this->tail($total, $i);
			}
		}
		$sql = "CREATE TABLE ".$mysqlTableCheck." ".$this->table." (".$c.") ".$mysqlEngine.$mysqlCollation;
		$res = $this->execSend($sql);
		// set keys postgresql
		if ($this->keys && $this->type == $this->psql){
			foreach ($this->keys as $query){
				$this->execSend($query);
			}
		}
		return $res;
	}
	
	// add columns
	public function addColumns(){
		$c = 0;
		// build columns
		if ($this->columns){
			$total = count($this->columns);
			foreach ($this->columns as $i => $item){
				$query = "ALTER TABLE ".$this->table." ADD COLUMN ".$item;
				$res = $this->execSend($query);
				if ($res){
					$c++;
				}
			}
		}
		// set keys postgresql
		if ($this->keys && $this->type == $this->psql){
			foreach ($this->keys as $query){
				$this->execSend($query);
			}
		}
		return $c;
	}
	
	// drop columns
	public function removeColumns(){
		// build columns
		if ($this->columns){
			$total = count($this->columns);
			$c = 0;
			foreach ($this->columns as $i => $item){
				$query = "ALTER TABLE ".$this->table." DROP COLUMN ".substr($item, 0, strpos($item, ' '))." CASCADE";
				$res = $this->execSend($query);
				if ($res){
					$c++;
				}
			}
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
		return $this->execSend($query);
	}
	
	// rename column
	public function renameColumn($columnFrom, $columnTo){
		if ($this->type == $this->mysql){
			// MySQL untested as of 2011/04/29
			$fields = $this->describe();
			$column_attributes = '';
			if ($fields){
				foreach ($fields as $i => $item){
					if ($item['Field'] == $column_from){
						$columnAttributes = $item['Type'];
					}
				}
			}
			$query = "ALTER TABLE ".$this->table." CHANGE ".$columnFrom." ".$columnTo." ".$columnAttributes;
		}
		else if ($this->type == $this->psql){
			$query = "ALTER TABLE ".$this->table." RENAME COLUMN ".$columnFrom." TO ".$columnTo;
		}
		return $this->execSend($query);
	}
	
	// change data type of a column -> tested postgresql only as of 2011/04/29
	public function changeDataType($column, $dataType){
		$query = "ALTER TABLE ".$this->table." ALTER COLUMN ".$column." TYPE ".$dataType;
		return $this->execSend($query);
	}
	
	// drop table 
	public function drop(){
		$q = "DROP TABLE ".$this->table;
		return $this->execSend($q);
	}
	
	/***********************************
		General Private Methods
	***********************************/
	private function timeFunc($t, $type){
		if ($this->type == $this->mysql){
			return $type."(".$t.")";	
		}
		else if ($this->type == $this->psql){
			return "EXTRACT(".$type." FROM ".$t.")";
		}
	}
	
	private function getSelect(){
		if ($this->selectData){
			$str = '';
			$total = count($this->selectData) - 1;
			foreach ($this->selectData as $i => $value){
				$str .= $value.$this->tail($i, $total);
			}
			return $str;
		}
		else {
			return '*';
		}
	}

	private function getAssociation(){
		if ($this->assocData){
			$str = "";
			$total = count($this->assocData) - 1;
			foreach ($this->assocData as $i => $item){
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
	
	private function getGroup(){
		if ($this->groupData){
			$str = " GROUP BY ";
			$total = count($this->groupData) - 1;
			foreach ($this->groupData as $i => $item){
				$str .= $item.$this->tail($i, $total);
			}
			return $str;
		}
		else {
			return '';
		}
	}
	
	private function getOrder(){
		if ($this->orderData){
			$str = " ORDER BY ";
			$total = count($this->orderData) - 1;
			foreach ($this->orderData as $i => $item){
				$str .= $item.$this->tail($i, $total);
			}
			return $str.' '.$this->sortOrder.' ';
		}
		else {
			return '';
		}
	}
	
	private function tail($i, $total){
		if ($i == $total){
			$tail = ' ';
		} else {
			$tail = ', ';
		}
		return $tail;
	}
	
	private function push($res){
		if ($res){
			if (isset($res[1])){
				return $res;
			} else {
				return $res[0];
			}
		} else {
			return false;
		}	
	}

	private function execSend($sql, $params = null){
                $res = $this->write->save(array($this->table), $sql, $params);
                if ($res && isset($res['statement'])) { 
			$lastId = false;
	                if ($this->type == $this->mysql){
	                        $lastId = $res['connection']->lastInsertId();
	                } else if ($this->type == $this->psql){
	                        $r = $res['statement']->fetch(PDO::FETCH_ASSOC);
	                        if ($r){
	                                if (isset($r[$this->returning])){
	                                        $lastId = $r[$this->returning];
	                                }
	                        }
	                }
                }
                $this->flush();
                if ($res['rows']){
                        return array('rows' => $res['rows'], 'lastId' => $lastId);
                } else {
                        return false;
                }
        }

	
	private function flush(){
		$this->setData = false;
		$this->column = false;
		$this->columns = false;
		$this->keys = false;
		$this->selectData = false;
		$this->conditionData = false;
		$this->conds = false;
		$this->assocData = false;
		$this->assocTables = false;
		$this->groupData = false;
		$this->orderData = false;
		$this->havingCond = false;
		$this->limitData = false;
		$this->sortOrder = '';
		$this->recursive = false;
		$this->returning = false;
		$this->inflateWith = false;
		$this->escaped = false;
		$this->all = false;
		$this->selectLock = '';
		$this->inflateThreshhold = false;
	}
	
	private function buildDataTypes(){
		$this->dataTypes = array();
		// MYSQL
		$this->dataTypes[$this->mysql] = array();
		// text types
		$this->dataTypes[$this->mysql]['char'] = 'CHAR'; // 0 to 255
		$this->dataTypes[$this->mysql]['varchar'] = 'VARCHAR'; // 0 to 255
		//$this->dataTypes[$this->mysql]['tinytext'] = 'TINYTEXT'; // 0 to 255
		$this->dataTypes[$this->mysql]['text'] = 'TEXT'; // 0 to 65535
		$this->dataTypes[$this->mysql]['mediumtext'] = 'TEXT'; // 0 to 16777215
		$this->dataTypes[$this->mysql]['blob'] = 'BLOB'; // 0 to 65535
		//$this->dataTypes[$this->mysql]['mediumtext'] = 'MEDIUMTEXT'; // 0 to 16777215
		//$this->dataTypes[$this->mysql]['mediumblob'] = 'MEDIUMBLOB'; // 0 to 16777215
		//$this->dataTypes[$this->mysql]['longtext'] = 'LONGTEXT';  // 0 to 4294967295
		//$this->dataTypes[$this->mysql]['longblob'] = 'LONGBLOB';  // 0 to 4294967295
		// number types
		//$this->dataTypes[$this->mysql]['tinyint'] = 'TINYINT'; // -128 to 127
		//$this->dataTypes[$this->mysql]['smallint'] = 'SMALLINT'; // 0 to 255
		//$this->dataTypes[$this->mysql]['mediumint'] = 'MEDIUMINT'; // -32768 to 32767
		$this->dataTypes[$this->mysql]['int'] = 'INT'; // -8388608 to 8388607
		//$this->dataTypes[$this->mysql]['bigint'] = 'BIGINT'; // -9223372036854775808 to 9223372036854775807 normal 0 to 18446744073709551615 unsigned
		$this->dataTypes[$this->mysql]['float'] = 'FLOAT';
		//$this->dataTypes[$this->mysql]['double'] = 'DOUBLE';
		//$this->dataTypes[$this->mysql]['decimal'] = 'DECIMAL';
		// date types
		$this->dataTypes[$this->mysql]['date'] = 'DATE'; // YYYY-MM-DD
		$this->dataTypes[$this->mysql]['datetime'] = 'DATETIME'; // YYYYY-MM-DD HH:MM:SS
		$this->dataTypes[$this->mysql]['timestamp'] = 'TIMESTAMP'; // YYYYMMDDHHMMSS
		$this->dataTypes[$this->mysql]['time'] = 'TIME'; // HH:MM:SS
		// misc types
		$this->dataTypes[$this->mysql]['enum'] = 'ENUM'; // e.i. ENUM('x', 'y', ....)
		$this->dataTypes[$this->mysql]['set'] = 'SET';
		// MYSQL data size
		$this->dataSize[$this->mysql]['char'] = 255;
		$this->dataSize[$this->mysql]['varchar'] = 255;
		$this->dataSize[$this->mysql]['text'] = 65535;
		$this->dataSize[$this->mysql]['int'] = 255;

		// POSTGRESQL
		$this->dataTypes[$this->psql] = array();
		// text types
		//$this->dataTypes[$this->psql]['bit'] = 'bit';
		//$this->dataTypes[$this->psql]['varbit'] = 'varbit';
		//$this->dataTypes[$this->psql]['char'] = 'char';
		$this->dataTypes[$this->psql]['varchar'] = 'varchar';
		$this->dataTypes[$this->psql]['text'] = 'text';
		//$this->dataTypes[$this->psql]['tsquery'] = 'tsquery'; // text search query
		//$this->dataTypes[$this->psql]['tsvector'] = 'tsvector'; // text search document
		// number types
		$this->dataTypes[$this->psql]['int'] = 'int';
		//$this->dataTypes[$this->psql]['bigint'] = 'int8';
		//$this->dataTypes[$this->psql]['smallint'] = 'int2';
		$this->dataTypes[$this->psql]['serial'] = 'serial'; // auto incrementing integer
		//$this->dataTypes[$this->psql]['bigserial'] = 'serial8'; // auto incrementing integer
		//$this->dataTypes[$this->psql]['decimal'] = 'numberic'; 
		$this->dataTypes[$this->psql]['float'] = 'float4'; 
		//$this->dataTypes[$this->psql]['double'] = 'float8'; 
		// date types
		$this->dataTypes[$this->psql]['date'] = 'date';
		$this->dataTypes[$this->psql]['time'] = 'time';
		$this->dataTypes[$this->psql]['timestamp'] = 'timestamp';
		// data type format
		$this->typeFormats['integer'] = 'int';
		$this->typeFormats['int'] = 'int';
		$this->typeFormats['character varying'] = 'varchar';
		$this->typeFormats['varchar'] = 'varchar';
		$this->typeFormats['text'] = 'text';
		$this->typeFormats['date'] = 'date';
		$this->typeFormats['time'] = 'time';
		$this->typeFormats['float4'] = 'float';
		$this->typeFormats['serial'] = 'serial';
		$this->typeFormats['float'] = 'float';
		$this->typeFormats['timestamp'] = 'timestamp';
		$this->typeFormats['datetime'] = 'datetime';
		$this->typeFormats['timestamp without time zone'] = 'timestamp';   
	}
}
