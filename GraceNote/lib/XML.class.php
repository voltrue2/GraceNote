<?php
class XML {
	
	private $type = false;
	private $el = 'elements';
	private $att = 'attributes';
	private $text = 'text';
	private $array = array();
	private $head = '';
	private $foot = '';
	
	public function XML(){
		
	}
	
	public function wrapper($h, $f){
		$this->head = $h;
		$this->foot = $f;
	}
	
	public function array_to_xml($a){
		if (is_array($a) && !empty($a)){
			return $this->head . $this->parse_array($a) . $this->foot;
		}
		else {
			return false;
		}
	}
	
	private function parse_array($a, $parent_key = false){
		$str = '';
		foreach ($a as $i => $item){
			$key = $this->key($i);
			$value = $this->value($item, $key);
			$att = '';
			if (isset($item[$this->att])){
				if (is_array($item[$this->att])){
					foreach ($item[$this->att] as $k => $v){
						$att .= $k.'="'.$v.'" ';
					}
					$att = ' '.$att;
				}
			}
			if ($key && $key != $this->att){
				if (is_array($item) && !empty($item) && isset($item[0])){
					$str .= $value;
				}
				else {
					$str .= '<'.$key.$att.'>'.$value.'</'.$key.'>';
				}
			}
			else if ($key != $this->att){
				$str .= '<'.$parent_key.$att.'>'.$value.'</'.$parent_key.'>';
			}
		}
		return $str;
	}
	
	private function key($i){
		if (is_numeric($i)){
			return false;
		}
		else {
			return $i;
		}
	}
	
	private function value($item, $key){
		if (is_array($item) && !empty($item)){
			return $this->parse_array($item, $key);
		}
		else {
			return $item;
		}
	}
	
	public function xml_to_array($xml, $encoding = 'UTF-8'){
		return $this->parse_xml($xml, $encoding);
	}
	
