<?php

// (c) Infocatcher 2012-2014
// version 0.1.0a6 - 2014-07-26
// https://github.com/Infocatcher/ChangesPHP

define('DIRS_PARSER_WORK', 0.2);
define('DIRS_PARSER_PAUSE', 1);

if(!defined('__DIR__')) // PHP < 5.3
	define('__DIR__', dirname(__FILE__));

function dbFile($dir, $time = '') {
	global $DB_DIR;
	if(!is_numeric($time))
		$time = '';
	$name = '@' . dechex(crc32($dir)) . ($time ? '-' . $time : '') . '.gz';
	$max = 255 - strlen(__DIR__ . '/' . $DB_DIR . '/' . $name);
	$dir = pathToFilename(substr($dir, 0, $max));
	return $DB_DIR . DIRECTORY_SEPARATOR . $dir . $name;
}
function dbFilePrefix($dir) {
	global $DB_DIR;
	$prefix = dbFile($dir);
	$prefix = substr($prefix, strlen($DB_DIR) + 1, -strlen('.gz'));
	return $prefix;
}
function pathToFilename($path) {
	return str_replace(array('/', '\\'), '!', $path);
}

function compareSnapshots($newTime, $oldTime) {
	global $DIR;
	$newEntries = $newTime == '***current***'
		? getEntries($DIR)
		: getSavedEntries($newTime);
	$oldEntries = &getSavedEntries($oldTime);
	if($newEntries && $oldEntries)
		compareEntries($newEntries, $oldEntries);
}

function compareEntries(&$newEntries, &$oldEntries) {
	// Note: isset() faster than array_key_exists():
	// http://www.php.net/manual/en/function.array-key-exists.php#82867
	$i = 0;
	foreach($newEntries as $path => $checksum) {
		//if(!array_key_exists($path, $oldEntries)) {
		if(!isset($oldEntries[$path])) {
			$info = parseEntryInfo($checksum);
			printRow(++$i, 'added', '+', $path, $info['dir'], $info['date'], 'n/a', $info['size'], 'n/a');
		}
		elseif($checksum !== $oldEntries[$path]) {
			$infoOld = parseEntryInfo($oldEntries[$path], true);
			$infoNew = parseEntryInfo($checksum, true);
			$dd = _dt($infoNew['date'] - $infoOld['date']);
			$ds = _dn($infoNew['size'], $infoOld['size']);
			$d = date('Y-m-d H:i:s', $infoNew['date']);
			$s = _n($infoNew['size']);
			printRow(++$i, 'changed', '*', $path, $infoNew['dir'], $d, $dd, $s, $ds);
		}
	}
	foreach($oldEntries as $path => $checksum) {
		//if(!array_key_exists($path, $newEntries)) {
		if(!isset($newEntries[$path])) {
			$info = parseEntryInfo($checksum);
			printRow(++$i, 'removed', '&#8722;', $path, $info['dir'], $info['date'], 'n/a', $info['size'], 'n/a');
			continue;
		}
	}
}
function printRow($n, $class, $mark, $path, $dirInfo, $date, $dateDiff, $size, $sizeDiff) {
	global $FILES_ACCESS;
	$htPath = htmlspecialchars($path);
	if(!$dirInfo)
		$htPath = highlightImportantFiles($htPath, $class);

	global $DIR;
	$fullPath = $DIR . DIRECTORY_SEPARATOR . $path;
	$url = $dirInfo || !$FILES_ACCESS
		? getWebUrl($fullPath)
		: 'view.php?file=' . rawurlencode($fullPath);
	if(isset($url)) {
		if($dirInfo) {
			$url .= '/';
			if($dirInfo[0] === DIRECTORY_SEPARATOR) {
				$htPath .= DIRECTORY_SEPARATOR;
				$dirInfo = substr($dirInfo, 1);
			}
		}
		$htPath = '<a class="plain-link" href="' . $url . '" target="_blank">' . $htPath . '</a>';
	}

	echo "<tr class=\"{$class}\">
	<td class=\"cell-number\">{$n}</td>
	<td class=\"cell-type\">{$mark}</td>
	<td class=\"cell-path\">{$htPath}{$dirInfo}</td>
	<td class=\"cell-date time\">{$date}</td>
	<td class=\"cell-date-diff\">{$dateDiff}</td>
	<td class=\"cell-size\">{$size}</td>
	<td class=\"cell-size-diff\">{$sizeDiff}</td>
</tr>
";
}
function highlightImportantFiles($path, &$class) {
	if(!preg_match('/\.(?:htaccess|html?|php)$/i', $path, $matches))
		return $path;
	$ext = $matches[0];
	$class .= ' important';
	return substr($path, 0, -strlen($ext)) . '<span class="importantExt">' . $ext . '</span>';
}

