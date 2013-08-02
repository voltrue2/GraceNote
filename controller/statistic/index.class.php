<?php

class Statistic extends Controller {
	
	private $view;

	public function Statistic($view) {
		$this->view = $view;
		// check for authentication
		$sess = CmsAuthHandler::check($view, $this);
		if ($sess) {
			// authenticated
			$this->sess = $sess;
			Text::get($view, $this, 'text');
			return;
		}
		// not authenticated remember where you were
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
