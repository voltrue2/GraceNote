<?php 

Load::model('SelectModel');

class Header {

	private $c;

	public function Header($view){
		$this->c = $view;
		$this->c->contents('header', 'HEADER');
		$read = new SelectModel();
		$langs = $read->table('languages');
		$langs->order('lang_id', 'DESC');
		$this->c->assign('LANGS', $langs->find_all());
		$ext_content = new ContentModel('extended_cms', $this->c->lang());
		$this->c->assign('EXTENDED_MENU', $this->check_permission($ext_content->get_all(false, true)));
		$full_uri = $this->c->get('FULL_URI');
		$q = $this->c->get('QUERIES');
		$lang_key = $this->c->get('LANG_QUERY_NAME');
		if (isset($q[$lang_key])){
			$full_uri = str_replace('?'.$lang_key.'='.$q[$lang_key], '', $full_uri);
			$full_uri = str_replace('&'.$lang_key.'='.$q[$lang_key], '', $full_uri);
			$full_uri = str_replace('/&', '/?', $full_uri);
		}
		if (strpos($full_uri, '?') !== false){
			$glue = '&';
		}
		else {
			$glue = '?';
		}
		$this->c->assign('LANG_PATH', $full_uri.$glue);
	}
	
	private function check_permission($ext_menu){
		$user = $this->c->get_session(session_id());
		if (isset($user['permission'])){
			$model = new SelectModel();
			$p = $model->table('permissions');
			$p->cond('permission = '.$p->escape($user['permission']));
			$list = $p->find_all();
			if ($list){
				$tmp = $ext_menu;
				$ext_menu = false;
				foreach ($list as $item){
					foreach ($tmp as $value){
						if ($value['path'] == '/'.$item['table_name'].'/'){
							$ext_menu[] = $value;
							break;
						}
					}
				}
			}
			else {
				$ext_menu = false;
			}
			return $ext_menu;
		}
		else {
			return false;
		}
	}
}
?>