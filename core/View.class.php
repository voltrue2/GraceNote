<?php 

class View {

	private $router = null;
	private $uri = null;
	private $vars = array();
	private $templatePath = '';
	
	public function View($router, $root) {
		$this->router = $router;
		$this->uri = $this->router->getUri();
		$this->templatePath = $root . 'template/';
	}
	
	public function assign($var, $value) {
		if (is_numeric($value)) {
			$value = (int)$value;
		}
		$this->vars[$var] = $value;
	}
	
	// output to the client with template
	public function respondTemplate($templateFile) {
		$path = $this->templatePath . $templateFile;
		if (file_exists($path)) {
			// pass all assigned variables to all template files
			Loader::setTemplateVars($this->vars);
			ob_start();
			extract($this->vars);
			include($path);
			$results = ob_get_contents();
			ob_end_clean();
			echo $results;
			return;
		}
		Log::error('[VIEW] respondTemplate > Template file does not exist >> ' . $path);
		$this->router->handleError(500, $this, microtime(true));
	}
	
	// output to the client as JSON
	public function respondJson($gzip = true) {
		if ($gzip) {
			header('Cache-Control: no-cache, must-revalidate');
			header('Content-Type: text/plain');
			header('Content-Encoding: gzip');
			ob_start('ob_gzhandler');
			echo json_encode($this->vars);
			ob_end_flush();
		} else {
			header("Cache-Control: no-cache, must-revalidate");
			header("Cache-Type: application/json");
			echo json_encode($this->vars);
		}
	}
	
	public function checkFileMod($fileName) {
		// check if-modified-since
		$modified = true;
		$fileModTime = filemtime($fileName);
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
			$ifModifiedSince = $_SERVER['HTTP_IF_MODIFIED_SINCE'];
			if (strtotime($ifModifiedSince) === $fileModTime) {
				$modified = false;
			}
		}
		return array( 'modified' => $modified, 'mtime' => $fileModTime);
	}
	
	public function respondImage($fileName, $fileData, $fileModTime) {
		// if $fileData is NOT provided, we consider to use cahce with modified-since
		if (!$fileData) {
			header('HTTP/1.1 304 Not Modified');
			header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $fileModTime).' GMT', true, 304);
		} else {
			$fileType = pathinfo($fileName, PATHINFO_EXTENSION);
			header('Last-Modified: '.gmdate('D, d M Y H:i:s', $fileModTime).' GMT', true, 200);
			header('Cache-Control: must-revalidate');
			header('Content-type: image/' . $fileType);
			header('Content-transfer-encoding: binary');
			header('Content-length: ' . filesize($fileName));
			echo $fileData;
		}
	}
	
	public function respondDownload($fileName, $fileData) {
		header('Content-Description: File Transfer');
    		header('Content-Transfer-Encoding: binary');
		header('Content-Disposition: attachment; filename="' . $fileName . '"');
		echo $fileData;
	}
	
	public function respondError($errorCode, $errorMsg = null) {
		Log::error('[VIEW] respondError > Error Code >> ' . $errorCode);
		if ($errorMsg) {
			Log::error('[VIEW]', $errorMsg);
		}
		$this->router->handleError($errorCode, $this, microtime(true));
	}
}

?>
