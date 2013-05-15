<?php
$filePath = $argv[1];
$keys = $argv[2]; // key1.key2.key3 ....
$src = file_get_contents($filePath);
$parsed = json_decode($src, true);
// turn keys string into an array
$keys = explode('.', $keys);
$res = $parsed;
for ($i = 0; $i < count($keys); $i++) {
	$res = $res[$keys[$i]];
}

echo $res;
?>
