<?php

if (!defined('AMI_ROOT')) {
    ami_show_error('The constant AMI_ROOT must be defined.');
}

mb_internal_encoding('UTF-8');


// Ignore any user abort requests
ignore_user_abort(TRUE);

// Attempt to load the configuration file config.php
if (file_exists(AMI_ROOT.'config.inc.php')) {
    include AMI_ROOT.'config.inc.php';
}

if (!defined('AMI')) {
    ami_show_error("Файл конфигурации «config.inc.php» не найден или повреждён.");
}

// Block prefetch requests
if (isset($_SERVER['HTTP_X_MOZ']) && $_SERVER['HTTP_X_MOZ'] == 'prefetch') {
    header('HTTP/1.1 403 Prefetching Forbidden');

    // Send no-cache headers
    header('Expires: Thu, 21 Jul 1977 07:30:00 GMT');	// When yours truly first set eyes on this world! :)
    header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
    header('Cache-Control: post-check=0, pre-check=0', FALSE);
    header('Pragma: no-cache');		// For HTTP/1.0 compability

    exit();
}

if (AMI_DEBUG === TRUE) {
    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
   //ini_set("display_errors", 1);
}

// GLOBAL VARIABLES
$ami_addScript = $ami_onWindowReady = $ami_onDOMReady = $ami_Menu = array();

// LOAD UTF-8 FUNCTIONS
require AMI_ROOT.'include/ami/utf8/utf8.php';
require AMI_ROOT.'include/ami/utf8/ucwords.php';
require AMI_ROOT.'include/ami/utf8/trim.php';

// LOAD ALL AMI LIBS
require AMI_ROOT.'include/ami/functions.inc.php';
require AMI_ROOT.'include/ami/password.inc.php';
require AMI_ROOT.'include/ami/exceptions.inc.php';
require AMI_ROOT.'include/ami/user.inc.php';
require AMI_ROOT.'include/ami/url.inc.php';
require AMI_ROOT.'include/ami/db.inc.php';
require AMI_ROOT.'include/ami/logger.inc.php';
require AMI_ROOT.'include/ami/ajax.inc.php';
require AMI_ROOT.'include/ami/email.inc.php';

// user class
require AMI_ROOT.'include/ami/user_info.inc.php';

//
require AMI_ROOT.'include/url_shortener.inc.php';

// FACEBOOK
require AMI_ROOT.'include/facebook/facebook.inc.php';


// Reverse the effect of register_globals
ami_unregister_globals();

// get user info
try {
    $o_ami_user = new AMI_User();
    $ami_User = $o_ami_user->get_CurrentUser();
} catch(Exception $e) {
    ami_show_error($e->getMessage());
}




function pic_getImageLink($storage, $location, $hashed_filename, $size) {
    switch ($size) {
	case PIC_IMAGE_SIZE_SMALL:
	    $image_link = 'sm_'.$hashed_filename;
	    break;

	case PIC_IMAGE_SIZE_MIDDLE:
	    $image_link = 'md_'.$hashed_filename;
	    break;

	case PIC_IMAGE_SIZE_PREVIEW:
	    $image_link = 'pv_'.$hashed_filename;
	    break;

	case PIC_IMAGE_SIZE_GALLERY:
	    $image_link = 'gl_'.$hashed_filename;
	    break;

	case PIC_IMAGE_SIZE_ORIGINAL:
	    $image_link = $hashed_filename;
	    break;

	default:
	    throw new Exception(__METHOD__.': неизвестный размер картинки');
	    break;
    }

    // CHANGE ext FOR thumbs in TIFF format
    if ($size != PIC_IMAGE_SIZE_ORIGINAL) {
        $file_ext = pic_GetFileExt($hashed_filename);
	switch ($file_ext) {
	    case  'tif':
	    case  'bmp':
		$image_link = pic_replaceFileExtension($image_link, 'png');
		break;

	    default:
		break;
	}
    }

    return ami_link('image', array($storage, $location, $image_link));
}

function pic_replaceFileExtension($filename, $new_extension) {
    return preg_replace('/\..+$/', '.' . $new_extension, $filename);
}

function pic_GetFileExt($file_name) {
    if (strlen($file_name) == 0) {
		return FALSE;
    }

    return strtolower(substr(strrchr($file_name, "."), 1));
}

function format_pics($r) {
    return $r.' '.ami_Pon($r, 'картинка', 'картинки', 'картинок');
}

?>
