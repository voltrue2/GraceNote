<?php
// include SQL model class
Load::model('SessionModel');

class SessionHandler {
	private $model;
	
	public function SessionHandler(){
		$this->model = new SessionModel();
	}
	
	/* I think this does nothing 2011/02/13
	public function getConnection(){
		return $this->model->getConnection();
	}
	*/
	
	// called on session_start
	public function start($save_path, $session_name) {
		return true;
	}
	
	// called on session_end
	public function close() {
		return true;
	}
	
	// called on session_read
	public function read($session_id) {
		return $this->model->read($session_id);
	}
	
	// called on session_write
	public function write($session_id, $value, $expr = null){	
		// set expiration date
		if (!$expr){
			// if no expriation date provided, set the expriation date for 24 hours
			$expr = strtotime("+ ".SESSION_EXPIRATION);
		}
		/*
		// force an array to be a string
		if (is_array($value)){
			$value = @serialize($value);
		}
		*/
		// write session
		return $this->model->write($session_id, $value, $expr);
	}
	
	// called on session destroy
	public function destroy($session_id){
		return $this->model->delete($session_id);
	}
	
	// called on session garbage control
	public function gc($maxlifetime){
		return $this->model->garbageControl();
	}
}

// setup DB session handler
$session = new SessionHandler();
session_set_save_handler(array(&$session, "start"), array(&$session, "close"), array(&$session, "read"), array(&$session, "write"), array(&$session, "destroy"), array(&$session, "gc"));
?>