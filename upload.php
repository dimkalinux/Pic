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

$api_key = FALSE;
if (isset($_POST['api_key'])) {
	$api_key = ami_trim($_POST['api_key']);
    unset($_POST['api_key']);
}

$reduce_original = 0;
if (isset($_POST['reduce_original'])) {
    $reduce_original = intval($_POST['reduce_original'], 10);
    unset($_POST['reduce_original']);
}


$return_format = AMI_ASYNC_JSON;
if (isset($_POST['xml'])) {
    $return_format = AMI_ASYNC_XML;
    unset($_POST['xml']);
}

try {
    $files = $_POST;
    fixFilesArray($files);

    if (empty($files)) {
        throw new AppLevelException("Получен запрос без файла.");
    }


    $upload = new Upload($files);
    $upload_result = $upload->run($ami_User, $reduce_original, PIC_UPLOAD_FILE, $api_key, array());

    // EXIT
	if ($use_api) {
		// RETURN UPLOADED image INFO
        ami_async_response(array('error' => 0, 'info' => $upload_result['info']), $return_format);
    } else {
		if ($async) {
			ami_async_response(array('error' => 0, 'url' => $upload_result['url']), $return_format);
		} else {
			ami_redirect($upload_result['url']);
		}
	}
} catch (AppLevelException $e) {
    if ($async || $use_api) {
        ami_async_response(array('error'=> 1, 'message' => $e->getMessage()), $return_format);
    } else {
        ami_show_error_message($e->getMessage());
    }
} catch (Exception $e) {
    if ($async || $use_api) {
        ami_async_response(array('error'=> 1, 'message' => $e->getMessage()), $return_format);
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
