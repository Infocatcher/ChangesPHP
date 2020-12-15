<?php

if(!isset($AUTH_PASS) || isset($_REQUEST['AUTH_PASS']))
	exit();

if(
	$AUTH_PASS === ''
	|| isset($_POST['password']) && getHash($_POST['password']) === $AUTH_PASS
	|| isset($_SESSION['changes:auth'])
) {
	$_SESSION['changes:auth'] = true;
	if($FILES_ACCESS)
		$_SESSION['changes:view'] = true;
	else
		unset($_SESSION['changes:view']);
	session_regenerate_id();
}
else {
	$action = htmlspecialchars($_SERVER['REQUEST_URI']);
	header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
	header('X-Frame-Options: DENY');
	$hash = isset($_POST['password'])
		? '<div style="margin-top: 0.5em;"><strong>Wrong password!</strong></div>'
			. '<div style="margin-top: 0.5em; font: 0.7em monospace; opacity: 0.5;">Hash: '
			. getHash($_POST['password'])
			. '<br />(config.php &#8658; $AUTH_PASS)</div>'
		: '';
	exit(
<<<HTML
<!DOCTYPE HTML>
<title>Changes: Login to access&hellip;</title>
<meta charset="{$SERVER_CHARSET}" />
<script type="text/javascript">if(top != self) top.location.replace(location);</script>
<form name="enter" action="{$action}" method="post">
	<input name="password" type="password" />
	<input type="submit" value="Let's go!" />{$hash}
	<script type="text/javascript">
		if(location.hash)
			document.forms.enter.action += location.hash;
		document.forms.enter.password.focus();
	</script>
</form>
HTML
	);
}

function getHash($pass) {
	global $AUTH_SALT, $AUTH_SALT2;
	return hash('sha256', $AUTH_SALT . $pass . $AUTH_SALT2);
}

?>