<?php

$FILES_ACCESS = true;
$AUTH_MODULE = 'auth.php';
$AUTH_PASS = ''; // Empty string or hash of password
// See getHash() function in auth.php

$SERVER_CHARSET = 'utf-8';
date_default_timezone_set('Europe/Moscow');
$DB_DIR = 'snapshots';
$JS_PATH = '/js/';

//$DIRS = getWritableDirectories('/home/user');
$DIRS = array(
	'example.com' => '/home/user/example.com',
	'sub.example.com' => '/home/user/sub.example.com',
	'example.net' => '/home/user/example.net'
);


function getWritableDirectories($dir) {
	$out = array();
	$handle = opendir($dir);
	if(!$handle)
		return $out;
	while(($entry = readdir($handle)) !== false) {
		if($entry === '.' || $entry === '..')
			continue;
		$fullPath = $dir . '/' . $entry;
		if(is_dir($fullPath) && is_writeable($fullPath)) {
			//if($entry[0] !== '.' && substr_count($entry, '.') == 1)
			//	$entry = 'www.' . $entry;
			$out[$entry] = $fullPath;
		}
	}
	closedir($handle);
	return $out;
}

?>