<?php

if (!defined('UP_ROOT')) {
	define('UP_ROOT', '../');
}

require_once UP_ROOT.'functions.inc.php';
require_once UP_ROOT.'include/ajax.inc.php';

$result = 0;
$out = '';

$action = isset($_REQUEST['t_action']) ? intval($_REQUEST['t_action'], 10) : -1;

switch ($action) {
	case ACTION_SERVICE_UPDATE:
		$ajax = new AJAX;
		$ajax->updateService();
		break;

	default:
		$out = 'Unknown command '.$action;
		break;
}


// Log errors
if ($result === 0) {
	//$log = new Logger;
	$ip = $user['ip'];
	$login = $user['login'];
	$addMessage = " ip: $ip, login: $login, action: $action";
	//$log->debug('AJAX backend error: «'.$out.$addMessage.'»');
}

exit(json_encode(array('result'=> $result, 'message' => $out)));


?>

