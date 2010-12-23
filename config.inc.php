<?php

define('AMI_DEBUG', TRUE);
define('AMI_DEBUG_LOG', '/tmp/pic2.log');

// DATABASE
define('AMI_MYSQL_ADDRESS', '194.146.132.67');
define('AMI_MYSQL_DB', 'pic2');
define('AMI_MYSQL_LOGIN', 'picDB_User');
define('AMI_MYSQL_PASSWORD', '_stB17ZfKs15:-)dddsDFa1d');
define('AMI_MYSQL_CHARSET', 'utf8');

// MEMCACHE
define('MEMCACHE_HOST', '194.146.132.67');
define('MEMCACHE_PORT', '11211');
define('MEMCACHE_PERSISTENT_CONNECT', TRUE);

// BASE URL aka CDN
define('AMI_CSS_BASE_URL', 'http://pic.lluga.net/');
define('AMI_JS_BASE_URL', 'http://pic.lluga.net/');
//
define('AMI_CONFIG_TIMEZONE', "Europe/Zaporozhye");


$ami_EnablePrintCSS = FALSE;

$ami_Production = FALSE;

// COOKIE SECTION
$ami_LoginCookieName = 'pic_login';
$ami_LoginCookieDomain = '';
$ami_LoginCookiePath = '/';
$ami_LoginCookieSecure = 0;
// ************** WARNING! PLEASE CHANGE THIS VALUE *****************
$ami_LoginCookieSalt = 'zzjdIof(df*f;ad';


// ************** WARNING! PLEASE CHANGE THIS VALUE *****************
$ami_CSRF_Key = '05dddlaoezz:_=dd';


// EMAIL
$ami_mailUseSMTP = TRUE;
$ami_mailSMTP_Server = 'mail.iteam.ua';
$ami_mailSMTP_User = '';
$ami_mailSMTP_Password = '';
$ami_mailDefaultFromName = 'Хостинг картинок pic.lg.ua';
$ami_mailDefaultFromEmail = 'webmaster@iteam.lg.ua';

// GOOGLE ANALYTICS SECTION
//$ami_googleAnalyticsCode = 'UA-6106025-9';

//
define('AMI_ASYNC_JSON', 1);
define('AMI_ASYNC_XML', 2);

//
define('AMI_GUEST_UID', 0);



// ========= APP PART ===========

// AJAX actions
define('PIC_AJAX_ACTION_URL_SHORT', 1);

//
define('PIC_IMAGE_SIZE_SMALL', 1);
define('PIC_IMAGE_SIZE_MIDDLE', 2);
define('PIC_IMAGE_SIZE_PREVIEW', 3);
define('PIC_IMAGE_SIZE_ORIGINAL', 4);
define('PIC_IMAGE_SIZE_GALLERY', 5);
define('PIC_IMAGE_SIZE_SLIDESHOW', 6);

$ami_BaseURL = $pic_BaseURL = 'http://pic.lluga.net';

// IMAGE SECTION
define('PIC_IMAGE_AUTOROTATE', TRUE);

//
define('PIC_IMAGE_GALLERY_HEIGHT', 70);
define('PIC_IMAGE_GALLERY_WIDTH', 70);
define('PIC_IMAGE_GALLERY_QUALITY', 90);
//
define('PIC_IMAGE_SMALL_HEIGHT', 250);
define('PIC_IMAGE_SMALL_WIDTH', 250);
define('PIC_IMAGE_SMALL_QUALITY', 92);
//
define('PIC_IMAGE_MEDIUM_HEIGHT', 350);
define('PIC_IMAGE_MEDIUM_WIDTH', 500);
define('PIC_IMAGE_MEDIUM_QUALITY', 92);
//
define('PIC_IMAGE_PREVIEW_HEIGHT', 650);
define('PIC_IMAGE_PREVIEW_WIDTH', 875);
define('PIC_IMAGE_PREVIEW_QUALITY', 93);

//
define('PIC_IMAGE_RESIZE_ORIGINAL_QUALITY', 95);

// USE PREVIEW IMAGE FOR CREATING THUMBS
define('PIC_USE_IMAGE_THUMBS_OPTIMIZE', TRUE);

define('PIC_USE_IMAGE_THUMBS_GAMMA_CORRECTION', TRUE);


// UPLOAD
define('PIC_UPLOAD_BASE_DIR', '/var/upload/pic2/');
define('PIC_UPLOAD_MAX_FILE_SIZE', 11534336);

// TMP
$pic_TMPdir = '/var/upload/pic2/tmp/';

$pic_UploadStorages = array('1');

// API KEYS
define('API_KEY_ID_UNKNOWN', 0);

//
define('PIC_VERSION', '7.2');

define('FACEBOOK_APP_ID', '142764589077335');
define('FACEBOOK_APP_SECRET', 'b1da5f70416eed03e55c7b2ce7190bd6');

// LAST, BUT NOT LEAST
define('AMI', 1);
?>
