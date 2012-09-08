<?php
Load::core('Base');

class SQL extends Base {
	
	private $cn = null;
	private $a = "?";
	private $db = '';
	private $db_type = false;
	private $debug = false;
	private $debug_cache = false;
	private $echo = false;
	private $mcache = false;
	private $cache = false;
	private $cache_key_limit = 250;
	private $expire = MCACHE_EXP; // default cache expiration time -> 1 hour : the value in seconds 60 * 60
	
	
	public function SQL($db){
		$this->connect($db);
		// memcache
		if (class_exists('Memcache')){
			$this->mcache = new Memcache();
			$this->mcache->pconnect(MCACHE_HOST, MCACHE_PORT);
		}
	}

	public function cache_key_read($key){
		//$res = gzuncompress($key);
		/*
		$res = mb_eregi_replace('S', 'SELECT', $key);
		$res = mb_eregi_replace('F', 'FROM', $res);
		$res = mb_eregi_replace('W', 'WHERE', $res);
		$res = mb_eregi_replace('A', 'AND', $res);
		$res = mb_eregi_replace('G', 'GROUP', $res);
		$res = mb_eregi_replace('O', 'ORDER', $res);
		$res = mb_eregi_replace('L', 'LIMIT', $res);
		$res = mb_eregi_replace('OS', 'OFFSET', $res);
		return $res;
		*/
		return $key;
	}

	public function cache_key_gen($sql, $params){
		if ($params){
			if (is_array($params)){
				$src = $sql . implode('', $params);
			}
			else {
				$src = $sql . $params;
			}
		}
		else {
			$src = $sql;
		}
		return $this->db.':'.str_replace(' ', '', $src);
	}

	public function set_cache($key, $value){
		if ($this->mcache){
			if (mb_strlen($key) <= $this->cache_key_limit){
				if (DISPLAY_ERRORS){
					Message::register('<span style="color: #0000CC;">Cached Data For </span><strong style="color: #0000FF;">'.$this->cache_key_read($key).'('.strlen($key).')'.'</strong>');
				}
				return $this->mcache->set($key, $value, false, $this->expire);
			}
			else {
				Message::register('<span style="color: #FF0000;">Cache key length exceeded The limit of '.$this->cache_key_limit.'</span><strong style="color: #0000FF;">'.$this->cache_key_read($key).'('.strlen($key).')'.'</strong>');
				return false;
			}
		}
		else {
			return false;
		}
	}

	public function get_cache($key){
		if ($this->mcache){
			if (DISPLAY_ERRORS){
				$timer = new Timer(1);
			}
			$res = $this->mcache->get($key);
			if (DISPLAY_ERRORS && $res){
				Message::register('<span style="color: #009900;">Cached Data Retrieved For </span><strong style="color: #009900;">'.$this->cache_key_read($key).'('.strlen($key).')'.'</strong><span style="color: #FF0000;"> [ '.round($timer->get() * 1000, 4).' ms ]</span>');
			}
			return $res;
		}
		else {
			return false;
		}
	}
	
	public function cache($expire = 0){
		$this->cache = true;
		if ($expire){
			$this->expire = $expire;
		}
	}

	public function flush(){
		if ($this->mcache){
			$this->mcache->flush();
		}
	}
	
	public function cache_info(){
		if ($this->mcache){
			$list = array();
			$allslabs = $this->mcache->getExtendedStats('slabs');
			$items = $this->mcache->getExtendedStats('items');
			foreach($allslabs as $server => $slabs) {
				foreach($slabs as $slabid => $slabmeta) {
					$id = (int)$slabid;
					if ($id){
						$cdump = $this->mcache->getExtendedStats('cachedump', $id);
						foreach($cdump as $server => $entries) {
						    if($entries) {
						            foreach($entries as $ename => $edata) {
						                $list[$ename] = array(
						                     'key' => $ename,
						                     'server' => $server,
						                     'slabId' => $slabid,
						                     'detail' => $edata,
						                     'age' => $items[$server]['items'][$slabid]['age'],
						                     );
						            }
						    }
						}
					}
				}
			}
			if ($this->debug){
				if ($this->echo){
					echo('<pre>');
					var_dump($list);
					echo('</pre>');
				}
				else {
					error_log(print_r($list, true));
				}
			}
			if (empty($list)){
				$list = false;
			}
			return $list;
		}
		else {
			return false;
		}
	}
	
	public function delete_cache($key){
		if ($this->mcache){
			$res = $this->mcache->delete($key);
			if (!$res){
				// delete pattern
				$list = $this->cache_info();
				if ($list){
					foreach ($list as $cache_key => $values){
						$src = $this->cache_key_read($cache_key);
						if (strpos($src, $key) !== false){
							$this->mcache->delete($cache_key);
							if (DISPLAY_ERRORS){
								Message::register('<span style="color: #CC0000;">Cached Data Deleted For </span><strong style="color: #FF0000;">'.$this->cache_key_read($cache_key).'</strong>');
							}
						}
					}
				}
			}
		}
	}
	
