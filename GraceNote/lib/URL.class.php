<?php    
class URL
{
    private $paths = null; // path components from the URL
    private $pattern; // output array keys
    private $url; // URL passed from the caller
    private $slash = "/";
    private $question = "?";
    private $ampersand = "&";
    private $equal = "=";
    private $def = "a-z-A-Z-0-9\-\%\!\@\^\_\+\.\$\=\[\]\(\)\{\}\*\<\>\,\ "; // regex pattern definition

    public function URL($url_in = "", $pattern_in = null, $ignore_no_match = false){ 
	// set URL
        $this->url = urldecode($url_in);
        // set up formats
        $this->pattern = $pattern_in;
        // start parsing URL
        $this->paths = $this->parse($ignore_no_match);
    }
    
    // Returns the parsed URL components
    public function get($key = null){
    	if ($this->paths != null){
	        if ($key !== null && array_key_exists($key, $this->paths)){
	            return $this->paths[$key];
	        }
	        else if ($key === null){
	            return $this->paths;
	        }
	        else {
	            return null;
	        }
        }
        else {
        	return null;
        }
    }
    
    private function parse($ignore_no_match){
    	// remove possible spaces
    	$this->url = str_replace(' ', '', $this->url);
        // prepare the given URL
        $preped = parse_url($this->url);
        // check the parsed URL
        $this->check_parsed($preped);
        // check to see if there is anything to parse
        if ($preped["path"] == $this->slash){
            // there is nothing to parse
            return null;
        }
        else {
            // keep the original url as source
            $paths["source"] = $preped;
            // look for parameters
            if (array_key_exists("query", $preped) !== false){
                $params = $this->get_params($preped["query"]);                
                if (is_array($params)){
                    $paths = array_merge($paths, $params);
                }
            }
            // check if $pattern is valid and given
            if (empty($this->pattern)){
                // no pattern is given
            	$seps = explode($this->slash, $preped["path"]);
                $blank = array("");
                $seps = array_diff($seps, $blank);
                $paths = array_merge($paths, $seps);
            }
            else {
                // we have a pattern to follow
                $uri = $preped["path"];
                // look for pattern matches                       
                $result_value = null;
                if (!empty($this->pattern) && count($this->pattern) > 0){
	                foreach ($this->pattern as $j => $key){               
	                    if (strpos($uri, $key) !== false){
	                        // match
	                        $res = $this->extract_matched($uri, $key);
	                        $result_value = $res["value"];
	                        $uri = $res["uri"];  
	                    }
	                    else {
	                        // no match
	                        // ignore_no_match will cause to skip the key that is not in the given url
	                        if (!$ignore_no_match){
	                            $res = $this->extract_nomatched($uri, $key);
	                            $result_value = $res["value"];
	                            $uri = $res["uri"];
	                        }
	                    }                                                        
	                    if ($result_value !== false && $result_value !== "" && $result_value !== null){
	                        if (array_key_exists($key, $paths) === false){
	                            $paths[$key] = $result_value;
	                        }
	                    }
	                }
                }
                // look for leftovers
                if ($uri !== "" && $uri !== $this->slash){
                	$leftovers = explode($this->slash, $uri);
                	if (is_array($leftovers)){
                		// append leftover values
                		$leftovers = array_diff($leftovers, array(""));
                		$paths = array_merge($paths, $leftovers);
                	}
                }               
            }
            return $paths;
        }
    }
    
    private function extract_matched($uri, $key){
        // extract
        $value = null;
        preg_match("<".$key."/[".$this->def."]+>", "?".$uri, $v);
        if (is_array($v)){
	        $extracted = explode($this->slash, current($v));
	        $value = trim(next($extracted));
        }
        // rebuild the uri
       // $updated = str_replace($this->slash.$key.$this->slash.$value, "", $uri);
       $updated = substr($uri, @strpos($uri, $this->slash.$key.$this->slash.$value) + strlen($this->slash.$key.$this->slash.$value));
        $res = array("value" => $value, "uri" => $updated);      
        return $res;
    }
    
    private function extract_nomatched($uri, $key){
    	// check for matched item
        preg_match("<[".$this->def."]+>", "?".$uri, $v);
	$value = null;
	if (is_array($v)){       
        	$value = trim(current($v));
        } 
        // rebuild the uri
       	//$updated = str_replace($value, "", $uri);
       	$updated = substr($uri, @strpos($uri, $value) + strlen($value));
        $res = array("value" => $value, "uri" => $updated);
        return $res;
    }
        
    private function get_params($value){
        // check for parameters
        preg_match_all("<[\?\&][".$this->def."]+>", "?".$value, $extracted);
	$params = array();
	if (is_array($extracted)){      
	        foreach (current($extracted) as $i => $v){
			$ext = explode($this->equal, $v);
			$params[substr(current($ext), 1)] = trim(next($ext));
	        }
        }
        return $params;
    }

    private function check_parsed(&$parsed){
    	if (empty($parsed["path"])){
    		$parsed["path"] = $this->slash;
    	}
    }
}

?>