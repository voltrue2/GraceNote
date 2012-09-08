<?php
Load::core("smarty/Smarty");

class TemplateHandler {
	
	private $smarty;
	private $prefix = null;

	public function TemplateHandler($prefix = null){
		$this->prefix = $prefix;
		$this->smarty = new Smarty();
		$this->smarty->template_dir = TPL_PATH;
		$this->smarty->compile_dir  = BASE_PATH.'templates_c/';
		$this->smarty->config_dir   = BASE_PATH.'configs/';
		$this->smarty->cache_dir    = BASE_PATH.'cache/';
	}
	
	public function set($key = null, $value = null){
		if ($key){
			$this->smarty->assign($key, $value);
			return true;
		}
		else {
			return false;
		}
	}
	
	public function fetch($path){
		return $this->smarty->fetch($this->prefix.$path);
	}
	
	public function display($path){
		$this->smarty->display($path);
	}
}
?>
