<?php 
class Cache {
	
	private $memcache = null;
	private $maxKeyLen = 250; // key that is bigger than this will not be stored
	private $expiration = '1 day'; // default is one day

	/***
	* Configuration name: Memcache
	* Configurations
	* {
	*	"host": "hostName",
	*	"port": portNumber,
	*	"flush": true/false, this is development use only
	*	"expiration": "1 day" (optional)
	* }
	*/
	public function Cache($altConf = null) {
		$confName = 'Cache';
		if ($altConf) {
			$confName = $altConf;
		}
		if (class_exists('Memcache')){
			$conf = Config::get($confName);
			if ($conf && isset($conf['host']) && isset($conf['port'])) {
				$this->memcache = new Memcache();
				$this->memcache->pconnect($conf['host'], $conf['port']);
			} else {
				throw new Exception('Cache::constructor > No configuration for Cache');
			}
		} else {
			throw new Exception('Cache::constructor > Memcache not found.');
		}
		// check for expiration
		if (isset($conf['expiration'])) {
			$this->expiration = strtotime('+1 ' . $conf['expiration']);
		}
		// we configuration is set flush: true > we flush cache
		if (isset($conf['flush']) && $conf['flush']) {
			$this->flush();
		}
	}

	public function get($keySrc, $useHash = true) {
		if ($useHash) {
			$key = $this->getKey($keySrc);
		} else {
			$key = $keySrc;
		}
		$res = $this->memcache->get($key);
		Log::verbose('[CACHE] get > key source: ' . $keySrc . ' > key: ' . $key . ' [cache read: ' . (($res) ? 'true]' : 'false]'));
		return $res;
	}

	public function set($keySrc, $value, $useHash = true) {
		if ($useHash) {
			$key = $this->getKey($keySrc);
		} else {
			$key = $keySrc;
		}
		$res = $this->memcache->set($key, $value, MEMCACHE_COMPRESSED, $this->expiration);
		Log::verbose('[CACHE] set > key source: ' . $keySrc . ' > key: ' . $key . ' [cache set: ' . (($res) ? 'true' : 'false') . ']');
		return $res;
	}
	
	public function delete($keySrc, $useHash = true) {
		if ($useHash) {
			$key = $this->getKey($keySrc);
		} else {
			$key = $keySrc;
		}
		$res = $this->memcache->delete($key);
		Log::verbose('[CACHE] delete > key source: ' . $keySrc . ' > key: ' . $key . ' [cache deleted: ' . (($res) ? 'true' : 'false') . ']');
		return $res;
	}

	public function flush() {
		Log::verbose('[CACHE] flush');
		return $this->memcache->flush();
	}

	// CMS or debug purpose ONLY
	public function getAllKeys() {
		$list = array();
		$allslabs = $this->memcache->getExtendedStats('slabs');
		$items = $this->memcache->getExtendedStats('items');
		foreach($allslabs as $server => $slabs) {
			foreach($slabs as $slabid => $slabmeta) {
				$id = (int)$slabid;
				if ($id){
					$cdump = $this->memcache->getExtendedStats('cachedump', $id);
					foreach($cdump as $server => $entries) {
						if($entries) {
							foreach($entries as $ename => $edata) {
								$list[] = $ename;								
							}
						}
					}
				}
			}
		}
		return $list;
	}

	private function getKey($keySrc) {
		return md5($keySrc);
	}
}
