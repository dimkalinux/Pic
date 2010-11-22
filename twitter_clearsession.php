<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';

session_start();
session_destroy();

ami_redirect($ami_link('twitter_connect'));
?>
