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
	session_regenerate_id();
}
else {
	$action = htmlspecialchars($_SERVER['REQUEST_URI']);
	header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
	header('X-Frame-Options: DENY');
	exit(
<<<HTML
<!DOCTYPE HTML>
<title>Changes: Login to access&hellip;</title>
<meta charset="{$SERVER_CHARSET}" />
<script type="text/javascript">if(top != self) top.location.replace(location);</script>
<form name="enter" action="{$action}" method="post">
	<input name="password" type="password">
	<input type="submit" value="Let's go!">
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
	return hash('sha256', '4bLEXig5dWow ' . $pass . '~GFP5OHh1F5aR');
}

?>