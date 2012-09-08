<?php
class Mailer {
	
	private $subject = "";
	private $to = "";
	private $to_name = "";
	private $from = "";
	private $from_name = "";
	private $body = "";
	private $header = "";
	private $name = "";
	private $key = 0;
	private $lock = 6;
	private $encoding = "ISO-2022-JP";
	private $content_type = "text/html";	
	private $mobile_content_type = "text/plain";	
	
	public function Mailer($name_in = ""){
		$this->name = $name_in;
	}
	
	public function HTMLcontent(){
		$this->content_type = "text/html";
	}
	
	public function textContent(){
		$this->content_type = "text/plain";
	}

	public function ConvertToSJISEncoding(){
		$this->encoding = "SJIS";
	}
	
	public function setEncoding($val){
		$this->encoding = $val;
	}

	public function setSubject($val = null){
		$this->set($this->subject, $val);
	}
	
	public function setTo($name, $val = null){
		$this->set($this->to, $val);
		$this->set($this->to_name, $name);
	}
	
	public function setFrom($name, $val = null){
		$this->set($this->from, $val);
		$this->set($this->from_name, $name);
	}
	
	public function setBody($val){
		if($this->content_type == "text/plain")
		{
			$val = strip_tags($val);
		}
		
		$this->set($this->body, $val);
	}

	public function send(){
		mb_language("japanese");
        $orginal_encode = mb_internal_encoding();
        // in case of mobile
        if (DEVICE == 'mobile') {
            $this->encoding = 'SJIS-win';
            $convert_kana = 'ka';
            $this->create_mobile_header();

            if($this->body[0] == chr(0xef)
               && $this->body[1] == chr(0xbb)
               && $this->body[2] == chr(0xbf)) {
                $this->body = substr($this->body, 3);
            }
        } else {
            // in case of PC
            $this->create_header();
            $convert_kana = 'KV';
            $this->body = mb_convert_kana($this->body, $convert_kana, "UTF-8");
        }
        mb_internal_encoding($this->encoding);
        $encoded_subject = mb_encode_mimeheader(mb_convert_encoding($this->subject, "ISO-2022-JP", "UTF-8"),"ISO-2022-JP","B","\n\n");
        $this->body = mb_convert_encoding($this->body, $this->encoding, "UTF-8");
        
        $res = mail($this->to, $encoded_subject, $this->body, $this->header);
               
        mb_internal_encoding($orginal_encode);
		return $res;
	}
	
	public function send_w_attachment($attach_path){
		mb_language("japanese");
		$this->create_att_header($attach_path);
                $orginal_encode = mb_internal_encoding();
                mb_internal_encoding($this->encoding);
                $encoded_subject = mb_encode_mimeheader(mb_convert_encoding($this->subject, "ISO-2022-JP", "UTF-8"),"ISO-2022-JP","B","\n");
                mb_internal_encoding($orginal_encode);
                $res = mail($this->to, $encoded_subject, "", $this->header);
		return $res;
	}
	
	private function create_att_header($file){
		// prepare the body text
		$orginal_encode = mb_internal_encoding();
                mb_internal_encoding($this->encoding);
		$b = mb_convert_encoding(mb_convert_kana($this->body, 'KV', "UTF-8"), $this->encoding, 'UTF-8')."\n\n";
		mb_internal_encoding($orginal_encode);
		// prepare the attachment
		$content = chunk_split(base64_encode(file_get_contents($file)));
		$uid = md5(time());
		$a = strrpos($file, ".");
		$filetype = pathinfo($file, PATHINFO_EXTENSION);
		$filename = basename($file);
		// create the header
		$this->header = "From: ". mb_encode_mimeheader(mb_convert_encoding($this->from_name, "ISO-2022-JP", 'UTF-8'), "ISO-2022-JP","B","\n")." <".$this->from.">\n";
		$this->header .= "Bcc: bcc@qpon.jp\n";;
		$this->header .= "Reply-To: ".$this->from."\n";
		$this->header .= "Return-Path: ".$this->from."\n";
		$this->header .= "X-Mailer: ".$this->name."\n";
		$this->header .= "MIME-Version: 1.0\n";
        $this->header .= "Content-Type: multipart/mixed; boundary=\"".$uid."\"\n\n";

		$this->header .= "--".$uid."\n";
		$this->header .= 'Content-type: '.$this->content_type.'; charset='.$this->encoding."\n";
		$this->header .= "Content-Transfer-Encoding: 7bit\n\n";
		// ****** body encoding
		$this->header .= $b;
		// ********************
		$this->header .= "--".$uid."\n";
		$this->header .= "Content-Type: application/".$filetype." name=\"".$filename."\"\n";
		$this->header .= "Content-Transfer-Encoding: base64\n";
		$this->header .= "Content-Disposition: attachment; filename=\"".$filename."\"\n\n";
		$this->header .= $content."\n\n";
		$this->header .= "--".$uid."--";
	}
	
	private function create_header(){
		$this->header .= "From: ". mb_encode_mimeheader(mb_convert_encoding($this->from_name, "ISO-2022-JP", 'UTF-8'), "ISO-2022-JP","B","\n")." <".$this->from.">\n";
		$this->header .= "Bcc: bcc@qpon.jp\n";
		$this->header .= "Reply-To: ".$this->from."\n";
		$this->header .= "Return-Path: ".$this->from."\n";
		$this->header .= "X-Mailer: ".$this->name."\n";
		$this->header .= 'MIME-Version: 1.0' . "\n";
		//$this->header .= 'Content-type: text/html; charset='.$this->encoding . "\n";
		$this->header .= 'Content-type: '.$this->content_type.'; charset='.$this->encoding."\n";
	}
	
	private function create_mobile_header(){
		$this->header .= "From: ". mb_encode_mimeheader(mb_convert_encoding($this->from_name, "ISO-2022-JP", 'UTF-8'), "ISO-2022-JP","B","\n")." <".$this->from.">\n";
		$this->header .= "Bcc: bcc@qpon.jp\n";
		$this->header .= "Reply-To: ".$this->from."\n";
		$this->header .= "Return-Path: ".$this->from."\n";
		$this->header .= "X-Mailer: ".$this->name."\n";
        $this->header .= 'MIME-Version: 1.0' . "\n";
        $this->header .= 'Content-type: '.$this->mobile_content_type."; charset=SJIS-win\n";
        
        return true;
	}
	
	private function set(&$set_val, $val){
		if ($val){
			$set_val .= $val;
			$this->unlock();
		}
	}
	
	private function unlock(){
		$this->key++;
	}
	
	private function check_lock(){
		if ($this->key >= $this->lock){
			return true;
		}
		else {
			return false;
		}
	}
}
?>
