<?php
class SqlConfig {
	
	private static $conf = null;

	/*
	* "Sql": {
		"whateverUniqueNameYouWantToGive": {
			"read": {
				"type": "mysql" or :pgsql"
				"behavior": "read"
				"host": "yourDatabaseHostName"
				"port": portNumber
				"db": "dbName"
				"user": "user"
				"password" "password"
			},
			"write": {
				"type": "mysql" or :pgsql"
				"behavior": "write"
				"host": "yourDatabaseHostName"
				"port": portNumber
				"db": "dbName"
				"user": "user"
				"password" "password"
			}
		},
		add more if you like...
	}
	*/
	public static function setConfig($config) {
		self::$conf = $config;
	}

	public static function getAll() {
		return self::$conf;
	}

	public static function get($groupName) {
		if (isset(self::$conf[$groupName])) {
			$group = self::$conf[$groupName];
			if (isset($group['read']) && isset($group['write'])) {
				return $group;
			}
		}
		return null;
	}
}

// setup from config
$conf = Config::get('Sql');
if ($conf) {
	SqlConfig::setConfig($conf);
} else {
	exit('SqlConfig >> missing configurations...');
}
?>
