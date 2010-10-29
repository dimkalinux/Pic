<?
if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';

try {
	$db = DB::singleton();
	$row = $db->getRow("SELECT COUNT(*) AS num_files FROM pic");
	exit(intval($row['num_files'], 10));
} catch (Exception $e) {
	exit(0);
}

