<?php

if (!defined('UP_ROOT')) {
	define('UP_ROOT', './');
}

require UP_ROOT.'functions.inc.php';
require UP_ROOT.'include/upload.inc.php';


$file = $_POST;

try {
	if (empty($file) || !isset($file['upload_name'])) {
		throw new AppLevelException("Получен запрос без файла.");
	}

	$upload_file = new Upload($file);

}  catch (AppLevelException $e) {
	if (isset($_POST['async'])) {
		exit(json_encode(array('error'=> 1, 'message' => $e->getMessage())));
	} else {
		ami_show_error_message($e->getMessage().'<p><br/><a href="'.$picBaseUrl.'">Перейти на главную страницу</a></p>');
	}
} catch (Exception $e) {
	if (isset($_POST['async'])) {
		exit(json_encode(array('error'=> 1, 'message' => $e->getMessage())));
	} else {
		ami_show_error($e->getMessage());
	}
}



?>
