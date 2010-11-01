<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit;
}



class API_Key {

	public function __construct() {
		if (!defined('API_KEY_ID_UNKNOWN')) {
			throw new Exception("В конфиге не определена константа 'API_KEY_UNKNOWN'");
		}
	}


	public function create($name, $desc='') {
		if (empty($name)) {
			throw new Exception("Параметр 'name' не указан");
		}

		//
		$name = mb_substr($name, 0, 96);
		$desc = mb_substr($desc, 0, 1024);
		//
		$key_uid = $this->generate_key_uid();

		return $this->add($key_uid, $name, $desc);
	}


	public function del($key_uid) {
		if (empty($key_uid)) {
			throw new Exception("Параметр 'key_uid' не указан");
		}

		$key_id = $this->get_id_by_key_uid($key_uid);
		if ($key_id === FALSE) {
			throw new Exception("Указаный 'key_uid' не существует");
		}

		$db = DB::singleton();
		$db->query("DELETE FROM api_keys WHERE id=? LIMIT 1", $key_id);
	}


	public function disable($key_uid) {
		if (empty($key_uid)) {
			throw new Exception("Параметр 'key_uid' не указан");
		}

		$key_id = $this->get_id_by_key_uid($key_uid);
		if ($key_id === FALSE) {
			throw new Exception("Указаный 'key_uid' не существует");
		}

		$db = DB::singleton();
		$db->query("UPDATE api_keys SET disabled=1 WHERE id=? LIMIT 1", $key_id);
	}

	public function enable($key_uid) {
		if (empty($key_uid)) {
			throw new Exception("Параметр 'key_uid' не указан");
		}

		$key_id = $this->get_id_by_key_uid($key_uid);
		if ($key_id === FALSE) {
			throw new Exception("Указаный 'key_uid' не существует");
		}

		$db = DB::singleton();
		$db->query("UPDATE api_keys SET disabled=0 WHERE id=? LIMIT 1", $key_id);
	}


	private function add($key_uid, $key_name, $key_desc='') {
		$db = DB::singleton();
		$db->query("INSERT INTO api_keys VALUES('', ?, ?, ?, 0, NOW())", $key_uid, $key_name, $key_desc);
		$key_id = $db->lastID();

		return array('id' => $key_id, 'key_uid' => $key_uid);
	}


	private function generate_key_uid() {
		$key_id = FALSE;

		$db = DB::singleton();
		$key_id = $db->create_uniq_hash_key_range('key_id', 'api_keys', 24, 24);

		return $key_id;
	}

	public function get_id_by_key_uid($key_uid) {
		$id = FALSE;

		if (empty($key_uid)) {
			throw new Exception("Параметр 'key_uid' не указан");
		}

		$db = DB::singleton();
		$row = $db->getRow('SELECT id FROM api_keys WHERE key_id=? LIMIT 1', $key_uid);
		if ($row) {
			$id = intval($row['id'], 10);
		}

		return $id;
	}
}

?>
