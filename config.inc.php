<?php

define('DEBUG', TRUE);


define('MYSQL_ADDRESS', '194.146.132.67');
define('MYSQL_DB', 'pic');
define('MYSQL_LOGIN', 'picDB_User');
define('MYSQL_PASSWORD', '_stB17ZfKs15:-)dddsDFa1d');
define('MYSQL_CHARSET', 'utf8');

// BASE URL aka CDN
define('CSS_BASE_URL', 'http://pic.iteam.net.ua/');
define('JS_BASE_URL', 'http://pic.iteam.net.ua/');
define('JS_BASE_URL_1', 'http://pic.iteam.net.ua/');


//
define('CONFIG_TIMEZONE', "Europe/Zaporozhye");

//
define('IMAGE_SIZE_SMALL', 1);
define('IMAGE_SIZE_MIDDLE', 2);
define('IMAGE_SIZE_PREVIEW', 3);
define('IMAGE_SIZE_ORIGINAL', 4);

define('AMI_ASYNC_JSON', 1);
define('AMI_ASYNC_XML', 2);


$amiBaseUrl = $picBaseUrl = $base_url = 'http://pic.lg.ua';
$picDefaultPreviewSize = IMAGE_SIZE_MIDDLE;

// IMAGE SECTION
$pic_image_autorotate = TRUE;
//
$pic_image_small_height = 250;
$pic_image_small_width = 250;
$pic_image_small_quality = 87;

$pic_image_medium_height = 350;
$pic_image_medium_width = 500;
$pic_image_medium_quality = 87;

$pic_image_preview_height = 625;
$pic_image_preview_width = 875;
$pic_image_preview_quality = 90;

// COOKIE SECTION
$cookie_domain = 'pic.iteam.net.ua';
$cookie_path = '/';
$cookie_secure = 0;

// UPLOAD
$picUploadBaseDir = '/var/upload/pic/';
$picMaxUploadSize = 11*1048576;

// GOOGLE ANALYTICS SECTION
$googleAnalyticsCode = '';

$picStorages = array('1','2','3');


define('UP', 1);

?>
