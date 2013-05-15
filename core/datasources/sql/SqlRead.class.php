<?php

class SqlRead {
	
	public $noCahce = false;
	private $conf = null;
	private $db = null; // database name
	private $cn = null; // database connection
	private $cache = null; // memcache
	private $dbType = null; // type of database: mysql or pgsql
	private $pgsql = 'pgsql';
	private $mysql = 'mysql';
	private $behavior = 'read';
	private $notAllowed = '/(insert|update|delete|create|alter|drop)/i';

	public function SqlRead($conf) {
		// set up
		try {
			// create cache
			$this->cache = new Cache();
			// check config
			if (!isset($conf)) {
				throw new Exception('Invalid db name given > ' . json_encode($conf) );
			}
			// check behavior
			if (isset($conf['behavior']) && $conf['behavior'] !== $this->behavior) {
				throw new Exception('SqlRead class expects behavior to be "' . $this->behavior . '" only, but "' . $conf['behavior'] . '" given');
			}
			// set configuration
			$this->conf = $conf;
			$this->dbType = $conf['type'];
			$this->db = $conf['db'];
			return true;
		} catch(Exception $e) {
			$this->error('constructor', $e, $conf);
			return false;
		}
	}
	
	public function getDbType() {
		return $this->dbType;
	}
	
	/***
	* executes a select query
	* $tables is used for cache control
	*/ 
	public function run($sql, $params = null, $tables = null, $useCache = true) {
		try {
			$start = microtime(true);
			if (!$this->isAllowed($sql)) {
				throw new Exception('Query not allowed to execute >> ' . $sql);
				return null;
			}
			if (!$params) {
				$params = array();
			}
			$result = null;
			if ($useCache) {
				// check cache first
				$result = $this->getCache($sql, $params, $tables);
			}
			if ($result && $result['data']) {
				$res = new SqlData($result['data']);
				$end = microtime(true);
				$time = (string)substr((($end - $start) * 1000), 0, 8);
				Log::verbose('[SQLREAD] run > The "cached" query ' . $sql . ' took [' . $time . ' msec]');
				return $res;
			}
			// no cache
			$res = $this->_exec($sql, $params, $result['key'], $tables, $useCache);
			// return the qurey result
			$res = new SqlData($res);
			$end = microtime(true);
			$time = (string)substr((($end - $start) * 1000), 0, 8);
			Log::verbose('[SQLREAD] run > The query ' . $sql . ' took [' . $time . ' msec]', $params);
			return $res;
		} catch (Exception $e) {
			$this->error('run', $e);
			return null;
		}
	}
	
	/***
	* takes an array of parameters for a query and returns escaped characters as a string
	*/ 
	public function placeHolder($params) {
		$res = '';
		foreach ($params as $i => $item) {
			$commna = ',';
			if ($i === 0) {
				$commna = '';
			}
			$res .= $commna . '?';
		}
		return $res;
	}
	
	/***
	* cross db show database
	*/
	public function showDatabases($useCache = true) {
		$sql = $this->crossDbQuery("SHOW DATABASES", "SELECT datname FROM pg_database");
		if ($sql) {
			return $this->run($sql, null, array('pg_database'), $useCache);
		}
		return null;
	}
	
	/***
	* cross db describe table
	*/
	public function describeTable($table, $useCache = true) {
		$mquery = "DESCRIBE " . $table;
		$pquery = "SELECT column_name AS field, data_type AS Type, column_default AS Default FROM information_schema.columns WHERE table_name = '" . $table . "'";
		$sql = $this->crossDbQuery($mquery, $pquery);
		if ($sql) {
			return $this->run($sql, null, array('columns', $table), $useCache);
		} 
		return null;
	}

	public function showTables($useCache = true) {
		$sql = $this->crossDbQuery("SELECT table_name AS tablename FROM INFORMATION_SCHEMA.TABLES WHERE table_schema = '" . $this->db . "'", "SELECT tablename FROM pg_catalog.pg_tables WHERE tablename NOT LIKE 'pg_%' AND tablename NOT LIKE 'sql_%'");
		if ($sql) {
			return $this->run($sql, null, array('tables', 'pg_tables'), $useCache);
		}
		return null;
	}

