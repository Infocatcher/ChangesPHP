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
	if($realFile === false)
		$realFile = $file; // Will show "not found" message
	foreach($DIRS as $path) {
		$path = realpath($path);
		if(substr($realFile, 0, strlen($path) + 1) === $path . DIRECTORY_SEPARATOR) {
			$found = true;
			break;
		}
	}
	if(!$found)
		exit();

	$styles = $web = '';
	require('mapping.php');
	$webUrl = getWebUrl($file, $webUrlHtml);
	if($webUrl) {
		$styles = <<<CSS

	#openWeb-panel {
		position: fixed; top: 0; right: 0;
		background: white;
		background: rgba(255, 255, 255, 0.9);
		padding: 1px 0.2em;
	}
	#openWeb { opacity: 0.35; filter: alpha(opacity=35); }
	#openWeb:hover { opacity: 1; filter: alpha(opacity=100); }
	#content { margin-top: 1.65em; }
CSS;
		$web = <<<HTML
<span id="openWeb-panel"><a id="openWeb" href="{$webUrl}">{$webUrlHtml}</a></span>

HTML;
	}

	header('X-Frame-Options: DENY');
	$title = htmlspecialchars($file);
	if(is_file($file)) {
		header('Last-Modified: ' . gmdate("D, d M Y H:i:s \G\M\T", filemtime($file)));
		$content = htmlspecialchars(file_get_contents($file));
		if($content === '')
			$content = '&lt;<em>empty</em>&gt;';
	}
	else {
		$content = "<strong>File not found:</strong>\n" . htmlspecialchars($file);
	}
	$content = <<<HTML
<!DOCTYPE HTML>
<meta charset="{$SERVER_CHARSET}" />
<title>{$title}</title>
<script type="text/javascript">if(top != self) top.location.replace(location);</script>
<style type="text/css">
	html, body {
		color: black; background: white;
		font: 13px Verdana,Arial,Helvetica,sans-serif;
		margin: 0; padding: 0;
	}
	#content { font: 13px "Courier New", monospace; margin: 0.4em 0.6em; }{$styles}
	:link    { text-decoration: underline; color: #00e; }
	:visited { text-decoration: underline; color: #551a8b }
</style>
{$web}<pre id="content">{$content}</pre>
HTML;

	header('Content-Length: ' . strlen($content));
	echo $content;
}

?>