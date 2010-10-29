<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit();
}

$ami_urls = array(
    'root'					    	=> '',
    'root_file'						=> '',
    'root_link'						=> 'link/',

    'advanced_root'			    	=> 'advanced/',
    'edit_image'					=> 'edit/$1/$2/',

    'delete_image'					=> 'delete/$1/$2/',
    'delete_group_image'			=> 'delete/group/$1/$2/',
    'delete_image_ok'				=> 'delete/ok/',
    'delete_group_image_ok'			=> 'delete/group/ok/',

    'upload'						=> 'upload/',
    'm_upload'						=> 'm_upload/',

    'search'						=> 'search/',

    'links_image_owner'				=> 'links/$1/$2/$3/',
    'links_group_image_owner'		=> 'links/group/$1/$2/$3/',
    'links_image'					=> 'links/$1/$2/',
    'links_group_image'			    => 'links/group/$1/$2/',

    'show_image'				    => 'show/$1/',
    'show_group_image_preselect'    => 'show/group/$1/$2/',
    'show_group_image'			    => 'show/group/$1/',
    'show_group_image_slideshow'    => 'show/slideshow/$1/',
    'image'					        => 'x/$1/$2/$3',

	'about_ext'		    			=> 'about/ext/',
    'about'			    			=> 'about/',

    'feedback'			    		=> 'feedback/',
    'feedback_ok'		    		=> 'feedback/ok/',

    'password_reset'			   	=> 'password_reset/',
    'password_reset_ok'		   		=> 'password_reset/ok/',
    'password_reset_error'	   		=> 'password_reset/error/$1/',

    'password_change'	   			=> 'password_change/',
    'password_change_ok'   			=> 'password_change/ok/',

    'register'			    		=> 'register/',
    'register_ok'		    		=> 'register/ok/',
    'register_facebook'		   		=> 'register/facebook/',

    'login'			    	    	=> 'login/',
    'login_facebook'    	    	=> 'login/facebook/',
    'login_ok'		     	    	=> 'login/ok/',

    'logout'			        	=> 'logout/',
    'logout_facebook'	        	=> 'logout/facebook/',

    'myfiles'			        	=> 'myfiles/',
    'profile'			        	=> 'profile/',
    'settings'			        	=> 'settings/',
    'settings_save'			    	=> 'settings/save/',
    'settings_ok'			    	=> 'settings/ok/',
    'ajax'                      	=> 'ajax/'
);

?>
