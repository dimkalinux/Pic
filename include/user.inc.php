<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit;
}


class User {
	public static function get_CurrentUser() {
		$user = array("email" => '', "ip" => '', "id" => 0, "login" => '', "is_admin" => FALSE, "is_guest" => TRUE, "geo" => 'world');

		$user['ip'] = ami_GetIP();

		$is_logged = User::logged();
		if ($is_logged !== FALSE) {
			$userinfo = User::getUserInfo($is_logged);
		}

		if ($is_logged === FALSE) {
			// is guest
			$user['id'] = AMI_GUEST_UID;
			$user['is_admin'] = FALSE;
			$user['is_guest'] = TRUE;
			$user['gravatar'] = '';
		} else {
			$user['id'] = $is_logged;
			$user['is_guest'] = FALSE;
			$user['email'] = $userinfo['email'];
			$user['is_admin'] = (bool) $userinfo['is_admin'];
		}

		// SET GEO
		$user['geo'] = ami_GetGEO();

		return $user;
	}


	public static function login($uid, $email, $is_admin) {
		global $ami_LoginCookieName, $ami_LoginCookieSalt;

		if ($uid == AMI_GUEST_UID) {
			throw new InvalidInputDataException('Попптыка входа с гостевым ID');
		}

		$sid = ami_GenerateRandomHash(32);
		$ip = ami_GetIP();

		// expires
		$expire = time() + 1209600;
		$dbExpire = 'NOW() + INTERVAL 14 DAY';

		$db = DB::singleton();
  		$db->query("DELETE FROM session WHERE sid=? AND uid=?", $sid, $uid);
	   	$db->query("INSERT INTO session VALUES(?, ?, INET_ATON(?), $dbExpire, ?, ?)", $sid, $uid, $ip, $email, $is_admin);

		// set login cookie
		ami_SetCookie($ami_LoginCookieName, base64_encode($uid.'|'.$sid.'|'.$expire.'|'.sha1($ami_LoginCookieSalt.$uid.$sid.$expire)), $expire);
	}

	public static function getUserFiles($user_id, $exceptID=FALSE) {
		global $base_url, $user;

		$imOwner = (bool) ($user['id'] === $user_id);

		$out = '';
		if (!$user_id) {
			return $out;
		}

		try {
			$db = DB::singleton();
			$datas = $db->getData("SELECT *, DATEDIFF(NOW(), GREATEST(last_downloaded_date,uploaded_date)) as NDI FROM up WHERE user_id=? AND deleted=0 ORDER BY id DESC LIMIT 5000", $user_id);
		} catch (Exception $e) {
			throw new Exception($e->getMessage());
		}

		if ($imOwner === TRUE) {
			$schemaOut = '
				<table class="t1" id="top_files_table">
				<thead>
				<tr>
					<th colspan="2" class="noborder"></th>
					<th class="noborder">
						<div class="controlButtonsBlock">
							<button type="button" class="btn" disabled="disabled" onmousedown="UP.userFiles.deleteItem();"><span><span>удалить</span></span></button>
						</div>
					</th>
				</tr>
				<th colspan="2" class="noborder"></th>
				<tr>
					<th class="center checkbox"><input type="checkbox" id="allCB"/></th>
					<th class="size">Размер</th>
					<th class="name">Имя файла</th>
					<th class="download">Скачан</th>
					<th class="time">Срок</th>
				</tr>
				</thead>
				<tbody>%s</tbody>
				</table>';
		} else {
			$schemaOut = '
				<table class="t1" id="top_files_table">
				<thead>
				<tr>
					<th class="size">Размер</th>
					<th class="name">Имя файла</th>
					<th class="download">Скачан</th>
					<th class="time">Срок</th>
				</tr>
				</thead>
				<tbody>%s</tbody>
				</table>';
		}

		if ($datas) {
			foreach ($datas as $item) {
				$item_id = intval($item['id'], 10);
				$filename = get_cool_and_short_filename($item['filename'], 45);
				$filesize_text = format_filesize($item['size']);
				$downloaded = $item['downloads'];
				$item_pass = $item['delete_num'];
				$wakkamakka = get_time_of_die($item['size'], $item['downloads'], $item['NDI'], (bool)$item['spam']);
				if ($wakkamakka < 1) {
					$wakkamakka_text = '0';
				} else {
					$wakkamakka_text = format_days($wakkamakka);
				}

				$passwordLabel = '';
				if (!empty($item['password'])) {
					$passwordLabel = '<span class="passwordLabel" title="Файл защищён паролем">&beta;</span>';
				}

				if ($imOwner === TRUE) {
					$itemURL = "{$base_url}{$item_id}/{$item_pass}/";
					$checkBoxRow = '<td class="center"><input type="checkbox" value="1" id="item_cb_'.$item_id.'"/></td>';
				} else {
					$itemURL = "{$base_url}{$item_id}/";
					$checkBoxRow = '';
				}


				$out .= <<<FMB
					<tr id="row_item_{$item_id}" class="row_item">
						$checkBoxRow
						<td class="size">$filesize_text</td>
						<td class="name">{$passwordLabel}<a rel="nofollow" href="$itemURL">$filename</a></td>
						<td class="download">$downloaded</td>
						<td class="time">$wakkamakka_text</td>
					</tr>
FMB;
				}
		}

		return sprintf($schemaOut, $out);
	}


