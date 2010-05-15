<?php

// Make sure no one attempts to run this script "directly"
if (!defined('UP')) {
	exit;
}

$ami_urls = array(
    'root'					=>	'',
    'delete_image'				=>	'delete/$1/$2/',
    'delete_image_ok'				=>	'delete/ok/',
    'upload'					=>	"upload/",
    'search'					=>	"search/",
    'view_image_owner'				=>	'view/$1/$2/$3/',
    'view_image'				=>	'view/$1/$2/',
    'show_image'				=>	'show/$1/',
    'image'					=>	'x/$1/$2/$3',
);

?>

