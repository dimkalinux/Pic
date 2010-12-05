<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit;
}


class AMI_User_Info {
	public static function getConfigValue($uid, $config_name, $default_value=FALSE) {
		$uid = intval($uid, 10);

		if ($uid === AMI_GUEST_UID) {
			return $default_value;
			//throw new AppLevelException('Functions access denied');
		}

		try {
			$db = DB::singleton();

			$row = $db->getRow("SELECT val FROM users_config WHERE uid=? AND name=? LIMIT 1", $uid, $config_name);
			if ($row) {
				return $row['val'];
			}

			return $default_value;
		} catch(Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	public static function setConfigValue($uid, $config_name, $config_value) {
		$uid = intval($uid, 10);

		if ($uid === AMI_GUEST_UID) {
			throw new AppLevelException('Functions access denied');
		}

		try {
			$db = DB::singleton();
			$db->query('DELETE FROM users_config WHERE uid=? and name=? LIMIT 1', $uid, $config_name);
			$row = $db->getRow("INSERT INTO users_config VALUES(?, ?, ?)", $uid, $config_name, $config_value);
		} catch(Exception $e) {
			throw new Exception($e->getMessage());
		}
	}


	public static function getUsername($uid) {
		try {
			$db = DB::singleton();

			$row = $db->getRow("SELECT username FROM session WHERE uid=? LIMIT 1", $uid);
			if ($row) {
				return $row['username'];
			}

			return '';
		} catch(Exception $e) {
			throw new Exception($e->getMessage());
		}
	}


	public static function getUserEmail($uid) {
		try {
			$db = DB::singleton();

			$row = $db->getRow("SELECT email FROM users WHERE id=? LIMIT 1", $uid);
			if ($row) {
				return $row['email'];
			}

			return '';
		} catch(Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	public static function getUserUsername($uid) {
		try {
			$db = DB::singleton();

			$row = $db->getRow("SELECT username FROM users WHERE id=? LIMIT 1", $uid);
			if ($row) {
				return $row['username'];
			}

			return '';
		} catch(Exception $e) {
			throw new Exception($e->getMessage());
		}
	}

	public static function getUserFB_uid($uid) {
		try {
			$db = DB::singleton();

			$row = $db->getRow("SELECT fb_uid FROM users WHERE id=? LIMIT 1", $uid);
			if ($row) {
				return empty($row['fb_uid']) ? FALSE : $row['fb_uid'];
			}

			return FALSE;
		} catch(Exception $e) {
			throw new Exception($e->getMessage());
		}
	}
}

?>
