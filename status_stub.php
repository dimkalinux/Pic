<?
if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';

try {
	$db = DB::singleton();
	$row = $db->getRow("SELECT COUNT(*) AS num_files FROM pic");
	exit($row['num_files']);
} catch (Exception $e) {
	exit('error');
}
exit($out);

