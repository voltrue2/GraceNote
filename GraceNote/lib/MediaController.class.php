<?php
Load::lib('Dir');

class MediaController extends Dir {
	
	private $dir = false;
	private $path = false;
	private $img = false;
	private $ext = array(
		'gif'   => 'image', 
		'png'   => 'image', 
		'jpg'   => 'image', 
		'jpeg'  => 'image', 
		'pdf'   => 'document',
		'doc'   => 'document',
		'xls'   => 'document',
		'csv'   => 'document',
		'pdf'   => 'document',
		'swf'   => 'flash',
		'flv'   => 'media',
		'mp4'   => 'media',
		'avi'   => 'media',
		'mov'   => 'media',
		'mp3'   => 'media',
		'm4a'   => 'media'
	);
	
	public function MediaController($path){
		parent::__construct($path);
		$this->path = $path; // serever image path
		$this->img =  str_replace(DOC_ROOT, '/', $this->path); // document root image path
	}
	
	public function get_list($stat = false){
		$list = $this->show_list($stat);
		if ($list){
			$res = array();
			foreach ($list as $i => $item){
				if ($item['type'] == 'file'){
					// read supported file ONLY
					$ext = $this->extension($item['name']);	
					if (isset($this->ext[$ext])){
						$item['file_type'] = $ext;
						$item['file_category'] = $this->ext[$ext];
						$res[] = $item;
					}
				}
				else {
					// directory
					$res[] = $item;
				}	
			}
			if (empty($res)){
				return $res;
			}	
			return $res;
		}
		else {
			return false;
		}
	}

	public function upload($tmp_name, $filename){
		$ext = $this->extension($filename);	
		if (isset($this->ext[$ext])){
			$res = $this->move_file($tmp_name, $filename);
			$this->change_mod($filename, 0755);
			if (isset($this->ext[$ext]) && $this->ext[$ext] == 'image'){
				// check for auto image formats
				$this->auto_image_format($filename);
			}
			return $res;
		}
		else {
			return false;
		}
	}
	
	/**
	* params[file_name], [src_path], [width], [height], [aspect_ratio], [crop], [output], [file_type]
	*/
	public function resize($params){
		$src_path = $this->array_value($params, 'src_path');
		$width = $this->array_value($params, 'width');
		$height = $this->array_value($params, 'height');
		$aspect_ratio = $this->array_value($params, 'aspect_ratio');
		$file_name = $this->array_value($params, 'file_name');
		// if cropping -> original aspect ratio is ignored
		if ($this->array_value($params, 'crop')){
			$aspect_ratio = false;
		}
		// image 
		try {
			$copy = new Imagick($src_path);
		}
		catch (Exception $e) {
			error($e);
			return;
		}
		$org_width = $copy->getImageWidth();
		$org_height = $copy->getImageHeight();
		$ratio = $org_width / $org_height;
		if ($aspect_ratio == 'width' && $width){
			// fix to width and keep the aspect ratio
			$height = $width / $ratio;
		}
		else if ($aspect_ratio == 'height' && $height){
			// fix to height and keep the aspect ratio
			$width = $height * $ratio;
		}
		else if ($width && !$height){
			// only width provided -> keep aspect ratio
			$height = $width / $ratio;
		}
		else if (!$width && $height){
			// only height provided -> keep aspect ratio
			$width = $height * $ratio;
		}
		else if (!$width && !$height){
			// no size provided -> keep the original size
			$width = $org_width;
			$height = $org_height;
		}
		// crop
		if ($this->array_value($params, 'crop')){
			$x = floor(($org_width - $width) / 2);
			$y = floor(($org_height - $height) / 2);
			$copy->cropImage($width, $height, $x, $y);
		}
		// resize
		$copy->thumbnailImage($width, $height);
		// output
		$last_index = strrpos($src_path, '.') + 1;
		$file_type = substr($src_path, $last_index, strlen($src_path) - $last_index);
		if (isset($params['file_type']) && isset($this->ext[strtolower($params['file_type'])])){
			$file_name = mb_ereg_replace($file_type, $params['file_type'], $file_name);
			$file_type = $params['file_type'];
		}
		if ($this->array_value($params, 'output') == 'save' && $file_name && $file_name != $src_path){
			// for GIF file format -> imagick bug workaround -> make sure to send the right page size
			if ($copy->getImageFormat() == 'GIF'){
				$copy->setImagePage($width, $height, 0, 0);
			}
			// create an image file	
			$copy->setImageFormat($file_type);
			$res = $copy->writeImage($file_name);
		}
		else {
			// display
			header('Content-Type: image/'.$file_type);
			echo($copy);
		}
		$copy->destroy();
	}
	
	private function auto_image_format($filename){
		$def = unserialize(DEF);
		$formats = $def['IMAGE_FORMATS'];
		if ($formats){
			foreach ($formats as $prefix => $values){
				// add parameters for resize
				$values['output'] = 'save';
				$values['file_name'] = $this->current_dir().$prefix.$filename;
				$values['src_path'] = $this->current_dir().$filename;
				// resize
				$this->resize($values);
			}
		}
	}
	
	private function extension($filename){
		$index = strrpos($filename, '.') + 1;
		return strtolower(substr($filename, $index, strlen($filename) - $index));
	}
}
?>
