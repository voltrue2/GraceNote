<?php
class Media {

	private $view = null;	
	private $controller = null;
	private $conf = null;
	private $cache = null;

	public function Media($view, $controller) {
		$this->view = $view;
		$this->controller = $controller;
		$this->conf = Config::get('Asset');	
		$this->cache = new Cache();
	}
	
	public function img() {
		$args = func_get_args();
		$path = implode('/', $args);
		$initGet = false;
		$sd = new StaticData($path, $initGet);
		$path = $sd->getSourcePath() . $path;
		$res = $this->view->checkFileMod($path);
		if (!$res['modified']) {
			// file has NOT been modified
			Log::debug('[MEDIA] image not modified > ' . $path);
			$data = null;
			
		} else {
			// file has been modified
			$data = $sd->getSource();
		}
		$this->view->respondImage($path, $data, $res['mtime']);
	}
}
?>