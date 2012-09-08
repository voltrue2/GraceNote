<?php
/************************************************
	Image Class with Imagik class 
************************************************/

class Image {

	private $name = "Image";
	private $width = 100;
	private $height = 0;
	private $ratio = true;
	private $bk = "black";
	private $b;
	private $parent = null;
	private $ref = null;
	private $type = "gif";
	
	public function Image($width = 100, $height = 0, $bk = "black", $type = "gif"){
		//$this->b = new Basic($this->name);
		$this->width = $width;
		$this->height = $height;
		$this->bk = $bk;
		$this->setType($type);
		$this->setup_parent();
	}
	
	public function get(){
		return $this->parent;
	}
	
	public function ignoreAspectRatio(){
		$this->ratio = false;
	}
	
	public function keepAspectRatio(){
		$this->ratio = true;
	}
	
	public function setReflection($alpha = 0.3){
		// reflection body
		$this->ref = $this->parent->clone();
		$this->ref->flipImage();
		// gradient overlay for the body
		$gradient = new Imagick();
		// add gradient
		$gradient->newPseudoImage($this->ref->getImageWidth(), $this->ref->getImageHeight(), "gradient:transparent-".$this->bk);
		// apply the overlay
		$this->ref->compositeImage($gradient, imagick::COMPOSITE_OVER, 0, 0);
		// apply alpha
		$this->ref->setImageOpacity($alpha);
		// add the reflection
		$this->add($this->ref, 0, $this->parent->getImageHeight());
		// remove to free the memory
		$this->ref->destroy();
	}
	
	public function width(){
		return $this->parent->getImageWidth();
	}
	
	public function height(){
		return $this->parent->getImageHeight();
	}
	
	public function scale($w, $h){
		return $this->parent->sampleImage($w, $h);
	}

	public function copy($src = null, $width = 100, $height = 0){
		try {
			if ($this->ratio){
				$h = 0;
			}
			else {
				$h = $height;
			}
			$img = new Imagick($src);
			$img->thumbnailImage($width, $h);
			return $img;
		}
		catch (Exception $e){
			//$this->b->error("copy > ".$e." : src = ".$src);
			return null;
		}
	}
	
	public function text($str = "", $x = 0, $y = 0, $size = 12, $font = "Arial", $color = "black", $angle = 0){
		$text = new ImagickDraw();
		$c = new ImagickPixel($color);
		$text->setFillColor($c);
		$text->setFont($font);
		$text->setFontSize($size);
		$this->parent->annotateImage($text, $x, $y, $angle, $str);
	}
	
	public function draw($width = 100, $height = 100, $color = "black", $alpha = 1){
		$img = new Imagick();
		$img->newImage($width, $height, new ImagickPixel($color));
		//$img->setImageOpacity($alpha);
		return $img;
	}
	
	public function add($child_image = null, $x = 0, $y = 0){
		if ($child_image){
			try {
				$this->parent->compositeImage($child_image, imagick::COMPOSITE_OVER, $x, $y);
			}
			catch (Exception $e){
				//$this->b->error("add > Invalid Child Image > ".$e);
			}
		}
		else {
			//$this->b->error("add > Child Image Not Given");
		}
	}

	public function display(){
		header('Content-type: image/'.$this->type);
		echo($this->parent);
		$this->parent->destroy();
	}
	
	public function write($path){
		$this->parent->writeImage($path);
		$this->parent->destroy();
	}

	private function setup_parent(){
		$this->parent = new Imagick();
		try {
			$this->parent->newImage($this->width, $this->height, new ImagickPixel($this->bk));
			$this->setup_type();
		}
		catch (Exception $e){
			//$this->b->error("_constructor > ".$e);
		}
	}
	
	private function setup_type(){
		// get file type
		if ($this->type == "jpg"){
			$this->type = "jpeg";
		}
		$this->parent->setImageFormat($this->type);
	}

	private function setType($t = null){
		if ($t){
			if (strtolower($t) == "jpg"){
				$t = "jpeg";
			}
			$this->type = $t;
		}
		else {
			//$this->b->error("setType > Image Type Not Given");
		}
	}
}
?>