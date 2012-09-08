<?php
Load::action('header/Header');
Load::lib('custom/LoginManager');

class Error {
	
	private $view;
	
	public function Error($view){
		$this->view = $view;
		$this->loginmanager = new LoginManager($this->view);
		$header = new Header($this->view);
	}
	
	public function not_found(){
		$this->view->display('index');
	}
}
?>