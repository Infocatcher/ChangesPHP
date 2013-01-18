<?php

error_reporting(E_ALL | E_STRICT);

session_start();
require('config.php');
require($AUTH_MODULE);

if(
	$FILES_ACCESS
	&& isset($_GET['file'])
	&& isset($_SESSION['changes:auth'])
	&& isset($_SESSION['changes:view'])
) {
	$file = $_GET['file'];

	$found = false;
	$realFile = realpath($file);
	foreach($DIRS as $path) {
		$path = realpath($path);
		if(substr($realFile, 0, strlen($path) + 1) === $path . DIRECTORY_SEPARATOR) {
			$found = true;
			break;
		}
	}
	if(!$found)
		exit();

	header('X-Frame-Options: DENY');
	$title = htmlspecialchars($file);
	if(is_file($_GET['file'])) {
		$content = htmlspecialchars(file_get_contents($file));
		if($content === '')
			$content = '&lt;<em>empty</em>&gt;';
	}
	else {
		$content = "<strong>File not found:</strong>\n" . $file;
	}
	$content = <<<HTML
<!DOCTYPE HTML>
<meta charset="{$SERVER_CHARSET}" />
<title>{$title}</title>
<script type="text/javascript">if(top != self) top.location.replace(location);</script>
<pre>{$content}</pre>
HTML;

	header('Content-Length: ' . strlen($content));
	header('Last-Modified: ' . gmdate("D, d M Y H:i:s \G\M\T", filemtime($file)));
	echo $content;
}

?>