<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';

if (isset($_GET['ok'])) {
	ami_show_message('Файл удалён', 'Файл успешно удалён с сервера.');
}

try {
	$key_id = isset($_GET['k']) ? ami_get_safe_string_len($_GET['k'], 32) : FALSE;
	$key_delete = isset($_GET['d']) ? ami_get_safe_string_len($_GET['d'], 32) : FALSE;


	// TRY FETCH KEY_DELETE if im OWNER by LOGIN
	if ((!$key_delete || $key_delete === 0) && ($ami_User['is_guest'] === FALSE)) {
		$db = DB::singleton();
		$row = $db->getRow("SELECT delete_key FROM pic WHERE id_key=? AND owner_id=? LIMIT 1", $key_id, $ami_User['id']);
		if ($row && !empty($row['delete_key'])) {
			$key_delete = $row['delete_key'];
		}
	}

	if (!$key_id || !$key_delete) {
		throw new AppLevelException('Недостаточно параметров в запросе');
	}

	$db = DB::singleton();
	$row = $db->getRow("SELECT * FROM pic WHERE id_key=? AND delete_key=? LIMIT 1", $key_id, $key_delete);
	if (!$row) {
		throw new AppLevelException('Файл не найден или уже удалён.');
	}

	$id = $row['id'];
	$storage = ami_get_safe_string($row['storage']);
	$location = ami_get_safe_string($row['location']);
	$hash_filename = $row['hash_filename'];

	// REMOVE FROM SERVER
	$storage_dir = $pic_UploadBaseDir.$storage.'/'.$location;
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
	if (isset($_GET['async'])) {
		ami_async_response(array('error'=> 0, 'message' => '', 'redirect' => ami_link('delete_image_ok')), AMI_ASYNC_JSON);
	} else {
		ami_redirect(ami_link('delete_image_ok'));
	}
} catch (AppLevelException $e) {
	if (isset($_GET['async'])) {
		ami_async_response(array('error'=> 1, 'message' => $e->getMessage()), AMI_ASYNC_JSON);
	} else {
		ami_show_error_message($e->getMessage());
	}
} catch (Exception $e) {
	if (isset($_GET['async'])) {
		ami_async_response(array('error'=> 1, 'message' => $e->getMessage()), AMI_ASYNC_JSON);
	} else {
		ami_show_error($e->getMessage());
	}
}




?>
