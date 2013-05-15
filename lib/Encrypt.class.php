<?php
class Encrypt {
	
	public static function createHashWithSalt($srcStr, $uniqueId, $randRange = 300) {
		// create salt
		$salt = strrev(hash('sha512', mt_rand(0, $randRange) . $uniqueId));
		// create hash with salt
		$hash = self::getHashWithSalt($srcStr, $salt);
		return array('hash' => $hash, 'salt' => $salt);
	}
	
	// this method returns the same format hash as createHashWithSalt
	public static function getHashWithSalt($srcStr, $salt) {
		$encrypted = hash('sha512', $srcStr);
		$reversed = strrev($encrypted);
		return substr($salt, 0, strlen($salt) / 2) . substr($reversed, 0, strlen($reversed) / 2) . substr($salt, strlen($salt) / 2, strlen($salt)) . substr($reversed, strlen($reversed) / 2, strlen($reversed));
	}
}
?>
