<?php

function isAllowedPath($file) {
	global $DIRS;
	$realFile = realpath($file);
	if($realFile === false)
		$realFile = $file; // File not found, fallback to show "not found" message
	foreach($DIRS as $path) {
		$path = realpath($path);
		if($path !== false && substr($realFile, 0, strlen($path) + 1) === $path . DIRECTORY_SEPARATOR)
			return true;
	}
	return false;
}

function getWebUrl($file, &$webUrlHtml = null) {
	global $MAPPINGS;
	$realFile = realpath($file);
	if($realFile === false)
		$realFile = $file;
	foreach($MAPPINGS as $path => $url) {
		$path = realpath($path);
		$pathLen = strlen($path);
		if(substr($realFile, 0, $pathLen) === $path) {
			$pathEnd = substr($realFile, $pathLen);
			if(DIRECTORY_SEPARATOR !== '/')
				$pathEnd = str_replace(DIRECTORY_SEPARATOR, '/', $pathEnd);
			$webUrlEnc = $url . str_replace('%2F', '/', rawurlencode($pathEnd));
			$webUrlHtml = htmlspecialchars($url . $pathEnd);
			return $webUrlEnc;
		}
	}
	return null;
}

?>