	public function get($sql, $params = null){
		// memcache
		if ($this->mcache && $this->cache){
			$key = $this->cache_key($sql, $params);
			$res = $this->mcache->get($key);
			if ($res){
				return $res;
			}
		}
		$data = $this->exec($sql, $this->check_params($params));
		if (isset($data[0])){
			// memcache
			if ($this->mcache && $this->cache){
				$this->mcache->set($key, $data[0], false, $this->expire);
			}
			return $data[0];
		}
		else {
			return null;
		}
	}
	
	public function getAll($sql, $params = null){
		// memcache
		if ($this->mcache && $this->cache){
			$key = $this->cache_key($sql, $params);
			$res = $this->mcache->get($key);
			if ($res){
				return $res;
			}
		}
		$res = $this->exec($sql, $this->check_params($params));
		// memcache
		if ($this->mcache && $this->cache){
			$this->mcache->set($key, $res, false, $this->expire);
		}
		if (empty($res)){
			$res = false;
		}
		return $res;
	}
	
	public function send($sql, $params = null){
		return $this->exec($sql, $this->check_params($params), "send");
	}
	
	public function debugger($active = true, $echo = false){
		$this->debug = $active;
		$this->echo = $echo;
	}
	
	public function show_cache($echo = true){
		 $this->debug_cache = true;
		 $this->echo = $echo;
	}
	
	public function connect($db){
		$src = unserialize(DEF);
		if ($src){
			$src = $src['DB'];
			try {
				$db = $src[$db];
				$this->db_type = $db['type'];
				$this->db = $db['db'];
				$this->cn = new PDO($this->db_type.":host=".$db['host'].";dbname=".$db['db'], $db['user'], $db['password'], array(PDO::ATTR_PERSISTENT =>  true));
				return true;
			}
			catch(PDOException $e) {
				$this->error("------ SQL::connect ------");
				$this->error($e->getMessage());
				$this->error($src[$db]['type'].'@'.$src[$db]['host'].'('.$db.')'.': user = '.$src[$db]['user'].' : password = '.$src[$db]['password'].' : database = '.$src[$db]['db']);
				$this->error("------ SQL::connect ------");
				return false;
			}
		}
		else {
			return false;
		}
	}
	
	public function database_type(){
		return $this->db_type;
	}
	
	// InnoDB and Postresql only
	public function transaction(){
		$this->cn->beginTransaction();
	}
	
	public function commit(){
		$this->cn->commit();
	}
	
	public function rollBack(){
		$this->cn->rollBack();
	}
	
	private function cache_key($sql, $params){
		return str_replace(' ', '', $sql).';'.serialize($params);
	}
	
	private function check_params($params){
		if ($params){
			if (is_array($params)){
				return $params;
			}
			else {
				return array($params);
			}
		}
		else {
			return null;
		}
	}
	
	private function chk_debug($sql, $params, $results = false){
		if ($this->debug){
			if ($this->echo) {
				if ($results){
					echo('SQL::exec > '.$sql);
					trace($params);
				}
				else {
					echo("SQL::exec > ".$sql."; using[".implode(', ', $params)."]"."<br>");
					echo("<br>");
				}
			}
			else {
				if ($results){
					error_log('SQL::exec > '.$sql);
					error_log(print_r($params, true));
				}
				else {
					error_log("SQL::exec > ".$sql."; using[".implode(', ', $params)."]");
					error_log("");
				}
			}
		}
	}
	
	private function exec($sql, $params = null, $type = "get"){
		$this->chk_debug($sql, $params);
		$st = $this->cn->prepare($sql);
		if ($type == "get"){
			$res = $this->getter($st, $params);
		}
		else {
			$res = $this->sender($st, $params);
		}
		$this->chk_debug($sql, $res, true);
		return $res;
	}
	
	private function getter($st, $params = null){
		$st->execute($params);
		if ($st->errorCode() !== '00000') {
			$info = $st->errorInfo();
			error_log('*** Error >>> ' . $st->queryString . ': [' . $st->errorCode() . '] ' . $info[2]);
			throw new Exception($st->queryString . ': [' . $st->errorCode() . '] ' . $info[2]);
		}
		$res = $st->fetchAll(PDO::FETCH_ASSOC);
		return $res;
	}	
	
	private function sender($st, $params = null){
		$st->execute($params);
		if ($st->errorCode() !== '00000') {
			$info = $st->errorInfo();
			error_log('*** Error >>> ' . $st->queryString . ': [' . $st->errorCode() . '] ' . $info[2]);
			throw new Exception($st->queryString . ': [' . $st->errorCode() . '] ' . $info[2]);
		}
		$res = $st->rowCount();
		return array('rows' => $res, 'statement' => $st);
	}	
}
?>
