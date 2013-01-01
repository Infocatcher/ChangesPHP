<?php

error_reporting(E_ALL | E_STRICT);

session_start();
require('config.php');

if(
	$FILES_ACCESS
	&& isset($_GET['file'])
	&& isset($_SESSION['changes:auth'])
	&& isset($_SESSION['changes:view'])
) {
	$file = $_GET['file'];
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
	echo <<<HTML
<!DOCTYPE HTML>
<meta charset="{$SERVER_CHARSET}" />
<title>{$title}</title>
<script type="text/javascript">if(top != self) top.location.replace(location);</script>
<pre>{$content}</pre>
HTML;
}

?>