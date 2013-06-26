<?php

class CacheManager extends Controller {
	
	private $view;

	public function CacheManager($view) {
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
		$this->view->redirect('/', 401);
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
			$counter = $from;
			for ($i = $from, $len = count($keyList); $i < $len; $i++) {
				if ($counter === $to) {
					break;
				}
				$key = $keyList[$i];
				$index = strpos($key, $search);
				if ($val = $cache->get($key, false) && $index !== false) {
					// highlight matched chars
					$head = substr($key, 0, $index);
					$body = substr($key, strlen($head), strlen($search));
					$tail = substr($key, strlen($head) + strlen($body));
					$key = $head . '<span style="text-decoration: underline; color: #f00;">' . $body . '</span>' . $tail;
					$list[] = array('key' => $key);
					$counter += 1;
				}
			}
		} else {
			// none search listing
			$counter = $from;
			for ($i = $from, $len = count($keyList); $i < $len; $i++) {
				if ($counter === $to) {
					break;
				}
				if ($val = $cache->get($keyList[$i], false)) {
					$list[] = array('key' => $keyList[$i]);
					$counter += 1;
				}
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
		$key = $this->getQuery('key');
		$cache = new Cache();
		$value = $cache->get($key, false);
		$this->view->assign('value', $value);
		$this->view->respondJson();
	}

	public function delete() {
		$key = $this->getQuery('key');
		$cache = new Cache();
		$res = $cache->delete($key, false);
		return  $this->view->respondJson();
	}
}

?>