function printTableHeader() {
	global $AUTOSCROLL;
	$autoScrollChecked = $AUTOSCROLL ? ' checked' : '';
	echo <<<HTML
<div class="wait">
Please wait&hellip;
<label id="autoScrollLabel" for="autoScrollCheckbox">
	<input id="autoScrollCheckbox" type="checkbox"{$autoScrollChecked}
		onclick="autoScroll(this.checked);" />Autoscroll
</label>
</div>
<script type="text/javascript">
if(document.getElementById("autoScrollCheckbox").checked)
	autoScroll(true);
else {
	var scrollToResultsDelay = 25;
	var scrollToResultsStop = new Date().getTime() + 1500;
	setTimeout(function scrollToResults() {
		var res = document.getElementById("compare");
		res.scrollIntoView();
		//console.log(document.documentElement.scrollTop + " < " + res.offsetTop);
		if(
			new Date().getTime() < scrollToResultsStop
			&& document.documentElement.scrollTop < res.offsetTop
		)
			setTimeout(scrollToResults, scrollToResultsDelay);
	}, scrollToResultsDelay);
}
</script>
<table id="compare">
<thead><tr>
	<th id="th-number">&nbsp;</th>
	<th id="th-type" title="* = changed &#10;+ = added &#10;&#8722; = removed">&nbsp;</th>
	<th id="th-path">Path</th>
	<th id="th-date">Date</th>
	<th id="th-date-diff">Date diff</th>
	<th id="th-size">Size</th>
	<th id="th-size-diff">Size diff</th>
</tr></thead>

HTML;
}
function printTableFooter() {
	global $start;
	echo '
</table>
' . getStats(microtime(true) - $start, memory_get_peak_usage(true)) . '
</div>
<style type="text/css">.wait { display: none; }</style>
<script type="text/javascript" src="sortRows.js"></script>
<script type="text/javascript">
	autoScroll(false);
	new RowsSorter("compare").sortRows(0, false, true);
</script>
';
}
function getStats($time, $memory, $desc = '') {
	return '<div id="state">
'
. $desc
. _n($time, 3) . ' s | '
. _n($memory/1024/1024, 2) . ' MiB
</div>';
}

