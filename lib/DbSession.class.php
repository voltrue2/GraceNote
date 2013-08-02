<?php

class DbSession {

	private $table;
	private $conf;
	private $duration = 0;

	public function DbSession(){
		$this->conf = Config::get('DbSession');
		try {
			if (!isset($this->conf['db']) || !isset( $this->conf['duration'])) {
				throw new Exception('Missing duration in the configuration');
			}
			$dm = new DataModel($this->conf['db']);
			$this->table = $dm->table('sessions');
			$this->duration = $this->conf['duration'];
		} catch (Exception $e) {
			Log::debug($e->getMessage());
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
		$this->table->select('value');
		$this->table->select('signature');
		$this->table->where('session_id = ?', $sessionId);
		$this->table->and('expr >= ?', strtotime('NOW'));
		$res = $this->table->getOne(0, false);
		if ($res && isset($res['value'])) {
			// check user signature
			if (isset($res['signature']) && $res['signature'] === $this->getUserSignature()) {
				return $res['value'];
			}
		}
		return null;
	}
	
	// called on session_write
	public function write($sessionId, $value, $expr = null){	
		// set expiration date
		if (!$expr){
			// if no expriation date provided, set the expriation date for 24 hours
			$expr = strtotime('+ ' . $this->duration);
		}
		if ($value) {
			$this->destroy($sessionId);
			session_regenerate_id(true);
			$this->table->set('session_id', session_id());
			$this->table->set('value', $value);
			$this->table->set('expr', $expr);
			$this->table->set('signature', $this->getUserSignature());
			$res = $this->table->save();	
			if ($res) {
				return true;
			}
		}
		return false;
	}
	
	// called on session destroy
	public function destroy($sessionId){
		// delete old session with the same ID
		$this->table->where('session_id = ?', $sessionId);
		return $this->table->delete();
	}
	
	// called on session garbage control
	public function gc($maxlifetime){
		$this->table->where('expr <= ?', time());
		return $this->table->delete();
	}

	private function getUserSignature() {
		$ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
		$ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : ''; 
		return md5($ip . $ua);
	}
}

// setup DB session handler
$session = new DbSession();
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
