<?php

error_reporting(E_ALL | E_STRICT);

session_start();

require('ui.php');
if(
	isset($_POST['compare'])
	|| isset($_POST['save'])
	|| isset($_POST['delete'])
	|| isset($_POST['logout'])
)
	checkToken();
if(isset($_POST['logout'])) {
	unset(
		$_SESSION['changes:auth'],
		$_SESSION['changes:view'],
		$_SESSION['changes:csrfToken']
	);
}

require('config.php');
require($AUTH_MODULE);
require('mapping.php');
require('changes.php');

header('X-Frame-Options: DENY');

$project = null;
if(isset($_GET['project']))
	$project = $_GET['project'];
elseif(isset($_POST['project']))
	$project = $_POST['project'];

if(isset($project) && isset($DIRS[$project])) {
	$DIR = $DIRS[$project];
	$DIR_NAME = $project;
}
else if(!isset($DIR) && !isset($DIR_NAME)) { // Or use defaults from config.php
	$DIR = reset($DIRS);
	$DIR_NAME = key($DIRS);
}

?>
<!DOCTYPE HTML>
<html>
<head>
	<title>Changes [<?php echo htmlspecialchars($DIR_NAME, ENT_NOQUOTES); ?>]</title>
	<meta charset="<?php echo $SERVER_CHARSET; ?>" />
	<script type="text/javascript">
		if(top != self)
			top.location.replace(location);
		if(!/[?&]project=/.test(location.search) && window.history && history.replaceState) {
			history.replaceState(
				"",
				document.title,
				location.href.replace(/[?#].*$/, "")
					+ "?project=" + encodeURIComponent("<?php echo $DIR_NAME; ?>")
			);
		}
	</script>
	<!--<link rel="stylesheet" type="text/css" href="styles.css" />-->
	<style type="text/css">
<?php
	// Server may go to 503 error after compare with current state, so it's better to apply styles anyway
	readfile('styles.css');
?>

	</style>
	<script type="text/javascript" src="<?php echo $JS_PATH; ?>eventListener.js"></script>
	<script type="text/javascript">
	var scrollTimer;
	function autoScroll(on) {
		if(on && !scrollTimer)
			scrollTimer = setInterval(scrollToBottom, 60);
		else if(!on && scrollTimer) {
			clearInterval(scrollTimer);
			scrollTimer = 0;
			scrollToBottom();
		}
	}
	function scrollToBottom() {
		var root = document.documentElement;
		window.scrollBy(0, root.scrollHeight - root.scrollTop);
	}
	function updateButtons(e) {
		if(e) {
			for(var node = e.target; ; node = node.parentNode) {
				if(!node)
					return;
				if(
					node.nodeName.toLowerCase() == "input"
					&& (node.type == "radio" || node.type == "checkbox")
				)
					break;
			}
		}

		var btnCompare = document.forms.actions.compare;
		var btnDelete = document.forms.actions.delete;

		var oldTime = getRadioState("old");
		var newTime = getRadioState("new");
		btnCompare.disabled = !oldTime || !newTime
			|| oldTime == newTime
			|| newTime != "***current***" && oldTime > newTime;

		var checked = false;
		var cbs = document.getElementsByTagName("input");
		for(var i = 0, l = cbs.length; i < l; ++i) {
			var cb = cbs[i];
			if(cb.type == "checkbox" && /^delete-/.test(cb.name) && cb.checked) {
				checked = true;
				break;
			}
		}
		btnDelete.disabled = !checked;
	}
	function getRadioState(name) {
		var rs = document.getElementsByName(name);
		for(var i = 0, l = rs.length; i < l; ++i) {
			var r = rs[i];
			if(r.checked)
				return r.value;
		}
		return null;
	}
	eventListener.add(document, "click", updateButtons);
	eventListener.add(window, "unload", function() {
		eventListener.remove(window, "unload", arguments.callee);
		eventListener.remove(document, "click", updateButtons);
	});
	</script>
</head>
<body class="jsDisabled">
<script type="text/javascript">document.body.className = "jsEnabled";</script>
<style type="text/css">
.jsDisabled .forJsEnabled,
.jsEnabled .forJsDisabled {
	display: none;
}
</style>

<?php

$oldTime = $newTime = $saveTime = null;
if(isset($_POST['compare'])) {
	if(isset($_POST['old']) && isset($_POST['new'])) {
		$oldTime = $_POST['old'];
		$newTime = $_POST['new'];
	}
}
elseif(isset($_POST['save'])) {
	$saveTime = microtime(true);
	saveSnapshot();
	$saveTime = microtime(true) - $saveTime;
	$saveMemory = memory_get_peak_usage(true);
}
elseif(isset($_POST['delete'])) {
	foreach($_POST as $key => $val) {
		if(preg_match('/^delete-(\d+)$/', $key, $m)) {
			$file = dbFile($DIR, $m[1]);
			if(is_file($file))
				unlink($file);
		}
	}
}

echo '<form id="actions" action="./" method="POST">
<input name="token" type="hidden" value="'. getToken() .'">';
printProjectsList($DIR);
printSavedList($DIR);
echo '</form>';

if(isset($saveTime))
	echo "\n" . getStats($saveTime, $saveMemory, 'Successfully saved: ') . "\n";
if($oldTime && $newTime) {
	$start = microtime(true);
	printTableHeader();
	compareSnapshots($newTime, $oldTime);
	printTableFooter();
}

?>

<script type="text/javascript" src="<?php echo $JS_PATH; ?>elapsedTime.js"></script>
<script type="text/javascript">
	updateButtons();
	elapsedTime.init("<?php echo date('Y-m-d H:i:s P'); ?>");
</script>

</body>
</html>