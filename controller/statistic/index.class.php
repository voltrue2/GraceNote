<?php

class Statistic {
	
	private $view;
	private $controller;

	public function Statistic($view, $controller) {
		$this->view = $view;
		$this->controller = $controller;
		// check for authentication
		$sess = CmsAuthHandler::check($view, $controller);
		if ($sess) {
			// authenticated
			$this->sess = $sess;
			Text::get($view, $controller, 'text');
			return;
		}
		// not authenticated remember where you were
		//$sess['prevUri'] = $this->controller->getUri();
		//$this->controller->setSession($sess);
		$this->controller->redirect('/', 401);
	}

	public function index() {
		$this->view->assign('types', Report::getTypes());
		$this->view->respondTemplate('statistic/index.html.php');
	}

	public function getData($type) {
		$this->view->assign(Report::getbyType($type));
		$this->view->respondJson();
	}
}

?>
