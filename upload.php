<?php

if (!defined('AMI_ROOT')) {
    define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';
require AMI_ROOT.'include/upload.inc.php';


$async = FALSE;
if (isset($_POST['async'])) {
    $async = TRUE;
    unset($_POST['async']);
}

try {
    /*if (!isset($_POST['upload'])) {
	throw new AppLevelException("Получен запрос без файла.");
    }*/

    $files = $_POST;
    fixFilesArray($files);

    $upload = new Upload($files, $async);
}  catch (AppLevelException $e) {
    if ($async) {
	ami_async_response(array('error'=> 1, 'message' => $e->getMessage()), AMI_ASYNC_JSON);
    } else {
	ami_show_error_message($e->getMessage().'<p><br/><a href="'.$picBaseUrl.'">Перейти на главную страницу</a></p>');
    }
} catch (Exception $e) {
    if ($async) {
	ami_async_response(array('error'=> 1, 'message' => $e->getMessage()), AMI_ASYNC_JSON);
    } else {
	ami_show_error($e->getMessage());
    }
}


function fixFilesArray(&$files) {
    $names = array('upload_name' => 1, 'upload_content_type' => 1, 'upload_path' => 1, 'upload_size' => 1);

    foreach ($files as $key => $part) {
        // only deal with valid keys and multiple files
        $key = (string) $key;
        if (isset($names[$key]) && is_array($part)) {
            foreach ($part as $position => $value) {
		        $files[$position][$key] = $value;
            }
            // remove old key reference
            unset($files[$key]);
        }
    }
}

?>
