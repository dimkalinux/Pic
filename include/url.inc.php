<?php

// Make sure no one attempts to run this script "directly"
if (!defined('UP')) {
	exit;
}

$ami_urls = array(
    'root'					    =>	'',
    'delete_image'				=>	'delete/$1/$2/',
    'delete_image_ok'			=>	'delete/ok/',
    'upload'					=>	'upload/',
    'search'					=>	'search/',
    'links_image_owner'			=>	'links/$1/$2/$3/',
    'links_image'				=>	'links/$1/$2/',
    'show_image'				=>	'show/$1/',
    'image'					    =>	'x/$1/$2/$3',
);

?>

