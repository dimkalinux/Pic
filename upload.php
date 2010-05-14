<?php

if (!defined('UP_ROOT')) {
	define('UP_ROOT', './');
}

require UP_ROOT.'functions.inc.php';
require UP_ROOT.'include/upload.inc.php';
require UP_ROOT.'include/image.inc.php';
require UP_ROOT.'include/upload_file.inc.php';


try {
	if (!isset($_FILES['upload'])) {
		throw new Exception("Empty request for upload");
	}

	$upload_file = new Upload($_FILES['upload']);
} catch (Exception $e) {
	error($e->getMessage());
}



?>
