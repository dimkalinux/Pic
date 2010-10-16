<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit;
}

/**
 * Application Level server exception.
 *
 * @package	pic
 */
class AppLevelException extends Exception {}
class InvalidInputDataException extends Exception {}

?>
