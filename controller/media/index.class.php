<?php
class Media Extends Controller{

	private $view = null;	
	private $conf = null;

	public function Media($view) {
		$this->view = $view;
		$this->conf = Config::get('Media');	
	}
	
	public function img() {
		$args = func_get_args();
		$path = implode('/', $args);
		$initGet = false;
		$fs = new FileSystem($this->conf['img']);
		$fullPath = $fs->getFullPath($path);
		$res = $this->view->checkFileMod($fullPath);
		if (!$res['modified']) {
			// file has NOT been modified
			Log::debug('[MEDIA] image not modified > ' . $path);
			$data = null;
			
		} else {
			// file has been modified
			$data = $fs->readFile($path);
		}
		$this->view->respondImage($fullPath, $data, $res['mtime']);
	}
}
?>
