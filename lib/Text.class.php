<?php

class Text {

	public static function get($view, $cnt, $name, $fileList = null) {
		$lang = $cnt->getQuery('lang');
		if ($lang !== null) {
			$cnt->addSession('lang', $lang);
		}
		$sess = $cnt->getSession();
		if ($sess && isset($sess['lang'])) {
			$lang = $sess['lang'];
		}
		// default
		if ($lang == '') {
			$lang = 0;
		}
		// TODO: dont hard code 'StaticData'
		if (!$fileList) {
			$fileList = 'text/cms/';
		}
		$dm = new DataModel('StaticData');
		$fd = $dm->staticData();
		$text = $fd->getOne($fileList, $lang);
		$view->assign('currentLang', $lang);
		$view->assign($name, $text);
		return $text;	
	}

}

?>
