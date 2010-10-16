<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';
require AMI_ROOT.'include/ajax.inc.php';

$result = AMI_AJAX_RESULT_ERROR;
$out = '';


$action = isset($_REQUEST['t_action']) ? intval($_REQUEST['t_action'], 10) : -1;

switch ($action) {
	case PIC_AJAX_ACTION_URL_SHORT:
		$ajax = new AJAX;
		$ajax->url_shortener_link();
		break;

	default:
		$out = 'Unknown command '.$action;
		break;
}


// Log errors
if ($result === 0) {
	//$log->debug('AJAX backend error: «'.$out.$addMessage.'»');
}

// RETURN and EXIT
ami_async_response(array('result'=> $result, 'message' => $out), AMI_ASYNC_JSON);