	private function parse_xml($contents, $encoding) {
		$priority = 'tag';
		if (!$contents) {
			return false;
		} 
		
		if (!function_exists('xml_parser_create')) { 
			return false; 
		} 
		
		$parser = xml_parser_create(''); 
		xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, $encoding);
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0); 
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1); 
		xml_parse_into_struct($parser, trim($contents), $xml_values); 
		xml_parser_free($parser); 
		
		if(!$xml_values) {
			return false;
		}
		
		$xml_array = array(); 
		$parents = array(); 
		$opened_tags = array(); 
		$arr = array(); 
		
		$current = &$xml_array;
		
		foreach($xml_values as $data) { 
			unset($attributes, $value);
			extract($data);
			$result = array(); 
			$attributes_data = array(); 
			if(isset($value)) { 
			    if($priority == 'tag') {
			    	$result = $value; 
			    }
			    else {
			    	$result[$this->text] = $value;
			    }
			} 
			if(isset($attributes)) { 
				foreach($attributes as $attr => $val) { 
					if($priority == 'tag') {
						$attributes_data[$attr] = $val;
					}
					else {
						$result[$this->att][$attr] = $val;
					} 
				} 
			} 
			if($type == "open") {
				$parent[$level-1] = &$current; 
			    	if(!is_array($current) || (!in_array($tag, array_keys($current)))) {
			        	$current[$tag] = $result; 
			        	if($attributes_data) {
			        		$current[$tag.'_'.$this->att] = $attributes_data;
			        	}
			        	$repeated_tag_index[$tag.'_'.$level] = 1; 		
			        	$current = &$current[$tag]; 
			
			    	} 
			    	else {
			        	if(isset($current[$tag][0])) {
			            		$current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result; 
			            		$repeated_tag_index[$tag.'_'.$level]++; 
			        	} 
			        	else {
			            		$current[$tag] = array($current[$tag],$result);
			            		$repeated_tag_index[$tag.'_'.$level] = 2; 
						if(isset($current[$tag.'_'.$this->att])) {
							$current[$tag]['0_'.$this->att] = $current[$tag.'_'.$this->att]; 
							unset($current[$tag.'_'.$this->att]); 
						} 
			        	} 
			        	$last_item_index = $repeated_tag_index[$tag.'_'.$level]-1; 
			        	$current = &$current[$tag][$last_item_index]; 
			    	} 
			
			} 
			else if($type == "complete") {
			    	if(!isset($current[$tag])) {
					$current[$tag] = $result; 
			        	$repeated_tag_index[$tag.'_'.$level] = 1; 
			        	if($priority == 'tag' and $attributes_data) {
			        		$current[$tag. '_' . $this->att] = $attributes_data;
			        	}
			
				    } 
				    else {
				    	if(isset($current[$tag][0]) && is_array($current[$tag])) {
				            $current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result; 
				             
				            if($priority == 'tag' and $get_attributes and $attributes_data) { 
				                $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_' . $this->att] = $attributes_data; 
				            } 
				            $repeated_tag_index[$tag.'_'.$level]++; 
				
				        } 
				        else {
				            $current[$tag] = array($current[$tag],$result);
				            $repeated_tag_index[$tag.'_'.$level] = 1; 
				            if($priority == 'tag' and $get_attributes) { 
				                if(isset($current[$tag.'_'.$this->att])) {
				                    $current[$tag]['0_'.$this->att] = $current[$tag.'_'.$this->att]; 
				                    unset($current[$tag.'_'.$this->att]); 
				                } 
				                if($attributes_data) { 
				                    $current[$tag][$repeated_tag_index[$tag.'_'.$level] . '_'.$this->att] = $attributes_data; 
				                } 
				            } 
				            $repeated_tag_index[$tag.'_'.$level]++; 
				        } 
				    } 
			
			}
			else if($type == 'close') { 
			    $current = &$parent[$level-1]; 
			} 
		}  
		return $xml_array; 
    	}
	
	/*
	public function parse_xml($xml, $encoding = 'utf8', $parent_name = null){
		$array = array(); 
	        $reels = '/<(\w+)\s*([^\/>]*)\s*(?:\/>|>(.*)<\/\s*\\1\s*>)/s';
	        $reattrs = '/(\w+)=(?:"|\')([^"\']*)(:?"|\')/';
	        $elements = array();
	        preg_match_all($reels, $xml, $elements);
		if (!empty($elements)){
		        foreach ($elements[1] as $ie => $xx) {
		                $name = $elements[1][$ie];
		                $attributes = $elements[2][$ie];
		                if ($attributes) {
		                	$att = array();
		                        preg_match_all($reattrs, $attributes, $att);
		                        foreach ($att[1] as $ia => $xx) {
		                                $array[$name][$this->att][$att[1][$ia]] = $att[2][$ia];
		                        }
		                }
		                $cdend = mb_strpos($elements[3][$ie], '<');
		                if ($cdend > 0) {
		                	$m = explode('><', $elements[0][$ie]);
		                	if (!empty($m)){
		                		$o = mb_internal_encoding();
		                		mb_internal_encoding($encoding);
		                		foreach ($m as $i => $item){
		                			$f = mb_strpos($item, '<');
		                			$e = mb_strpos($item, '>');
		                			$value = $item;
		                			if ($f !== 0){
		                				$value = '<'.$value;
		                			}
		                			if ($e !== mb_strlen($item)){
		                				$value = $value.'>';
		                			}
		                			$res = $this->parse_xml($value, $encoding, $name);
		                			
		                			error_log($parent_name." = ".$name." = ".$value." = ".$res);
		                			
		                			if ($res){
		                				$array[$name][] = $res;
		                			}
		                		}
		                	}
		                }
		                else if (preg_match($reels, $elements[3][$ie]) && $name != $parent_name) {
		                        $array[$name][$this->el] = $this->parse_xml($elements[3][$ie], $encoding, $name);
		                }
		                else if ($elements[3][$ie] && $name != $parent_name) {
		                	$array[$name][$this->text] = $elements[3][$ie];
		                }
		                else if ($elements[3][$ie] && $name == $parent_name) {
		                	$array[$this->text] = $elements[3][$ie];
		                }
		                
		        }
		        return $array;
	        }
	        else {
	        	return false;
	        }
	}
	*/
	
	/*
	public function parse_xml($xml, $encoding = 'utf8'){
		$array = array(); 
	        $reels = '/<(\w+)\s*([^\/>]*)\s*(?:\/>|>(.*)<\/\s*\\1\s*>)/s';
	        $reattrs = '/(\w+)=(?:"|\')([^"\']*)(:?"|\')/';
	        $elements = array();
	        preg_match_all($reels, $xml, $elements);
		if (!empty($elements)){
		        foreach ($elements[1] as $ie => $xx) {
		                $name = $elements[1][$ie];
		                //$attributes = mb_ereg_replace(' ', '', $elements[2][$ie]);
		                $attributes = $elements[2][$ie];
		                if ($attributes) {
		                	$att = array();
		                        preg_match_all($reattrs, $attributes, $att);
		                        foreach ($att[1] as $ia => $xx) {
		                                $array[$name][$this->att][$att[1][$ia]] = $att[2][$ia];
		                        }
		                }
		                $cdend = mb_strpos($elements[3][$ie], '<');
		                if ($cdend > 0) {
		                	$o = mb_internal_encoding();
		                	mb_internal_encoding($encoding);
		                        $text = mb_substr($elements[3][$ie], 0, $cdend - 1);
		                        mb_internal_encoding($o);
		                        if ($text){
		                        	$array[$name][$this->text] = $text;
		                        }
		                }
		                else if (preg_match($reels, $elements[3][$ie])) {
		                        $array[$name][$this->el] = $this->parse_xml($elements[3][$ie], $encoding);
		                }
		                else if ($elements[3][$ie]) {
		                	$array[$name][$this->text] = $elements[3][$ie];
		                }    
		        }
		        return $array;
	        }
	        else {
	        	return false;
	        }
	}
	*/
}
?>