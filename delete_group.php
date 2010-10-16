<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';

if (isset($_GET['ok'])) {
	ami_show_message('Файлы удалёны', 'Файлы успешно удалёны с сервера.');
}

$is_owner = FALSE;

try {
	$key_group_id = isset($_GET['g']) ? ami_get_safe_string_len($_GET['g'], 32) : FALSE;
	$key_delete = isset($_GET['d']) ? ami_get_safe_string_len($_GET['d'], 32) : FALSE;

	// TRY FETCH KEY_DELETE if im OWNER by LOGIN
	if ((!$key_delete || $key_delete === 0) && ($ami_User['is_guest'] === FALSE)) {
		$db = DB::singleton();
		$row = $db->getRow("SELECT delete_key FROM pic WHERE group_id=? AND owner_id=? LIMIT 1", $key_group_id, $ami_User['id']);
		if ($row && !empty($row['delete_key'])) {
			$key_delete = $row['delete_key'];
		}
	}

	if (!$key_group_id || !$key_delete) {
		throw new AppLevelException('Недостаточно параметров в запросе');
	}

	$db = DB::singleton();
	$data = $db->getData("SELECT * FROM pic WHERE group_id=? AND delete_key=?", $key_group_id, $key_delete);
	if (!$data) {
		throw new AppLevelException('Группа файлов не найдена или уже удалёна.');
	}

	foreach ($data as $row) {
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
	}

	// is async request
	if (isset($_GET['async'])) {
		ami_async_response(array('error'=> 0, 'message' => ''), AMI_ASYNC_JSON);
	} else {
		ami_redirect(ami_link('delete_group_image_ok'));
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
