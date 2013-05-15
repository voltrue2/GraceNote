<?php

class CacheManager {
	
	private $view;
	private $controller;	

	public function CacheManager($view, $controller) {
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

	public function index($from = 0, $search = null) {
		$num = 50;
		$to = $num + $from;
		$cache = new Cache();
		$keyList = $cache->getAllKeys();
		$list = array();
		if ($search) {
			// TODO: come up with paging
			// search listing
			for ($i = 0, $len = count($keyList); $i < $len; $i++) {
				$key = $keyList[$i];
				$index = strpos($key, $search);
				if ($index !== false) {
					// highlight matched chars
					$head = substr($key, 0, $index);
					$body = substr($key, strlen($head), strlen($search));
					$tail = substr($key, strlen($head) + strlen($body));
					$key = $head . '<span style="text-decoration: underline; color: #f00;">' . $body . '</span>' . $tail;
					$list[] = $key;
				}
			}
		} else {
			// none search listing
			for ($i = $from, $len = count($keyList); $i < $len; $i++) {
				if ($i === $to) {
					break;
				}
				$list[] = $keyList[$i];
			}
		
		}
		$this->view->assign('search', $search);
		$this->view->assign('totalNum', $len);
		$this->view->assign('from', $from);
		$this->view->assign('to', count($list) + $from);
		$this->view->assign('num', $num);
		$this->view->assign('list', $list);
		$this->view->respondTemplate('cachemanager/index.html.php');
	}

	public function getValue() {
		$key = $this->controller->getQuery('key');
		$cache = new Cache();
		$this->view->assign('value', $cache->get($key, false));
		$this->view->respondJson();
	}

	public function delete() {
		$key = $this->controller->getQuery('key');
		$cache = new Cache();
		$res = $cache->delete($key, false);
		return  $this->view->respondJson();
	}
}

?>
