<?php

if (!defined('AMI_ROOT')) {
    define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';
require AMI_ROOT.'include/upload.inc.php';


try {
    $async = TRUE;
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

    $return_format = AMI_ASYNC_JSON;
    if (isset($_POST['xml'])) {
        $return_format = AMI_ASYNC_XML;
        unset($_POST['xml']);
    }

    $reduce_original = 0;

    $a_urls = array();
    $query_url = isset($_POST['url']) ? $_POST['url'] : FALSE;

    if (!$query_url) {
        throw new Exception('Не найдено ни одного URL');
    }

    $urls = explode(',', $query_url);
    foreach ($urls as $url) {
        if (empty($url) || !ami_CheckIs_URL($url)) {
            continue;
        }

        array_push($a_urls, $url);
    }

    $a_urls = array_unique($a_urls);

    if (count($a_urls) < 1) {
        throw new Exception('Не найдено ни одного URL');
    }

    $upload_options = array(UPLOAD_FLAG_SKIP_FILESIZE_CHECK => TRUE);

    $upload = new Upload($a_urls);
    $upload_result = $upload->run($ami_User, $reduce_original, PIC_UPLOAD_URL, $upload_options);

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

?>
