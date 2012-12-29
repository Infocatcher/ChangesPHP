<?php

error_reporting(E_ALL | E_STRICT);

require('config.php');
require('changes.php');
require('ui.php');

?>
<!DOCTYPE HTML>
<html>
<head>
	<title>Changes</title>
	<meta charset="<?php echo $SERVER_CHARSET; ?>" />
	<!--<link rel="stylesheet" type="text/css" href="styles.css" />-->
	<style type="text/css">
<?php readfile('styles.css'); ?>

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

if(isset($_POST['dir']) && is_dir($_POST['dir']))
	$DIR = $_POST['dir'];
else {
	foreach($DIRS as $name => $_dir) {
		$DIR = $_dir;
		break;
	}
}
$oldTime = null;
$newTime = null;
if(isset($_POST['compare'])) {
	if(isset($_POST['old']) && isset($_POST['new'])) {
		$oldTime = $_POST['old'];
		$newTime = $_POST['new'];
	}
}
elseif(isset($_POST['save'])) {
	saveSnapshot();
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

echo '<form id="actions" action="./" method="POST">';
printProjectsList($DIR);
printSavedList($DIR);
echo '</form>';

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