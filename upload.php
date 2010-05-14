<?php

if (!defined('UP_ROOT')) {
	define('UP_ROOT', './');
}

require UP_ROOT.'functions.inc.php';
require UP_ROOT.'image.inc.php';
require UP_ROOT.'upload_file.inc.php';


try {
	$upload_file = new Upload_file();
} catch (Exception $e) {

}



?>
