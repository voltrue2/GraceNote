<?php

class DbSession {

	private $table;
	private $conf;
	private $duration = 0;

	public function DbSession(){
		$this->conf = Config::get('DbSession');
		try {
			$dm = new DataModel($this->conf['db']);
			$this->table = $dm->table('sessions');
			if (!isset( $this->conf['duration'])) {
				throw new Exception('Missing duration in the configuration');
			}
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
		$this->table->where('session_id = ?', $sessionId);
		$this->table->and('expr >= ?', strtotime('NOW'));
		$res = $this->table->getOne(0, false);
		if ($res && isset($res['value'])) {
			return $res['value'];
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
			/*
			// try to update first
			$this->table->where('session_id = ?', $sessionId);
			$this->table->set('value', $value);
			$this->table->set('expr', $expr);
			$res = $this->table->update();
			if (!$res) {
				// now try insert
				$this->table->set('session_id', $sessionId);
				$this->table->set('value', $value);
				$this->table->set('expr', $expr);
				$res = $this->table->save();
			}
			*/
			$this->destroy($sessionId);
			$this->table->set('session_id', $sessionId);
			$this->table->set('value', $value);
			$this->table->set('expr', $expr);
			$res = $this->table->save();	
			if ($res) {
				return true;
			}
			return false;
		} else {
			return false;
		}
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
?>
