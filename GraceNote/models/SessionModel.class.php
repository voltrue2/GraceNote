<?php
Load::core("orm/Model");

class SessionModel{

	private $model;
	private $sessions; 
	
	public function SessionModel(){
		$this->model = new Model('admin');
		$this->sessions = $this->model->table('sessions');
	}

	public function create(){
		$this->sessions->set_auto_increment_column('id', false, 'primary');
		$this->sessions->set_column('session_id', 'varchar', 32);
		$this->sessions->set_column('value', 'text');
		$this->sessions->set_column('expr', 'int');
		$this->sessions->set_key('session_id', 'UNIQUE INDEX');
		$this->sessions->create();
	}

	public function read($session_id){
		/*
		$sql = "SELECT value FROM sessions WHERE session_id = ? AND expr >= '".strtotime("NOW")."'";
		$data = $this->dealmate->get($sql, $session_id);
		if (isset($data["value"])){
			$str = @unserialize($data["value"]);
			if ($str){
				return $str;
			}
			else {
				return $data["value"];
			}
		}
		*/
		$this->sessions->cache(false);
		$this->sessions->select('value');
		$this->sessions->cond('session_id = ?', $session_id);
		$this->sessions->cond('expr >= ?', strtotime('NOW'));
		$data = $this->sessions->find();
		if ($data){
			/*
			$str = @unserialize($data['value']);
			if ($str){
				return $str;
			}
			else {
				if (isset($data['value'])){
					return $data["value"];
				}
				else {
					return $data;
				}
			}
			*/
			if (isset($data['value'])){
				return $data["value"];
			}
			else {
				return $data;
			}
		}
		else {
			return false;
		}
	}

	public function write($session_id, $value, $expr){
		/*
		$sql = "REPLACE INTO sessions (session_id, value, expr) VALUES(?, ?, ?)";
		return $this->dealmate->send($sql, array($session_id, $value, $expr));
		*/
		if ($value){
			$this->sessions->cond('session_id = ?', $session_id);
			$this->sessions->delete();
			$this->sessions->set('session_id', $session_id);
			$this->sessions->set('value', $value);
			$this->sessions->set('expr', $expr);
			return $this->sessions->save();
		}
		else {
			return false;
		}
	}

	public function delete($session_id){
		/*
		$sql = "DELETE FROM sessions WHERE session_id = ?";
		return $this->dealmate->send($sql, $session_id);
		*/
		$this->sessions->cond('session_id = ?', $session_id);
		return $this->sessions->delete();
	}

	public function garbageControl(){
		/*
		$sql = "DELETE FROM sessions WHERE expr < '".strtotime("NOW")."'";
		return $this->dealmate->send($sql);
		*/
		$this->sessions->cond('expr <= ?', strtotime("NOW"));
		return $this->sessions->delete();
	}
}
?>