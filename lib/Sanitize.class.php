<?php

class Sanitize {

	public static function removeHTML($text) {
		return strip_tags($text);
	}

	public static function escapeHTML($text) {
		return htmlspecialchars($text, ENT_QUOTES);
	}
}
