<?php

class CmsData {
	
	private static $conf;

	/***
	* Configuration Name: CmsData
	* "CmsData": {
		"db": "dbNameInSqlConfig"
	} 
	***/

	public static function setConf($conf) {
		self::$conf = $conf;
	}

	public static function getDbName() {
		if (isset(self::$conf['db'])) {
			return self::$conf['db'];
		}
		return null;
	}
}

CmsData::setConf(Config::get('CmsData'));
