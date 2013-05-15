<?php
class ClientLogger {

	private $view;
	private $controller;
	private $header = '[Client]';

	public function ClientLogger ($view, $controller) {
		$this->view = $view;
		$this->controller = $controller;
	}

	public function index() {
		$type = $this->controller->getQuery('type');
		$msg = $this->controller->getQuery('msg');
		if ($msg) {
			switch ($type) {
				case 'verbose':
					Log::verbose($this->header, $msg);
					break;
				case 'debug':
					Log::debug($this->header, $msg);
					break;
				case 'info':
					Log::info($this->header, $msg);
					break;
				case 'warn':
					Log::warn($this->header, $msg);
					break;
				case 'error':
					Log::error($this->header, $msg);
					break;
				case 'fatal':
					Log::fatal($this->header, $msg);
					break;
			}
		}
		$this->view->respondJson();
	}

}
?>
