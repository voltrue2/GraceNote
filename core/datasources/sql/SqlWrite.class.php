<?php

class SqlWrite {
	
	private $cn = null;
	private $dbType = '';
	private $db = null;
	private $cache = null;
	private $behavior = 'write';

	public function SqlWrite($conf) {
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
				throw new Exception('SqlWrite class expects behavior to be "' . $this->behavior . '" but "' . $conf['behavior'] . '" given');
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
	
	public function transaction(){
		$this->connect();
		if ($this->cn->inTransaction()) {
			return Log::error('[SQLWRITE] transaction: there is an active transaction');
		}
		Log::info('[SQLWRITE] transaction: Starting transaction');
		try {
			$this->cn->beginTransaction();
		} catch (Exception $e) {
			Log::error('[SQLWRITE] transaction failed > ' . $e->getMessage());
		}
	}

	public function inTransaction() {
		$this->connect();
		return ($this->cn->inTransaction()) ? true : false;
	}
	
	public function commit(){
		$this->connect();
		if (!$this->cn->inTransaction()) {
			return Log::error('[SQLWRITE] commit: no active transaction');
		}
		try {
			Log::info('[SQLWRITE] commit: Commited a write');
			$this->cn->commit();
		} catch (Exception $e) {
			Log::error('[SQLWRITE] commit failed > ' . $e->getMessage());
		}
	}
	
	public function rollBack(){
		$this->connect();
		if (!$this->cn->inTransaction()) {
			return Log::error('[SQLWRITE] commit: no active transaction');
		}
		try {
			Log::info('[SQLWRITE] rollBack: Rolled back a write');
			$this->cn->rollBack();
		} catch (Exception $e) {
			Log::error('[SQLWRITE] rollBack failed > ' . $e->getMessage());
		}
	}
	
	/**
	* @tableNames (Array) a list of table names to be updated/instered for cache deletion
	*/
	public function save($tableNames, $sql, $params) {
		try {
			$this->connect();
			$st = $this->cn->prepare($sql);
			$st->execute($params);
			if ($st->errorCode() !== '00000') {
				$info = $st->errorInfo();
				throw new Exception($st->queryString . ': [' . $st->errorCode() . '] ' . $info[2]);
			}
			// clear cache
			$this->updateCache($tableNames);
			// done
			$res = $st->rowCount();
			$this->log('save', $sql, $params, '(affected rows: ' . $res . ')');
			return array('rows' => $res, 'statement' => $st, 'connection' => $this->cn);
		} catch (Exception $e) {
			$this->error('save', $e);
			Log::error('[SQLWRITE] save > Failed', $sql, $params);
			return null;	
		}
	}

	private function connect() {
		if ($this->cn) {
			return;
		}
		// create db connection
		$this->cn = SqlConnection::getConnection($this->conf);
	}
	
	/**
	* delete all cache with the given table names in the keys 
	*/
	private function updateCache($tableNames) {
		$prefix = mt_rand(1, 999) . microtime();
		for ($i = 0, $len = count($tableNames); $i < $len; $i++) {
			$key = $this->tableKey($tableNames[$i]);
			$this->cache->delete($key);
			$this->cache->set($key, $prefix);
		}
	}
	
	private function tableKey($table) {
		if (strpos($table, '.') !== false) {
			$sep = explode('.', $table);
			$table = $sep[count($sep) - 1];
		}
		$conf = $this->conf;
		return $conf['type'] . ':' . $conf['host'] . ':' . $conf['db'] . ':sql:' . strtolower($table);
	}

	private function debug($method, $sql, $params) {
		Log::verbose('[SQLWRITE] ' . $method . ': Executing >> ' . $sql, $params);
	}
	
	private function log($method, $msg, $params = null, $extra = null) {
		Log::info('[SQLWRITE] ' . $method . ': ' . $msg, $params, $extra);
	}
	
	private function error($method, $error, $params = null) {
		Log::error('[SQLWRITE] ' . $method . ': *** Error > ' . $error->getMessage(), $params);
	}

}

?>
