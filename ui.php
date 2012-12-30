<?php

function printProjectsList($selectedDir) {
	global $DIRS;
	echo <<<HTML
	<select id="projects-list" name="dir" onchange="document.getElementsByName('select')[0].click();">

HTML;
	foreach($DIRS as $name => $dir) {
		$selected = $dir === $selectedDir ? ' selected="selected"' : '';
		$dir  = htmlspecialchars($dir);
		$name = htmlspecialchars($name);
		echo "<option value=\"{$dir}\"{$selected}>{$name}</option>\n";
	}
	echo <<<HTML
	</select>
	<input class="forJsDisabled" name="select" type="submit" value="Select" />

HTML;
}
function printSavedList($selectedDir) {
	global $DB_DIR;
	global $newTime, $oldTime;

	$entries = array();
	$count = 0;
	$prefix = dbFilePrefix($selectedDir);
	$prefixLen = strlen($prefix);
	if(file_exists($DB_DIR) && ($handle = opendir($DB_DIR))) {
		while(($entry = readdir($handle)) !== false) {
			if(
				substr($entry, 0, $prefixLen) === $prefix
				&& preg_match('/\D((\d{4})(\d\d)(\d\d)(\d\d)(\d\d)(\d\d))\.[^.]+$/', $entry, $m)
			) {
				$time = $m[1];
				$date = "{$m[2]}-{$m[3]}-{$m[4]} {$m[5]}:{$m[6]}:{$m[7]}";
				$entries[$time] = array(
					'date' => $date,
					'file' => $DB_DIR . '/' . $entry
				);
				++$count;
			}
		}
		closedir($handle);
	}
	krsort($entries);

	$checkedNew = $newTime == '***current***' || !isset($newTime) ? ' checked="checked"' : '';
	echo <<<HTML
	<input name="save" type="submit" value="Save snapshot" />
	<input name="compare" type="submit" value="Compare selected snapshots" />
	<input name="delete" type="submit" value="Delete checked snapshots" />
	<ul id="snapshots-list">
		<li><input name="old" type="radio" value="***current***" style="visibility: hidden;"
			/><label for="current"><input id="current" name="new" type="radio" value="***current***"{$checkedNew} />***current***</label>
		</li>

HTML;
	$i = 0;
	foreach($entries as $time => $fileData) {
		$date = $fileData['date'];
		$file = $fileData['file'];
		$checkedOld = $time == $oldTime || !$i && !isset($oldTime) ? ' checked="checked"' : '';
		$checkedNew = $time == $newTime ? ' checked="checked"' : '';
		++$i;
		$hideNew = $i == $count ? ' style="visibility: hidden;"' : '';
		$size = str_pad(
			'[' . _n(filesize($file)) . ' bytes]',
			15, // [999 999 bytes],
			' ', // &nbsp;
			STR_PAD_LEFT
		);
		echo <<<HTML
		<li><input id="old-{$time}" name="old" type="radio" value="{$time}"{$checkedOld}
			/><input id="new-{$time}" name="new" type="radio" value="{$time}"{$checkedNew}{$hideNew} /><label for="old-{$time}"><span class="time">{$date}</span> {$size}</label>
			<input name="delete-{$time}" type="checkbox" />
		</li>

HTML;
	}
	echo <<<HTML
	</ul>
HTML;
}

?>