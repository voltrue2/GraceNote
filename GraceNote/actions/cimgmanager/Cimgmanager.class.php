<?php 
Load::action('header/Header');
//Load::lib('ImageController');
Load::lib('MediaController');
Load::lib('custom/LoginManager');

class Cimgmanager extends Base {
	
	private $image;
	private $model;
	private $loginmanager;
	private $c;
	private $q;
	private $current_dir;
	private $anchor;
	private $thumb = 'thumb.';
	
	public function Cimgmanager($view){
		$this->c = $view;
		$this->q = $this->c->get('QUERIES');
		$this->loginmanager = new LoginManager($this->c);
		$this->session = $this->loginmanager->get();
		if (!$this->session){
			// not logged in -> redirect
			$this->c->redirect('http://'.HOST.'/login/', 302);
		}
		if (isset($this->session['media_restriction'])){
			$this->anchor = CIMG_PATH.$this->session['media_restriction'];
		}
		else {
			$this->anchor = CIMG_PATH;
		}
		$header = new Header($this->c);
		if (isset($this->q['current_dir'])){
			$path = $this->anchor.$this->q['current_dir'];
		}
		else {
			$path = $this->anchor;
		}	
		$this->media = new MediaController($path);
		$this->current_dir = $this->get_current_dir($path);
		$this->media->move($this->current_dir);
	}
	
	public function menu(){
		$this->check_permission();
		$this->c->contents('cimgmanager_contents');
		$this->display();
	}
	
	public function rename(){
		if (!isset($this->session['root_access']['cimgmanager']) || !$this->session['root_access']['cimgmanager']){
			// no permission
			$this->c->return_error(403);
			return false;
		}
		$this->check_permission();
		$current_path = $this->c->queries('current_path');
		$old_name = $this->c->queries('old_name');
		$name = $this->c->queries('name');
		$path = substr($current_path, strpos($current_path, '/') + 1, strlen($current_path));
		$old = $this->current_dir.$path.$old_name;
		$new = $this->current_dir.$path.$name;
		$res = rename($old, $new);
		$this->c->redirect('/cimgmanager/?current_dir='.$current_path);
	}
	
	public function mkdir(){
		if (!isset($this->session['root_access']['cimgmanager']) || !$this->session['root_access']['cimgmanager']){
			// no permission
			$this->c->return_error(403);
			return false;
		}
		$this->check_permission();
		$error = '';
		if (isset($this->q['dir'])){
			$res = $this->media->create_dir($this->q['dir']);
			if (!$res){
				$error = '&error=create_dir_failed';
			}
		}
		$this->c->redirect('/cimgmanager/?current_dir='.str_replace($this->anchor, '/', $this->current_dir).$error);
	}
	
	public function rmdir(){
		if (!isset($this->session['root_access']['cimgmanager']) || !$this->session['root_access']['cimgmanager']){
			// no permission
			$this->c->return_error(403);
			return false;
		}
		$this->check_permission();
		$error = '';
		if (isset($this->q['dir'])){
			$res = $this->media->remove_dir($this->q['dir']);
			if (!$res){
				$error = '&error=remove_dir_failed';
			}
		}
		$this->c->redirect('/cimgmanager/?current_dir='.str_replace($this->anchor, '/', $this->current_dir).$error);
	}
	
	public function upload(){
		if (!isset($this->session['root_access']['cimgmanager']) || !$this->session['root_access']['cimgmanager']){
			// no permission
			$this->c->return_error(403);
			return false;
		}
		$this->check_permission();
		$error = '';
		if (isset($this->q['image'])){
			$tmp = $this->q['image']['tmp_name'];
			$filename = $this->q['image']['name'];
			for ($i = 0; $i < count($tmp); $i++){
				$res = $this->media->upload($tmp[$i], $filename[$i]);
			}
		}
		$this->c->redirect('/cimgmanager/?current_dir='.str_replace($this->anchor, '/', $this->current_dir).$error);
	}
	
	public function resize(){
		if (!isset($this->session['root_access']['cimgmanager']) || !$this->session['root_access']['cimgmanager']){
			// no permission
			$this->c->return_error(403);
			return false;
		}
		$this->check_permission();
		$path = substr($this->anchor, 0, strlen($this->anchor) - 1);
		$this->media->resize(array(
			'src_path'     => $path.$this->c->queries('current_dir').$this->c->queries('src_path'),
			'width'        => $this->c->queries('width'),
			'height'       => $this->c->queries('height'),
			'aspect_ratio' => $this->c->queries('aspect_ratio'),
			'crop'         => $this->c->queries('crop'),
			'file_name'    => $path.$this->c->queries('current_dir').$this->c->queries('name'),
			'output'       => $this->c->queries('output')
		));
		$this->c->redirect('/cimgmanager/?current_dir='.str_replace($this->anchor, '/', $this->current_dir));
	}
	
	public function rmfile(){
		if (!isset($this->session['root_access']['cimgmanager']) || !$this->session['root_access']['cimgmanager']){
			// no permission
			$this->c->return_error(403);
			return false;
		}
		$this->check_permission();
		$error = '';
		if ($this->q['file']){
			$res = $this->media->remove_file($this->q['file']);
			if (!$res){
				$error = '&error=remove_file_failed';
			}
		}
		$this->c->redirect('/cimgmanager/?current_dir='.str_replace($this->anchor, '/', $this->current_dir).$error);
	}
	
	public function image_list(){
		$this->display(true);
	}
	
	public function media_list(){
		$this->display();
	}
	
	private function get_list($image_only = false){
		$res = $this->media->get_list(true); // get list of files and directories WITH file/dir status
		if ($res){
			$list = array();
			foreach ($res as $item){
				if (strpos($item['path'], $this->anchor) !== false){
					$item['path'] = str_replace($this->anchor, '', $item['path']);
					if (strpos($item['name'], $this->thumb) === false){
						$item['thumb'] = str_replace($item['name'], $this->thumb.$item['name'], $item['file_path']);
						if (!file_exists(DOC_ROOT.$item['thumb'])){
							$item['thumb'] = $item['file_path'];
						}
					}
					else {
						$item['thumb'] = $item['file_path'];
					}
					if ($image_only && isset($item['file_category']) && $item['file_category'] != 'image'){
						$item = false;
					}
					if ($item){
						$list[] = $item;
					}
				}
			}
			if (empty($list)){
				$list = false;
			}
		}
		else {
			// directry does not exist -> redirect
			$this->c->redirect('/cimgmanager/');
			
		}
		return $list;
	}
	
	private function get_current_dir($path){
		$p = $this->media->read_path($path);
		$butt = substr($p, -1);
		if ($butt != '/'){
			$p .= '/';
		}
		if (strpos($p, $this->anchor) !== false){
			return $p;
		}
		else {
			return $this->anchor;
		}
	}
	
	private function display($image_only = false){
		$list = $this->get_list($image_only);
		$current_path = $this->media->current_dir();
		$this->c->assign('LIST', $list);
		$this->c->assign('CURRENT_PATH', str_replace($this->anchor, '/', $current_path));
		if ($this->c->queries('data_type') == 'json' || $this->c->queries('data_type') == 'xml'){
			$this->c->push($this->c->queries('data_type'), array('list' => $list, 'current_path' => $this->c->get('CURRENT_PATH')));	
		}
		else {
			$this->c->display('init');
		}
	}
	
	private function check_permission(){
		if (!$this->loginmanager->check_permission('cimgmanager')){
			// no permission
			$this->c->redirect('/menu/');
		}
	}
}
?>