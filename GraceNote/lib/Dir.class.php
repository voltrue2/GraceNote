<?php 
class Dir extends Base {
	
	private $path;
	private $file_path;
		
	// This class will NOT allow you to navigate outside of document root
	public function Dir($path){
		$this->move($path);
	}
	
	public function move($input_path){
		$path = $this->read_path($input_path); 
		if (strpos($path, DOC_ROOT) !== false){
			$this->path = $path;
		}
		else {
			// trying to go outside of doc root -> reject!
			$this->path = DOC_ROOT;
		}
		$this->file_path = str_replace(DOC_ROOT, '/', $this->path);
	}
	
	public function create_dir($dir){
		return mkdir($this->path.$dir);
	}
	
	public function remove_dir($dir){
		return rmdir($this->path.$dir);
	}
	
	public function change_mod($file, $mod){
		// 0600 Read and write for owner, nothing for everybody else
		// 0644 Read and write for owner, read for everybody else
		// 0755 Everything for owner, read and execute for others
		// 0750 Everything for owner, read and execute for owner's group
		return chmod($this->path.$file, $mod);
	}
	
	public function move_file($tmp_path, $filename){
        	return move_uploaded_file($tmp_path, $this->path.$filename);
	}
	
	public function remove_file($file){
		return unlink($this->path.$file);
	}
	
	public function show_list($stat = false){
		try {			
			$handle = opendir($this->path);
			$list = array();
			while (($file = readdir($handle)) !== false) {
				$i = $file;
				if (is_file($this->path.$file)){
					// file
					$list[$i]['type'] = 'file';
					$list[$i]['name'] = $file;
					$list[$i]['path'] = $this->path.$file;
					$list[$i]['file_path'] = $this->file_path.$file;
					if ($stat){
						$list[$i]['stat'] = stat(DOC_ROOT.$this->file_path.$file);
					}
					else {
						$list[$i]['stat'] = false;
					}
				}
				else {
					// directory
					if ($file == '..'){
						// previous location
						$seps = explode('/', $this->path);
						$c = count($seps) - 2;
						$path = '';
						foreach ($seps as $j => $item){
							if ($j < $c){
								$path .= $item.'/';
							}
						}
						if (strpos($path, DOC_ROOT) !== false){
							// within document root
							$list[$i]['type'] = 'prev';
							$list[$i]['name'] = $file;
							$list[$i]['path'] = $path;
							$list[$i]['file_path'] = false;
						}
					}
					else if ($file != '.'){
						// directory
						$list[$i]['type'] = 'dir';
						$list[$i]['name'] = $file;
						$list[$i]['path'] = $this->path.$file.'/';
						$list[$i]['file_path'] = false;
					}
				}
			}
			if (empty($list)) {
				$list = false;
			}
			ksort($list);
			return $list;
		}
		catch (Exception $e) {
			$this->error('Dir::show_list > '.$e);
			return false;
		}
	}
	
	public function current_dir(){
		return $this->path;
	}
	
	public function read_path($input_path){
		$sep = explode('/', $input_path);
		$path = '/';
		if ($sep){
			$reverse = '..';
			$tail = '';
			foreach ($sep as $i => $item){
				if ($item == '..'){
					// go back one directory
					$path = str_replace($tail, '', $path);
				}
				else if ($item){
					// go forward one directory
					$path .= $item . '/';
				}
				$src = substr($path, 0, -1);
				$pos = strrpos($src, '/');
				$tail = substr($src, $pos + 1, strlen($src) - $pos) . '/';
			}
		}
		return $path;
	}
}
?>