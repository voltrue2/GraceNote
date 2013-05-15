<?php

/***
* Required sql database table
* CREATE TABLE report (
	user VARCHAR,
	type VARCHAR,
	value VARCHAR,
	created VARCHAR
);
*
* Reports statistical values and stores them in database 
**/

class Report {
	
	/***
	* Configurations 
	*
		"Report": {
			"db": "dbNameInSqlConfig"
		}
	*/

	static private $types = array();
	static private $conf;

	// never call this method outside of this file
	static public function setConfig($conf) {
		if (!$conf) {
			return Log::error('Report::setConfig > missing configurations');
		}
		self::$conf = $conf;
	}

	static public function createType($type) {
		self::$types[$type] = true;
	}

	static public function getTypes() {
		return self::$types;
	}

	// $type needs to be created by createType method first
	static public function send($type, $value, $user = '') {
		if (!isset(self::$types[$type])) {
			return Log::warn('Report::send > invalid type given (' . $type . ')', self::$types);
		}
		if (!isset(self::$conf) || !isset(self::$conf['db'])) {
			// missing config
			return;
		}
		if (is_array($value)) {
			$value = json_encode($value);
		}
		$created = microtime(true);
		$dm = new DataModel(self::$conf['db']);
		$table = $dm->table('report');
		$table->set('user', $user);
		$table->set('type', $type);
		$table->set('value', $value);
		$table->set('created', $created);
		$table->save();
	}

	static public function getByType($type, $sortOn = null) {
		if (!isset(self::$conf) || !isset($conf['dv'])) {
			// missing config
			return Log::error('Report::getByType > missing configurations');
		}
		$dm = new DataModel(self::$conf['db']);
		$table = $dm->table('report');
		$table->where('type = ?', $type);
		if ($sortOn) {
			$table->order($sortOn, 'DESC');
		}
		return $table->getMany();
	}
}

Report::setConfig(Config::get('Report'));

?>