	public static function logout() {
		global $ami_LoginCookieName, $ami_LoginCookieSalt;

		if (!isset($_COOKIE[$ami_LoginCookieName])) {
			return FALSE;
		}

		$ip = ami_GetIP();
		list($uid, $sid, $expire, $checksum) = explode('|', base64_decode($_COOKIE[$ami_LoginCookieName]), 4);

		$uid = intval($uid, 10);
		$expire = intval($expire, 10);

		// logouted cookie?
		if ($uid === 0) {
			return FALSE;
		}

		// check checksum
		if ($checksum != sha1($ami_LoginCookieSalt.$uid.$sid.$expire)) {
			return FALSE;
		}

		$db = DB::singleton();

		// delete all expires from session DB
		$db->query('DELETE FROM session WHERE expire < NOW()');

		// check sid
		$result = $db->numRows('SELECT sid FROM session WHERE sid=? AND uid=? AND ip=INET_ATON(?) LIMIT 1', $sid, $uid, $ip);
		if ($result !== 1) {
			return FALSE;
		}

		// all OK
		// 1. delete from session DB
		$db->query('DELETE FROM session WHERE sid=? AND uid=? AND ip=INET_ATON(?) LIMIT 1', $sid, $uid, $ip);

		// 2. set logouted cookie
		$expire += 1209600;
		$randomSID = ami_GenerateRandomHash(32);
		ami_SetCookie($ami_LoginCookieName, base64_encode('0|'.$randomSID.'|'.$expire.'|'.sha1($ami_LoginCookieSalt.'0'.$randomSID.$expire)), $expire);
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

	public static function getUserInfo($uid) {
		$db = DB::singleton();

		$row = $db->getRow("SELECT email,admin FROM session WHERE uid=? LIMIT 1", $uid);
		if ($row) {
			return array("email" => $row['email'], "is_admin" => $row['admin']);
		}

		return '';
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


	public static function logged() {
		global $ami_LoginCookieName, $ami_LoginCookieSalt;

		if (!isset($_COOKIE[$ami_LoginCookieName])) {
			return FALSE;
		}

		$ip = ami_GetIP();

		list($uid, $sid, $expire, $checksum) = explode('|', base64_decode($_COOKIE[$ami_LoginCookieName]), 4);
		// safe data
		$uid = intval($uid, 10);
		$expire = intval($expire, 10);

		// logouted cookie?
		if ($uid === 0) {
			return FALSE;
		}

		// check checksum
		if ($checksum != sha1($ami_LoginCookieSalt.$uid.$sid.$expire)) {
			$log = new Logger;
			$log->info('Invalid cookie checksum: logged '.$uid);
			return false;
		}

		$db = DB::singleton();

		// delete all expires from session DB
		$db->query('DELETE FROM session WHERE expire < NOW()');

		// check sid
		$result = $db->numRows('SELECT sid FROM session WHERE sid=? AND uid=? AND ip=INET_ATON(?) LIMIT 1', $sid, $uid, $ip);
		if ($result !== 1) {
			return false;
		}

		// all OK
		// 1. update expire on DB and Cookie
		$db->query('UPDATE session SET expire=(NOW() + INTERVAL 14 DAY) WHERE sid=? AND uid=? AND ip=INET_ATON(?) LIMIT 1', $sid, $uid, $ip);
		$expire = time() + 1209600;
		ami_SetCookie($ami_LoginCookieName, base64_encode($uid.'|'.$sid.'|'.$expire.'|'.sha1($ami_LoginCookieSalt.$uid.$sid.$expire)), $expire);

		// 2. return UID
		return $uid;
	}
}

?>
