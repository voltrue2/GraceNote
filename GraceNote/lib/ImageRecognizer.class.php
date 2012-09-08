<?php
class ImageRecognizer {

	private $iteration = 100; // will check 1000 pixcels
	private $tolarence = 3; // consider the gap within 3% as same

	public function ImageRecognizer(){
		
	}
	
	public function set_iteration($i){
		$this->iteration = $i;
	}
	
	public function set_tolarence($t){
		$this->tolarence = $t;
	}
	
	public function compare($src_one, $src_two, $accuracy = 100) {
		$src1 = imagecreatefromjpeg($src_one);
		$src2 = imagecreatefromjpeg($src_two);
		$size1 = getimagesize($src_one);
		$size2 = getimagesize($src_two);
		$r = 0.8;
		if ($size1[0] <= $size2[0]){
			$maxx = $size1[0];
		}
		else {
			$maxx = $size2[0];
		}
		$maxx = floor($maxx * $r);
		if ($size1[1] <= $size2[1]){
			$maxy = $size1[1];
		}
		else {
			$maxy = $size2[1];
		}
		$maxy = floor($maxy * $r);
		$match_count = 0;
		for ($i = 0; $i < $this->iteration; $i++){
			$x = mt_rand(0, $maxx);
			$y = mt_rand(0, $maxy);
			$px1 = imagecolorsforindex($src1, imagecolorat($src1, $x, $y));
			$px2 = imagecolorsforindex($src2, imagecolorat($src2, $x, $y));
			$res = $this->is_same($px1, $px2);
			if ($res){
				$match_count++;
			}
		}
		$match_ratio = ($match_count / $this->iteration) * 100;
		
		trace($match_count);
		trace($this->iteration);
		trace($match_ratio);
		
		if ($match_ratio >= $accuracy){
			return true;
		}
		else {
			return false;
		}
	}
	
	private function is_same($c1, $c2){
		$r1 = $c1['red'];
		$g1 = $c1['green'];
		$b1 = $c1['blue'];
		$a1 = $c1['alpha'];
		$r2 = $c2['red'];
		$g2 = $c2['green'];
		$b2 = $c2['blue'];
		$a2 = $c2['alpha'];
		// check the color differences
		$rdiff = $this->diff($r1, $r2);
		$gdiff = $this->diff($g1, $g2);
		$bdiff = $this->diff($b1, $b2);
		$adiff = $this->diff($a1, $a2);
		// get the average color difference
		$avg = ($rdiff + $gdiff + $bdiff + $adiff) / 4;
		// check with tolarence
		if ($avg <= $this->tolarence){
			return true;
		}
		else {
			return false;
		}
	}
	
	private function diff($one, $two){
		$diff = $one - $two;
		if ($diff < 0){
			$diff = $diff * -1;
		}
		if ($diff > 0){
			$r = $diff / ($one + $two) * 100;
		}
		else {
			$r = 0;
		}
		return $r;
	}

/*	
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
*/
}
?>