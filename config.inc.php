<?php

define('DEBUG', TRUE);


define('MYSQL_ADDRESS', '194.146.132.67');
define('MYSQL_DB', 'pic');
define('MYSQL_LOGIN', 'picDB_User');
define('MYSQL_PASSWORD', '_stB17ZfKs15:-)dddsDFa1d');
define('MYSQL_CHARSET', 'utf8');

// BASE URL aka CDN
define('CSS_BASE_URL', 'http://pic.iteam.net.ua/');
define('JS_BASE_URL', 'http://pic.lluga.net/');
define('JS_BASE_URL_1', 'http://pic.iteam.net.ua/');


// AJAX
define('ACTION_', 1);

//
define('CONFIG_TIMEZONE', "Europe/Zaporozhye");

//
define('IMAGE_SIZE_SMALL', 1);
define('IMAGE_SIZE_MIDDLE', 2);
define('IMAGE_SIZE_PREVIEW', 3);
define('IMAGE_SIZE_ORIGINAL', 4);


$amiBaseUrl = $picBaseUrl = $base_url = 'http://pic.iteam.net.ua';
$picDefaultPreviewSize = IMAGE_SIZE_MIDDLE;

// COOKIE SECTION
$cookie_domain = 'pic.iteam.net.ua';
$cookie_path = '/';
$cookie_secure = 0;

// UPLOAD
$picUploadBaseDir = '/var/upload/pic/';
$picMaxUploadSize = 10*1048576;

// GOOGLE ANALYTICS SECTION
$googleAnalyticsCode = '';

$picStorages = array('1','2','3','4','5','6','7','8');


define('UP', 1);

?>
