<?php 
require_once(LIB_PATH.'PFC.class.php');

class DBF extends Base {
	
	private $pfc;
	private $path = false;
	private $mcache_exp = MCACHE_EXP;
	private $mcache = false;
	private $enable_debug = false;
	
	public function DBF($table_name){
		$this->path = PFC_PATH.$table_name.'/';
		$this->pfc = new PFC($this->path);
		// memcache
		if (class_exists('Memcache')){
			$this->mcache = new Memcache();
			$this->mcache->pconnect(MCACHE_HOST, MCACHE_PORT);
		}		
	}

	public function debug($active = true){
		$this->enable_debug = $active;
	}

	public function set($key, $value){
		$this->create_dir();
		$this->mcache->delete($this->cache_key($key));
		if ($this->enable_debug){
			$this->trace('<span style="font-size: 11px; color: #990000;">DBF::get > cache deleted = '.$this->cache_key($key).'</span>');
		}
		return $this->pfc->write($key, $value);
	}
	
	public function get($key){
		// from Memcache
		$res = $this->mcache->get($this->cache_key($key));
		if (!$res){
			// from PFC
			$res = $this->pfc->read($key);
			if ($this->mcache && $res){
				$this->mcache->set($this->cache_key($key), $res, false, $this->mcache_exp);
				if ($this->enable_debug){
					$this->trace('<span style="font-size: 11px; color: #0000FF;">DBF::get > cache set = '.$this->cache_key($key));
					$this->trace($res);
					$this->trace('</span>');
				}
			}
		}
		else if ($this->enable_debug){
			$this->trace('<span style="font-size: 11px; color: #009900;">DBF::get > cache retrieved = '.$this->cache_key($key));
			$this->trace($res);
			$this->trace('</span>');
		}
		return $res;
	}
	
	public function search($key, $open_file = false){
		return $this->pfc->search($key, $open_file);
	}
	
	public function delete($key = false){
		if ($key){
			$this->mcache->delete($this->cache_key($key));
			$this->pfc->remove($key);
		}
		else {
			$this->mcache->flush();
			$this->pfc->flush();
			rmdir($this->path);
		}
	}
	
	public function check(){
		return file_exists($this->path);
	}
	
	private function create_dir(){
		if (!file_exists($this->path)){
			mkdir($this->path);
		}
	}
	
	private function cache_key($key){
		return str_replace('/', '_', $this->path.$key);
	}
}
?>