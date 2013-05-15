<?php 

class Error {
	
	private $view;
	private $controller;
	
	public function Error($view, $controller) {
		$this->view = $view;
		$this->controller = $controller;
	}

	public function notFound() {
		$args = func_get_args();
		echo '404 ERROR<br/>';
		for ($i = 0; $i < count($args); $i++) {
			echo $args[$i] . '<br/>';
		}
	}
	
	public function busy() {
		echo '500 ERROR<br/>';
		for ($i = 0; $i < count($args); $i++) {
			echo $args[$i] . '<br/>';
		}
	}
}

?>