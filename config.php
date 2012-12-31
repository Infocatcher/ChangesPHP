<?php

$SERVER_CHARSET = 'utf-8';
date_default_timezone_set('Europe/Moscow');
$DB_DIR = 'snapshots';
$JS_PATH = '/js/';
$FILES_ACCESS = true;

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