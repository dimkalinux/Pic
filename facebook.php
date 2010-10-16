<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';

// GET ACTION
$action = isset($_REQUEST['a']) ? intval($_REQUEST['a'], 10) : FALSE;
$post_url = isset($_REQUEST['u']) ? $_REQUEST['u'] : FALSE;
$csrf = isset($_REQUEST['c']) ? $_REQUEST['c'] : FALSE;


// CHECK CSRF



//
switch ($action) {
	//
	case AMI_FACEBOOK_ACTION_LOGIN:


		break;

	//
	case AMI_FACEBOOK_ACTION_REGISTER:


		break;


	//
	case AMI_FACEBOOK_ACTION_CONNECT:


		break;

}


?>
