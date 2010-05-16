<?php

if (!defined('UP_ROOT')) {
	define('UP_ROOT', './');
}

require UP_ROOT.'functions.inc.php';
require UP_ROOT.'include/upload.inc.php';


$file = $_POST;

$raw_file = $HTTP_RAW_POST_DATA;

$File = "/tmp/123";
$Handle = fopen($File, 'w');
fwrite($Handle, $raw_file);
fclose($Handle);
exit(1);


try {
	if (empty($file) || !isset($file['upload_name'])) {
		throw new AppLevelException("Получен запрос без файла.");
	}

	$upload = new Upload($file);
}  catch (AppLevelException $e) {
	if (isset($_POST['async'])) {
		ami_async_response(array('error'=> 1, 'message' => $e->getMessage()), AMI_ASYNC_JSON);
	} else {
		ami_show_error_message($e->getMessage().'<p><br/><a href="'.$picBaseUrl.'">Перейти на главную страницу</a></p>');
	}
} catch (Exception $e) {
	if (isset($_POST['async'])) {
		ami_async_response(array('error'=> 1, 'message' => $e->getMessage()), AMI_ASYNC_JSON);
	} else {
		ami_show_error($e->getMessage());
	}
}



?>
