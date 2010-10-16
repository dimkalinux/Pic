<?php

// VERSION 0.1

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit();
}

//
define('AMI_AJAX_RESULT_ERROR', 0);
define('AMI_AJAX_RESULT_OK', 1);

//
class AMI_Ajax {
	protected function exitWithError($msg='') {
		ami_async_response(array('result'=> 0, 'message' => $msg), AMI_ASYNC_JSON);
	}
}
