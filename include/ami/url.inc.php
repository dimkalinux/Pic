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

	/* SHOW */
    'show_image'				    			=> 'show/$1/',
    'show_image_with_delete'	    			=> 'show/$1/del_key/$2/',

    'show_group_image_preselect_with_delete'    => 'show/group/$1/$2/del_key/$3/',
    'show_group_image_preselect'    			=> 'show/group/$1/$2/',

    'show_group_image_with_delete'  			=> 'show/group/$1/del_key/$2/',
    'show_group_image'			    			=> 'show/group/$1/',

    'show_group_image_slideshow'    			=> 'show/slideshow/$1/',
    'image'					        			=> 'x/$1/$2/$3',


	'about_ext'		    			=> 'about/ext/',
	'about_updates'	    			=> 'about/updates/',
    'about'			    			=> 'about/',

    'feedback'			    		=> 'feedback/',
    'feedback_ok'		    		=> 'feedback/ok/',

    'password_reset'			   	=> 'password/reset/',
    'password_reset_ok'		   		=> 'password/reset/ok/',
    'password_reset_error'	   		=> 'password/reset/error/$1/',

	'password_loged_with_new'		=> 'password/changed/',
    'password_change'	   			=> 'password/change/',
    'password_change_ok'   			=> 'password/change/ok/',

    'register'			    		=> 'register/',
    'register_ok'		    		=> 'register/ok/',
    'register_facebook'	    		=> 'register/facebook/',

    'login'			    	    	=> 'login/',
    'login_ok'		     	    	=> 'login/ok/',
    'login_facebook'	   	    	=> 'login/facebook/',

    'logout'			        	=> 'logout/',

	'myfiles_page'			       	=> 'myfiles/page/$1',
    'myfiles'			        	=> 'myfiles/',
    'profile_facebook'				=> 'profile/facebook/',
    'profile_facebook_connect'		=> 'profile/facebook/connect/',
    'profile_facebook_disconnect'	=> 'profile/facebook/disconnect/',
    'profile'			        	=> 'profile/',
    'settings'			        	=> 'settings/',
    'settings_save'			    	=> 'settings/save/',
    'settings_ok'			    	=> 'settings/ok/',
    'ajax'                      	=> 'ajax/',
);

?>
