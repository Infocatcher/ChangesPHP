<?php

session_start();

//~ todo: implement authorization!
// https://github.com/Infocatcher/ChangesPHP/issues/2
if(isset($FILES_ACCESS) && $FILES_ACCESS) {
	$_SESSION['changes:view'] = true;
	session_regenerate_id();
}

?>