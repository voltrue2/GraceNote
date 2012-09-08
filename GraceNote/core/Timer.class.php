<?
class Timer {
	private $start;
	private $pause_time;

	public function Timer($start = 0){
		if($start) { 
			$this->start(); 
		}
	}

	public function start(){
		$this->start = $this->get_time();
		$this->pause_time = 0;
	}

	public function pause(){
		$this->pause_time = $this->get_time();
	}

	public function resume(){
		$this->start += ($this->get_time() - $this->pause_time);
		$this->pause_time = 0;
	}

	public function get($decimals = 8){
		return round(($this->get_time() - $this->start), $decimals);
	}

	private function get_time(){
		list($usec, $sec) = explode(' ', microtime());
		return ((float)$usec + (float)$sec);
	}
}
?>
