<?php

define('AMI_DEBUG', TRUE);


define('AMI_MYSQL_ADDRESS', '194.146.132.67');
define('AMI_MYSQL_DB', 'pic2');
define('AMI_MYSQL_LOGIN', 'picDB_User');
define('AMI_MYSQL_PASSWORD', '_stB17ZfKs15:-)dddsDFa1d');
define('AMI_MYSQL_CHARSET', 'utf8');

// BASE URL aka CDN
define('AMI_CSS_BASE_URL', 'http://pic.lluga.net/');
define('AMI_JS_BASE_URL', 'http://pic.lluga.net/');

//
define('AMI_CONFIG_TIMEZONE', "Europe/Zaporozhye");

//
define('PIC_IMAGE_SIZE_SMALL', 1);
define('PIC_IMAGE_SIZE_MIDDLE', 2);
define('PIC_IMAGE_SIZE_PREVIEW', 3);
define('PIC_IMAGE_SIZE_ORIGINAL', 4);
define('PIC_IMAGE_SIZE_GALLERY', 5);

define('AMI_ASYNC_JSON', 1);
define('AMI_ASYNC_XML', 2);


$ami_BaseURL = $pic_BaseURL = 'http://pic.lluga.net';
$pic_DefaultPreviewSize = PIC_IMAGE_SIZE_MIDDLE;

// IMAGE SECTION
$pic_image_autorotate = TRUE;

//
$pic_image_gallery_height = 70;
$pic_image_gallery_width = 70;
$pic_image_gallery_quality = 95;
//
$pic_image_small_height = 250;
$pic_image_small_width = 250;
$pic_image_small_quality = 90;
//
$pic_image_medium_height = 350;
$pic_image_medium_width = 500;
$pic_image_medium_quality = 90;
//
$pic_image_preview_height = 600;
$pic_image_preview_width = 875;
$pic_image_preview_quality = 90;


// UPLOAD
$pic_UploadBaseDir = '/var/upload/pic2/';
$pic_MaxUploadSize = 11*1048576;

// GOOGLE ANALYTICS SECTION
//$ami_googleAnalyticsCode = 'UA-6106025-9';

$pic_UploadStorages = array('1');

define('AMI', 1);
?>
