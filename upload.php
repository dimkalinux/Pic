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

$use_api = FALSE;
if (isset($_POST['api'])) {
    $use_api = TRUE;
    unset($_POST['api']);
}

$reduce_original = 0;
if (isset($_POST['reduce_original'])) {
    $reduce_original = intval($_POST['reduce_original'], 10);
    unset($_POST['reduce_original']);
}

try {
    $files = $_POST;
    fixFilesArray($files);

    if (empty($files)) {
        throw new AppLevelException("Получен запрос без файла.");
    }


    $upload = new Upload($files, $async, $ami_User, $use_api, $reduce_original);
} catch (AppLevelException $e) {
    if ($async || $use_api) {
        ami_async_response(array('error'=> 1, 'message' => $e->getMessage()), AMI_ASYNC_JSON);
    } else {
        ami_show_error_message($e->getMessage());
    }
} catch (Exception $e) {
    if ($async || $use_api) {
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