function &getEntries($dir, &$_strip = 0, &$_pauseTime = 0, &$_out = array()) {
	if(!is_writeable($dir)) {
		echo "*** \"$dir\" isn't writable ***<br>\n";
		return $_out;
	}
	$now = microtime(true);
	if(func_num_args() == 1) { // First external call
		$_strip = strlen($dir) + 1;
		$_pauseTime = $now + DIRS_PARSER_WORK;
	}
	elseif($now >= $_pauseTime) {
		usleep(DIRS_PARSER_PAUSE);
		$_pauseTime = $now + DIRS_PARSER_WORK + DIRS_PARSER_PAUSE;
	}

	// Less memory usage:
	$handle = opendir($dir);
	if(!$handle) {
		echo "*** opendir(\"$dir\") failed ***<br>\n";
		return $_out;
	}
	while(($entry = readdir($handle)) !== false) {
		if($entry === '.' || $entry === '..')
			continue;
		$entry = $dir . DIRECTORY_SEPARATOR . $entry;
		$key = substr($entry, $_strip);
		$_out[$key] = filemtime($entry) . '-' . filesize($entry);
		if(is_dir($entry)) {
			$_out[$key] .= '-d';
			getEntries($entry, $_strip, $_pauseTime, $_out);
		}
	}
	closedir($handle);

	// Faster:
	/*
	$entries = scandir($dir);
	if($entries === false) {
		echo "*** scandir(\"$dir\") failed ***<br>\n";
		return $_out;
	}
	foreach($entries as $entry) {
		if($entry === '.' || $entry === '..')
			continue;
		$entry = $dir . DIRECTORY_SEPARATOR . $entry;
		$key = substr($entry, $_strip);
		$_out[$key] = filemtime($entry) . '-' . filesize($entry);
		if(is_dir($entry)) {
			$_out[$key] .= '-d';
			getEntries($entry, $_strip, $_pauseTime, $_out);
		}
	}
	*/

	return $_out;
}

function &getSavedEntries($time) {
	global $DIR;
	$dbFile = dbFile($DIR, $time);
	if(!file_exists($dbFile)) {
		echo "*** No saved data for \"{$DIR}\" at {$time} ***<br>\n";
		return array();
	}
	return readData($dbFile);
}

function parseEntryInfo($checksum, $raw = false) {
	$data = explode('-', $checksum);
	$info = array(
		'date' => $raw ? (int) $data[0] : date('Y-m-d H:i:s', $data[0]),
		'size' => $raw ? (int) $data[1] : _n($data[1])
	);
	$info['dir'] = isset($data[2]) && $data[2] === 'd'
		? DIRECTORY_SEPARATOR . '&nbsp;&lt;dir&gt;'
		: '';
	return $info;
}
function _n($n, $decimals = 0) {
	return number_format($n, $decimals, ',', ' ');
}
function _dn($n1, $n2, $decimals = 0) {
	if($n1 === $n2)
		return '=';
	$d = $n1 - $n2;
	return ($d >= 0 ? '+' : '') . _n($d, $decimals);
}
function _dt($time) {
	if($time === 0)
		return '=';
	if($time < 0) {
		$time = -$time;
		$sign = '-';
	}
	else {
		$sign = '+';
	}
	//~ todo: $secs = $time % 60 ?
	$days = floor($time/24/3600);
	$time -= $days*24*3600;
	$hours = floor($time/3600);
	$time -= $hours*3600;
	$mins = floor($time/60);
	$time -= $mins*60;
	$secs = round($time);

	return $sign . ($days ? $days . 'd ' : '')
		. str_pad($hours, 2, '0', STR_PAD_LEFT) . ':'
		. str_pad($mins,  2, '0', STR_PAD_LEFT) . ':'
		. str_pad($secs,  2, '0', STR_PAD_LEFT);
}

function saveSnapshot() {
	global $DIR, $DB_DIR;
	if(!file_exists($DB_DIR)) {
		mkdir($DB_DIR, 0755);
		protectDir($DB_DIR);
	}
	$currentEntries = getEntries($DIR);
	$dbFile = dbFile($DIR, date('YmdHis'));
	saveData($currentEntries, $dbFile);
}
function protectDir($dir) {
	global $SERVER_CHARSET;
	file_put_contents($dir . DIRECTORY_SEPARATOR . '.htaccess', 'deny from all');
	file_put_contents(
		$dir . DIRECTORY_SEPARATOR . 'index.html',
		'<!DOCTYPE HTML><meta charset="' . $SERVER_CHARSET . '"><title>Nothing here</title>'
	);
}

// Tries to don't allocate nested memory...
function saveData(&$arr, &$file) {
	file_put_contents($file, gzencode(json_encode($arr), 9, FORCE_GZIP));
}
function &readData($file) {
	$arr = json_decode(gzinflate(
		substr(file_get_contents($file), 10, -8)
	), true);
	return $arr;
}

?>