	private function connect() {
		if ($this->cn) {
			return;
		}
		// create db connection
		$this->cn = SqlConnection::getConnection($this->conf);
	}

	// filter out any write qeury
	private function isAllowed($sql) {
		list($first) = mb_split(' ', $sql);
		$res = preg_match($this->notAllowed, $first, $matches);
		if ($res) {
			return false;
		}
		return true;
	}
	
	// cross db query
	private function crossDbQuery($mysqlQuery, $pgsqlQuery) {
		if ($this->dbType === $this->mysql) {
			return $mysqlQuery;
		} else if ($this->dbType === $this->pgsql) {
			return $pgsqlQuery;
		} else {
			$this->warn('showDatabase', 'Invalid database >> ' . $this->dbType . ' >>> ' . $mysqlQuery . ' / ' . $pgsqlQuery);
			return null;
		}
	}
	
	private function _exec($sql, $params, $key = null, $tables, $useCache) {
		$this->debug('_exec', $sql, $params);
		$this->connect();
		$st = $this->cn->prepare($sql);
		$st->execute($params);
		if ($st->errorCode() !== '00000') {
			$info = $st->errorInfo();
			throw new Exception($st->queryString . ': [' . $st->errorCode() . '] ' . $info[2]);
		}
		// fetch
		$res = $st->fetchAll(PDO::FETCH_ASSOC);
		if ($useCache) {
			// set cache
			if (!$key) {
				$key = $this->createCacheKey($sql, $params);
			}
			$this->setCache($key, $res, $tables);
		} else {
			Log::verbose('[SQLREAD] _exec: no cache being used for ' . $sql, $params);
		}
		// return values
		return $res;
	}
	
	private function getCache($sql, $params, $tables) {
		// check each table's cache timestamp
		$invalidateCache = false;
		$prefix = ''; 
		for ($i = 0, $len = count($tables); $i < $len; $i++) {
			$tableUpdateKey = $this->tableKey($tables[$i]);
			$valid = $this->cache->get($tableUpdateKey);
			if (!$valid) {
				$invalidateCache = true;		
				break;
			}
			$prefix .= $valid;	
		}
		$key = $prefix . $this->createCacheKey($sql, $params);
		if ($invalidateCache) {
			Log::verbose('[SQLREAD] getCache > missing table update key for cache >> ' . $tableUpdateKey);
			// create invalid table key
			$prefix = mt_rand(1, 999) . microtime();
			$this->cache->set($tableUpdateKey, $prefix);
			Log::verbose('[SQLREAD] create table update key > ' . $tableUpdateKey . ' >> ' . $prefix); 
			return array('data' => null, 'key' => $key);
		}
		return array('data' => $this->cache->get($key), 'key' => $key);
	}
	
	private function setCache($key, $value, $tables) {
		$this->cache->set($key, $value);
	}

	private function tableKey($table) {
		if (strpos($table, '.') !== false) {
			$sep = explode('.', $table);
			$table = $sep[count($sep) - 1];
		}
		$conf = $this->conf;
		return $conf['type'] . ':' . $conf['host'] . ':' . $conf['db'] . ':sql:' .  strtolower($table);
	}
	
	private function createCacheKey($sql, $params) {
		$conf = $this->conf;
		return $conf['type'] . ':' . $conf['host'] . ':' . $conf['db'] . ':' . $sql . ':' . implode('', $params);
	}
	
	private function debug($method, $sql, $params) {
		Log::verbose('[SQLREAD] ' . $method . ': Executing >> ' . $sql, $params);
	}
	
	private function log($method, $msg) {
		Log::info('[SQLREAD] ' . $method . ': ' . $msg);
	}
	
	private function warn($method, $msg) {
		Log::warn('[SQLREAD] ' . $method . ': WARNING > ' . $msg);
	}
	
	private function error($method, $error) {
		Log::error('[SQLREAD] ' . $method . ': *** Error > ' . $error->getMessage());
	}
}

?>
