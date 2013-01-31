<?php

function getWebUrl($realFile, &$webUrlHtml = null) {
	global $MAPPINGS;
	$realFile = realpath($realFile);
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