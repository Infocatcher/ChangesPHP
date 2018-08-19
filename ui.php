<?php

function printProjectsList($selectedDir) {
	global $DIRS;
	echo <<<HTML
	<select id="projects-list" name="project" onchange="document.getElementsByName('select')[0].click();">

HTML;
	foreach($DIRS as $name => $dir) {
		$selected = $dir === $selectedDir ? ' selected="selected"' : '';
		$name = htmlspecialchars($name);
		echo "<option value=\"{$name}\"{$selected}>{$name}</option>\n";
	}
	echo <<<HTML
	</select>
	<input class="forJsDisabled" name="select" type="submit" value="Select" />

HTML;
}
function printSavedList($selectedDir) {
	global $AUTH_PASS, $DB_DIR;
	global $newTime, $oldTime;

	$entries = array();
	$count = 0;
	$prefix = dbFilePrefix($selectedDir);
	$prefixLen = strlen($prefix);
	$maxSizeLen = 0;
	if(file_exists($DB_DIR) && ($handle = opendir($DB_DIR))) {
		while(($entry = readdir($handle)) !== false) {
			if(
				substr($entry, 0, $prefixLen) === $prefix
				&& preg_match('/\D((\d{4})(\d\d)(\d\d)(\d\d)(\d\d)(\d\d))\.[^.]+$/', $entry, $m)
			) {
				$time = $m[1];
				$date = "{$m[2]}-{$m[3]}-{$m[4]} {$m[5]}:{$m[6]}:{$m[7]}";
				$file = $DB_DIR . DIRECTORY_SEPARATOR . $entry;
				$size = '[' . _n(filesize($file)) . ' bytes]';
				if(($sizeLen = strlen($size)) > $maxSizeLen)
					$maxSizeLen = $sizeLen;
				$entries[$time] = array(
					'date' => $date,
					'size' => $size
				);
				++$count;
			}
		}
		closedir($handle);
	}
	krsort($entries);

	$checkedNew = $newTime == '***current***' || !isset($newTime) ? ' checked="checked"' : '';
	$logoutBtn = $AUTH_PASS === '' ? '' : '
	<input style="float: right;" name="logout" type="submit" value="Logout" />';
	echo <<<HTML
	<input name="save" type="submit" value="Save snapshot" />
	<input name="compare" type="submit" value="Compare selected snapshots" />
	<input name="delete" type="submit" value="Delete checked snapshots" />{$logoutBtn}
	<ul id="snapshots-list">
		<li><input name="old" type="radio" value="***current***" style="visibility: hidden;"
			/><label for="current"><input id="current" name="new" type="radio" value="***current***"{$checkedNew} />***current***</label>
		</li>

HTML;
	$i = 0;
	foreach($entries as $time => $fileData) {
		$date = $fileData['date'];
		$size = str_pad($fileData['size'], $maxSizeLen, ' ', STR_PAD_LEFT);
		$checkedOld = $time == $oldTime || !$i && !isset($oldTime) ? ' checked="checked"' : '';
		$checkedNew = $time == $newTime ? ' checked="checked"' : '';
		++$i;
		$hideNew = $i == $count ? ' style="visibility: hidden;"' : '';
		echo <<<HTML
		<li><input id="old-{$time}" name="old" type="radio" value="{$time}"{$checkedOld}
			/><input id="new-{$time}" name="new" type="radio" value="{$time}"{$checkedNew}{$hideNew} /><label class="pre" for="old-{$time}"><span class="time">{$date}</span> {$size}</label>
			<input name="delete-{$time}" type="checkbox" />
		</li>

HTML;
	}
	echo <<<HTML
	</ul>
HTML;
}

function getToken() {
	if(!session_id())
		session_start();
	if(!isset($_SESSION['changes:csrfToken']))
		return $_SESSION['changes:csrfToken'] = getRandomId();
	return $_SESSION['changes:csrfToken'];
}
function checkToken() {
	if(!isset($_POST['token']) || $_POST['token'] !== getToken())
		exit();
}
function getRandomId($raw = false, $length = 128) {
	$rnd = '';
	if(
		function_exists('openssl_random_pseudo_bytes')
		&& strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN' // OpenSSL slow on Win
	) {
		$rnd = openssl_random_pseudo_bytes($length);
	}
	if(
		$rnd === ''
		&& is_readable('/dev/urandom')
		&& ($hRand = @fopen('/dev/urandom', 'rb'))
	) {
		$rnd = fread($hRand, $length);
		fclose($hRand);
	}
	if(
		$rnd === ''
		&& class_exists('COM')
	) {
		// http://msdn.microsoft.com/en-us/library/aa388176(VS.85).aspx
		try {
			$CAPIUtil = new COM('CAPICOM.Utilities.1');
			$rnd = $CAPIUtil->GetRandom($length, 1 /*CAPICOM_ENCODE_BINARY*/);
		}
		catch(Exception $ex) {
		}
	}
	if($rnd === '') {
		for($i = 1; $i <= $length; ++$i)
			$rnd .= chr(mt_rand(0, 255));
	}
	if($raw)
		return $rnd;
	return str_replace(
		array('+', '/', '='),
		array('-', '_', ''),
		base64_encode(hash('sha256', $rnd, true))
	);
}

?>