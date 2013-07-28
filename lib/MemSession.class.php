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
		$res = $this->cache->get($this->getKey($sessionId));

		Log::debug('read (' . $_SERVER['REQUEST_URI'] . ') >>>>> ' . $sessionId, $res);
		
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
		$success = $this->cache->set($this->getKey(session_id()), $session);

		Log::debug('session write (' . $_SERVER['REQUEST_URI'] . ')>>>>>>>>>> ', session_id(), $session, $success);

		return $success;
	}
	
	// called on session destroy
	public function destroy($sessionId){

		LOg::debug('session destroy -----------------> ', $sessionId);
		
		return $this->cache->delete($this->getKey($sessionId));
	}
	
	// called on session garbage control
	public function gc($maxlifetime){
		$now = strtotime('NOW');
		// TODO: maybe we need to clean cache?
		return true;
	}

	private function getKey($sessionId) {
		return $this->prefix . $sessionId;
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
session_name('GNS');
session_start();
?>
