<?php
class Encrypt {
	
	// bigger the cost slower this method becomes
	// how to validate the hash: crypt($str, $hash) === $storedHash
	public static function createHash($str) {
		// create salt
		$cost = 10;
		$encrypted = mcrypt_create_iv(16, MCRYPT_DEV_URANDOM);
		$encoded = base64_encode($encrypted);
		$translated = strtr($encoded, '+', '.');
		// prefix salt for PHP to validate later with crypt function
		// $2a$ means we are using Blowfish algorithm
		$salt = sprintf('$2a$%02d$', $cost) . $translated;
		$hash = crypt($str, $salt);
		return $hash;
	}

	public static function validateHash($str, $strHash) {
		// hash $str with its has as the salt returns the same hash
		return crypt($str, $strHash) === $strHash;
	}

	// requires mod_unique_id in Apache
	public static function uid() {
		$serverUid = isset($_SERVER['UNIQUE_ID']) ? $_SERVER['UNIQUE_ID'] : null;
		$ip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : null;
		$phpUid = uniqid(mt_rand(0, 300), true);
		$source = $serverUid . $ip . $phpUid;
		$uidSource = hash('sha256', $source);
		return base64_encode($uidSource);
	}
}
