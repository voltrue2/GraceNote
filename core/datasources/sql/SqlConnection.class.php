<?php

class SqlConnection {
	
	private static $cnList = array();

	public static function getConnection($conf) {
		$cn = null;
		$confStr = json_encode($conf);
		if (isset(self::$cnList[$confStr])) {
			return self::$cnList[$confStr];
		}
		Log::debug('[SQLCONNECTION] getConnection > connection created > '. $conf['type'] . ' ' . $conf['behavior']);
		try {
			$cn = new PDO($conf['type'].":host=" . $conf['host'].";dbname=" . $conf['db'], $conf['user'], $conf['password'], array(PDO::ATTR_PERSISTENT =>  true));
			self::$cnList[$confStr] = $cn;
		} catch (Exception $e) {
			Log::error($e->getMessage());
			return null;
		}
		return $cn;
	}

}

?>
