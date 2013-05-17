<?php
// CMS specific authentication handler

class CmsAuthHandler {

	private static $sess = null;

	public static function check($view, $cnt) {
		// asset map
		$view->assign('logos', Asset::map('normal', 'logos'));	
		$view->assign('assets', Asset::map('normal', 'system'));	
		$view->assign('spinners', Asset::map('normal', 'preloaders'));	
		// check authenticated session
		$sess = $cnt->getSession();
		if ($sess && isset($sess['id']) && isset($sess['user']) && isset($sess['lastLogin'])) {
			// language list
			$st = new StaticData('system/cms/languages.csv');
			$view->assign('languages', $st->getMany());
			// session data
			self::$sess = $sess;
			$view->assign('cmsUser', $sess);
			return $sess;
		}
		return false;
	}

	public static function get() {
		return self::$sess;
	}

}

?>
