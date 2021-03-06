<?php

$FILES_ACCESS = true;
$AUTH_MODULE = 'auth.php';
$AUTH_SALT = '4bLEXig5dWow ';
$AUTH_SALT2 = '~GFP5OHh1F5aR';
$AUTH_PASS = '244afca6dd2d7300cdfeef4574f11c17da565058e40f1425e8a8e6e1e697e208'; // Hash of 'changes' string
// $AUTH_PASS: empty string or hash of password
// See getHash() function in auth.php

$SERVER_CHARSET = 'utf-8';
date_default_timezone_set('Europe/Moscow');
$DB_DIR = 'snapshots';
$JS_PATH = '/js/';
$AUTOSCROLL = true;

/*
$DIRS = getWritableDirectories('/home/user');
$MAPPINGS = array();
foreach($DIRS as $dir => $fullPath) {
	$MAPPINGS[$fullPath . '/www'] = 'http://' . $dir;
}
*/

$DIRS = array(
	'example.com'     => '/home/user/example.com',
	'sub.example.com' => '/home/user/sub.example.com',
	'example.net'     => '/home/user/example.net'
);
$MAPPINGS = array(
	'/home/user/example.com/www'     => 'http://example.com',
	'/home/user/sub.example.com/www' => 'http://sub.example.com',
	'/home/user/example.net/www'     => 'http://example.net'
);

// Set default
$DIR_NAME = 'sub.example.com';
$DIR = $DIRS[$DIR_NAME];

function getWritableDirectories($dir) {
	$out = array();
	$handle = opendir($dir);
	if(!$handle)
		return $out;
	while(($entry = readdir($handle)) !== false) {
		if($entry === '.' || $entry === '..')
			continue;
		$fullPath = $dir . DIRECTORY_SEPARATOR . $entry;
		if(looksLikeDomain($entry) && is_dir($fullPath) && is_writeable($fullPath)) {
			//if($entry[0] !== '.' && substr_count($entry, '.') == 1)
			//	$entry = 'www.' . $entry;
			$out[$entry] = $fullPath;
		}
	}
	closedir($handle);
	return $out;
}
function looksLikeDomain($dir) {
	return preg_match('/^[a-z-]+(\.[a-z-]+)+$/', $dir) === 1;
}

?>