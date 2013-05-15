<?php

class MemSession {

	private $cache;
	private $duration = 0;
	private $prefix = 'sess/';

	public function MemSession() {
		$this->cache = new Cache('MemSession');
		$conf = Config::get('MemSession');
		if ($conf && isset($conf['duration'])) {
			$this->duration = $conf['duration'];
		}
	}
	
	// called on session_start
	public function start($savePath, $sessionName) {
		return true;
	}
	
	// called on session_end
	public function close() {
		return true;
	}
	
	// called on session_read
	public function read($sessionId) {
		$res = $this->cache->get($this->prefix . $sessionId);
		if ($res) {
			if ($res['expr'] >= strtotime('NOW')) {
				Log::debug('MemSession::read (sessionId: ' . $sessionId . ') >> ', $res);
				return $res['value'];
			}
		}
		return null;
	}
	
	// called on session_write
	public function write($sessionId, $value, $expr = null){
		if (!$value) {
			return false;
		}	
		// set expiration date
		if (!$expr){
			// if no expriation date provided, set the expriation date for 24 hours
			$expr = strtotime('+ ' . $this->duration);
		}
		// override the session
		$session = array(
			'expr' => $expr,
			'value' => $value
		);
		return $this->cache->set($this->prefix . $sessionId, $session);
	}
	
	// called on session destroy
	public function destroy($sessionId){
		return $this->cache->delete($sessionId);
	}
	
	// called on session garbage control
	public function gc($maxlifetime){
		$now = strtotime('NOW');
		// TODO: maybe we need to clean cache?
		return true;
	}
}

// setup DB session handler
$session = new MemSession();
session_set_save_handler(
	array(&$session, 'start'), 
	array(&$session, 'close'), 
	array(&$session, 'read'), 
	array(&$session, 'write'), 
	array(&$session, 'destroy'), 
	array(&$session, 'gc')
);
?>
