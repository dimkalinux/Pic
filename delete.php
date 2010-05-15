<?php

if (!defined('UP_ROOT')) {
	define('UP_ROOT', './');
}

require UP_ROOT.'functions.inc.php';

if (isset($_GET['ok'])) {
	$home_link = ami_link('root');
	$out = <<<ZZZ
	<div id="status">&nbsp;</div>
	<h2>OK</h2>
	<p>Файл удалён с сервера</p>
	<p><a href="$home_link">Перейти на главную страницу</a></p>
ZZZ;
	ami_printPage($out);
	exit();
}

try {
	$key_id = isset($_GET['k']) ? get_safe_string_len($_GET['k'], 16) : FALSE;
	$key_delete = isset($_GET['d']) ? get_safe_string_len($_GET['d'], 16) : FALSE;

	if (!$key_id || !$key_delete) {
		throw new AppLevelException('Недостаточно параметров в запросе');
	}

	$db = DB::singleton();
	$row = $db->getRow("SELECT * FROM pic WHERE id_key=? AND delete_key=? LIMIT 1", $key_id, $key_delete);
	if (!$row) {
		throw new AppLevelException('Файл не найден или уже удалён.');
	}

	$id = $row['id'];
	$storage = get_safe_string($row['storage']);
	$location = get_safe_string($row['location']);
	$hash_filename = $row['hash_filename'];

	// REMOVE FROM SERVER
	$storage_dir = $picUploadBaseDir.$storage.'/'.$location;
	ami_cleanDir($storage_dir);
	if (!rmdir($storage_dir)) {
		$log = Logger::singleton();
		$log->error("Cant remove dir '$storage_dir'");
	}

	// REMOVE FROM DB
	$db->query("DELETE FROM pic WHERE id=? LIMIT 1", $id);
	if ($db->affected() !== 1) {
		$log = Logger::singleton();
		$log->error("Cant remove from DB '$id'");
	}

	// is async request
	if (isset($_POST['async'])) {
		exit(json_encode(array('error'=> 0, 'message' => '')));
	} else {
		ami_redirect(ami_link('delete_image_ok'));
	}
} catch (AppLevelException $e) {
	if (isset($_POST['async'])) {
		exit(json_encode(array('error'=> 1, 'message' => $e->getMessage())));
	} else {
		ami_show_error_message($e->getMessage());
	}
} catch (Exception $e) {
	if (isset($_POST['async'])) {
		exit(json_encode(array('error'=> 1, 'message' => $e->getMessage())));
	} else {
		ami_show_error($e->getMessage());
	}
}




?>
