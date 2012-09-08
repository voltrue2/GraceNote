<?php
Load::model('ContentModel');
Load::lib('XML');

class View extends Base{
	
	private $smarty = false;
	private $file_type;
	private $assigned_vars = array();
	private $tpl_path = false;
	private $return_path = 'controller_prev_path';
	private $main = false;
	private $cache_dir = false;
	private $contents_key = 'CONTENTS';
	private $error_code = false;
	private $static = false;

	public function View($main, $smarty = false){
		$this->main = $main;
		$this->smarty = $smarty;
	}

	// call this method from action object to throw an error and display error page
	public function return_error($code){
		if ($this->header($code)){
			// display error page and exit
			$this->main->display_error_page($code);
		}
	}

	public function referer(){
		return $this->get_session('PREVIOUS_URL');
	}
	
	public function tpl_path($path){
		if ($path){
			$this->tpl_path = $path;
		}
		if ($this->smarty){
			$this->file_type = SMARTY_TPL_TYPE;
		}
		else {
			$this->file_type = TPL_TYPE;
		}
		return $this->file_type;
	}
	
	public function set_header($header){
		$this->header = $header;
	}

	public function assign($name, $value){
		if ($this->smarty){
			$this->smarty->assign($name, $value);
		}
		$this->assigned_vars[$name] = $value;
	}
	
	public function contents($table, $alt_key = false){
		$contents = new ContentModel($table, $this->lang());
		$text = $contents->get();
		if ($alt_key){
			$key = $alt_key;
		}
		else {
			$key = $this->contents_key;
		}
		$this->assign($key, $text);
		return $text;
	}
	
	public function list_contents($table, $alt_key = false, $get_key = false){
		$contents = new ContentModel($table, $this->lang());
		$text = $contents->get_all($get_key);
		if ($alt_key){
			$key = $alt_key;
		}
		else {
			$key = $this->contents_key;
		}
		$this->assign($key, $text);
		return $text;
	}
	
	public function queries($key = false){
		$q = $this->get('QUERIES');
		if ($key){
			if (isset($q[$key])){
				return $q[$key];
			}
			else {
				return false;
			}
		}
		else {
			return $q;
		}
	}
	
	public function get($name = false){
		if ($name){
			if (isset($this->assigned_vars[$name])){
				return $this->assigned_vars[$name];
			}
			else {
				return false;
			}
		}
		else {
			return $this->assigned_vars;
		}
	}

	// data output options : xml OR json OR php/serialize
	public function push($output_type, $value = false, $download = false){
		if ($this->error_code){
			return;
		}
		if ($output_type){
			$o = strtolower($output_type);
			$encoding = $this->queries('encoding');
			$data = $value;
			if (!$data){
				$data = $this->get();
			}
			if ($o == 'xml'){
				// output as XML
				$xml_convertor = new XML();
				$data = $xml_convertor->array_to_xml($data, $encoding);
				header("Cache-Control: no-cache, must-revalidate");
				header("Cache-Type: text/xml");
			}
			else if ($o == 'json'){
				// output as JSON
				$data = json_encode($data);
				/*
				$encoding = mb_detect_encoding($json);
				$encoding = 'UTF-8';
				header("Cache-Control: no-cache, must-revalidate");
				header('Content-Type: application/x-javascript; charset='.$encoding);
				*/
				header("Cache-Control: no-cache, must-revalidate");
				header("Cache-Type: application/json");
				if ($this->queries('callback') && !$download){
					$json = $this->queries('callback').'('.$json.');';
				}
			}
			else if ($o == 'serialize' || $o == 'php'){
				// output as php serialize
				echo serialize($data);
			}
			else if ($o == 'csv'){
				header("Cache-Control: no-cache, must-revalidate");
				header('Content-type: tex/csv');
				echo($data);
			}
			if ($download){
				header('Content-Disposition: attachment; filename="'.$download.'"');
			}
			echo($data);
		}
		exit();
	}	
	
	public function display($path = false){
		if ($this->error_code){
			return;
		}
		// set the assigned varibales for Load::template
		Load::set_vars($this->get());
		if ($path){
			// output as HTML page
			if ($this->smarty){
				// smarty template output
				$this->smarty->display($this->tpl_path.$path.$this->file_type);
			}
			else {
				if (file_exists($this->tpl_path.$path.$this->file_type)){
					// GraceNote native template output
					if (DISPLAY_ERRORS){
			                       $timer = new Timer(1);
			                }
					ob_start();
					extract($this->get());
					include($this->tpl_path.$path.$this->file_type);
					$results = ob_get_contents();
					ob_end_clean();
					$results = $this->header.$results;
					if (DISPLAY_ERRORS){
			                        Message::register('<span style="color: #0000CC;">Rendering Time <span style="color: #FF0000;">[ '.round($timer->get() * 1000, 4).' ms ]</span></span>');
			                }
					echo($results);
				}
				else {
					$this->main->error('View::display > Template resource not found > '.$this->tpl_path.$path.$file_type);
					$this->return_error(404);
				}
			}
		}
	}
	
	public function fetch($path, $alt_params = false){
		if ($this->error_code){
			return;
		}
		// set the assigned varibales for Load::template
		if (is_array($alt_params)){
			$params = $alt_params;
		}
		else{
			$params = $this->get();
		}
		$path = $this->tpl_path.$path.$this->file_type;
		if ($this->smarty){
			// smarty template output
			return $this->smarty->fetch($path);
		}
		else {
			if (file_exists($path)){
				// GraceNote native template output
				ob_start();
				if (!empty($params)){
					extract($params);
				}
				include($path);
				$results = ob_get_contents();
				ob_end_clean();
				return $results;
			}
			else {
				$this->main->error('View::fetch > Template resource not found > '.$path);
				return false;
			}
		}
		return false;
	}

	public function lang(){	
		// try session first
		$res = $this->get_session(LANG_QUERY_NAME);
		if (!$res){
			// try cookie then
			$res = $this->get_cookie(LANG_QUERY_NAME);
			if (!$res){
				// fall back to default
				$res = DEFAULT_LANG;
			}
		}
		return $res;
	}
}
?>
