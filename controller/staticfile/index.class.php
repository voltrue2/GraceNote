<?php 

class Staticfile Extends Controller {
	
	private $view;
	private $fs;
	
	public function Staticfile($view) {
		// setup
		$this->view = $view;
		$sdConf = Config::get('StaticData');
		$this->fs = new FileSystem($sdConf['sourcePath']);
		// check for authentication
		$sess = CmsAuthHandler::check($view, $this);
		if ($sess) {
			// authenticated
			Text::get($view, $this, 'text');
			return;
		}
		// not authenticated remember where you were
		$this->view->redirect('/', 401);
	}

	public function index() {
		$dataList = array();
		// get list of static files
		$list = $this->fs->listFiles();	
		$this->view->assign('fileList', $list);
		$this->view->respondTemplate('staticfile/index.html.php');
	}

	public function getDirList($from = 0, $to = 50) {
		$path = $this->getPath('path');
		$list = $this->fs->listFiles($path);
		if ($from !== 'nolimit') {
			$this->view->assign('more', (count($list) > $from + $to) ? true : false);
			$list = array_splice($list, $from, $to);
		}
		$this->view->assign('from', $from);
		$this->view->assign('list', $list);
		$useGzip = true;
		$this->view->respondJson($useGzip);
	}

	public function rename() {
		$srcPath = $this->fs->getFullPath($this->getPath('path'));
		$oldPath = $srcPath . $this->getPath('oldPath', true);
		$newPath = $srcPath . $this->getPath('newPath', true);
		$res = rename($oldPath, $newPath);
		if (!$res) {
			Log::error($srcPath, $oldPath, $newPath);
			return $this->view->respondError(404);	
		}
		$useGzip = true;
		$this->view->assign('success', true);
		$this->view->respondJson($useGzip);		
	}

	public function download() {
		$path = $this->getPath('path', true);
		$data = $this->fs->readFile($path);
		$fileName = basename($path);
		$this->view->respondDownload($fileName, $data);
	}

	public function upload() {
		$path = $this->fs->getFullPath($this->getPath('path'));
		$images = $this->getFile('images');
		$tmpNames = $images['tmp_name'];
		$fileNames = $images['name'];
		$count = count($tmpNames);
		try {
			for ($i = 0; $i < $count; $i++) {
				// only alpha numeric and -, _, . are allowed
				if (preg_match('/[^a-z_\-\.\_0-9]/i', $fileNames[$i])) {
					throw new Exception('alpha numeric only');
				}
				$success = move_uploaded_file($tmpNames[$i], $path . '/' . $fileNames[$i]);
				if (!$success) {
					throw new Exception('failed to upload > ' . $path . '/' . $fileNames[$i]);
				}
				GlobalEvent::emit('staticfile.upload', array($this->getSession(), $fileNames[$i]));
			}
			$success = true;
		} catch (Exception $e) {
			Log::error('upload failed', $e->getMessage());
			$success = false;
		}
		$useGzip = true;
		$this->view->assign('success', $success);
		$this->view->respondJson($useGzip);
	}

	public function deleteFile() {
		$path = $this->fs->getFullPath($this->getPath('path', true));
		$res = unlink($path);
		if (!$res) {
			Log::error('deleteFile > failed to delete > ' . $path);
		}
		$useGzip = true;
		$this->view->assign('success', $res);
		$this->view->respondJson($useGzip);
	}

	public function deleteFolder() {
		// construct path and prefix 
		$path = $this->fs->getFullPath($this->getPath('path'));
		$res = rmdir($path);
		$useGzip = true;
		$this->view->assign('success', $res);
		$this->view->respondJson($useGzip);
	}

	public function createDir() {
		$path = $this->fs->getFullPath($this->getPath('path'));
		mkdir($path);
		$useGzip = true;
		$this->view->assign('success', $true);
		$this->view->respondJson($useGzip);
	}

	public function getFileData() {
		$path = $this->getQuery('path');
        $dm = new DataModel('StaticData');
        $fd = $dm->staticData();
        $data = $fd->getMany($path);
        $this->view->assign('data', $data);
		$useGzip = true;
		$this->view->respondJson($useGzip);
	}

	private function getPath($pathName, $file = false) {
		$path = $this->getQuery($pathName);
		$sess = CmsAuthHandler::get();
		// force the path based on fileRestriction
		if ($sess['fileRestriction'] !== $path) {
			if ($path === '/') {
				$path = '';
			}
			if (substr($sess['fileRestriction'], strlen($sess['fileRestriction']) - 1, 1) !== '/' && substr($path, 0, 1) !== '/') {
				$path = '/' . $path;
			}
			$path = $sess['fileRestriction'] . $path;
			if (!$file) {
				if (substr($path, strlen($path) - 1) !== '/') {
					$path .= '/';
				}
			}
		}
		return $path;
	}
}
