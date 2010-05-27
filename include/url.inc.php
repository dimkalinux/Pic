<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit();
}

$ami_urls = array(
    'root'					    =>	'',
    'delete_image'				=>	'delete/$1/$2/',
    'delete_group_image'		=>	'delete/group/$1/$2/',
    'delete_image_ok'			=>	'delete/ok/',
    'delete_group_image_ok'		=>	'delete/group/ok/',
    'upload'					=>	'upload/',
    'm_upload'					=>	'm_upload/',
    'search'					=>	'search/',
    'links_image_owner'			=>	'links/$1/$2/$3/',
    'links_group_image_owner'	=>	'links/group/$1/$2/$3/',
    'links_image'				=>	'links/$1/$2/',
    'links_group_image'			=>	'links/group/$1/$2/',
    'show_image'				=>	'show/$1/',
    'show_group_image'			=>	'show/group/$1/$2/',
    'image'					    =>	'x/$1/$2/$3',
    'about'			    		=>	'about/',
    'feedback'			    	=>	'feedback/',
    'feedback_ok'		    	=>	'feedback/ok/',
);

?>
