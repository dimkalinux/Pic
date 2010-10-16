<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit;
}


//
class AMI_User {
	public function get_CurrentUser() {
		$user = array(
			'email' => '',
			'ip' => '',
			'id' => 0,
			'login' => '',
			'is_admin' => FALSE,
			'is_guest' => TRUE,
			'geo' => 'world',
			'profile_name' => '',
			'facebook_uid' => FALSE);

		$is_logged = $this->logged();

		if ($is_logged !== FALSE) {
			// GET USERINFO from SESSIOn BY SID
			$userinfo = $this->getUserInfo($is_logged);
			//
			$user['id'] = $userinfo['uid'];
			$user['is_guest'] = FALSE;
			$user['email'] = $userinfo['email'];
			$user['facebook_uid'] = $userinfo['facebook_uid'];
			$user['is_admin'] = (bool) $userinfo['is_admin'];
			$user['profile_name'] = $userinfo['profile_name'];
		} else {
			// is guest
			$user['id'] = AMI_GUEST_UID;
			$user['is_admin'] = FALSE;
			$user['is_guest'] = TRUE;
			$user['gravatar'] = '';
		}

		// SET GEO
		$user['geo'] = ami_GetGEO();

		// SET IP
		$user['ip'] = ami_GetIP();


		return $user;
	}

	private function getUserInfo($sid) {
		$userinfo = FALSE;
		$db = DB::singleton();

		$row = $db->getRow("SELECT uid,email,admin,facebook_uid,profile_link FROM session WHERE sid=? LIMIT 1", $sid);
		if ($row) {
			$userinfo['email'] = $row['email'];
			$userinfo['is_admin'] = $row['admin'];
			$userinfo['facebook_uid'] = $row['facebook_uid'];
			$userinfo['uid'] = intval($row['uid'], 10);
			$userinfo['profile_name'] = $row['profile_link'];
		}

		return $userinfo;
	}



	public function login($uid, $email, $is_admin) {
		global $ami_LoginCookieName, $ami_LoginCookieSalt;

		if ($uid == AMI_GUEST_UID) {
			throw new InvalidInputDataException('Попытка входа с гостевым UID');
		}

		$sid = ami_GenerateRandomHash(32);
		$ip = ami_GetIP();

		// expires
		$expire = time() + 1209600;
		$dbExpire = 'NOW() + INTERVAL 14 DAY';

		$db = DB::singleton();
  		$db->query("DELETE FROM session WHERE sid=? AND uid=?", $sid, $uid);
	   	$db->query("INSERT INTO session VALUES(?, ?, INET_ATON(?), $dbExpire, ?, ?, '', ?)", $sid, $uid, $ip, $email, $is_admin, $email);

		// set login cookie
		ami_SetCookie($ami_LoginCookieName, base64_encode($uid.'|'.$sid.'|'.$expire.'|'.sha1($ami_LoginCookieSalt.$uid.$sid.$expire)), $expire);
	}



	public function facebook_login($uid, $email, $is_admin, $sid, $facebook_uid, $facebook_name) {
		global $ami_LoginCookieName, $ami_LoginCookieSalt;

		if ($uid == AMI_GUEST_UID) {
			throw new InvalidInputDataException('Попытка входа с гостевым UID');
		}

		// LOGOUT as EMAIL USER
		self::logout();

		$ip = ami_GetIP();

		// expires
		$expire = time() + 1209600;
		$dbExpire = 'NOW() + INTERVAL 14 DAY';

		$db = DB::singleton();
  		$db->query("DELETE FROM session WHERE sid=? AND uid=?", $sid, $uid);
	   	$db->query("INSERT INTO session VALUES(?, ?, INET_ATON(?), $dbExpire, ?, ?, ?, ?)", $sid, $uid, $ip, $email, $is_admin, $facebook_uid, $facebook_name);
	}



	public function logout() {
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



	public function logout_facebook() {
		try {
			setcookie('fbs_142764589077335', 0, (time() - 99999));
		} catch(FacebookApiException $e) {
			throw new Exception($e->getMessage());
		}
	}



	public function logged() {
		global $ami_LoginCookieName, $ami_LoginCookieSalt, $ami_UseFacebook;

		$ip = ami_GetIP();

		//
		do {
			if (!isset($_COOKIE[$ami_LoginCookieName])) {
				break;
			}

			list($uid, $sid, $expire, $checksum) = explode('|', base64_decode($_COOKIE[$ami_LoginCookieName]), 4);

			// safe data
			$uid = intval($uid, 10);
			$expire = intval($expire, 10);

			// logouted cookie?
			if ($uid === 0) {
				break;
			}

			// check checksum
			if ($checksum != sha1($ami_LoginCookieSalt.$uid.$sid.$expire)) {
				$log = new Logger;
				$log->info('Invalid cookie checksum: logged '.$uid);
				break;
			}

			$db = DB::singleton();

			// delete all expires from session DB
			$db->query('DELETE FROM session WHERE expire < NOW()');

			// check sid
			$result = $db->numRows('SELECT sid FROM session WHERE sid=? AND uid=? AND ip=INET_ATON(?) LIMIT 1', $sid, $uid, $ip);
			if ($result !== 1) {
				break;
			}

			// all OK
			// 1. update expire on DB and Cookie
			$db->query('UPDATE session SET expire=(NOW() + INTERVAL 14 DAY) WHERE sid=? AND uid=? AND ip=INET_ATON(?) LIMIT 1', $sid, $uid, $ip);
			$expire = time() + 1209600;
			ami_SetCookie($ami_LoginCookieName, base64_encode($uid.'|'.$sid.'|'.$expire.'|'.sha1($ami_LoginCookieSalt.$uid.$sid.$expire)), $expire);

			// 2. return SID
			return $sid;
		} while(0);


		// FACEBOOK?
		if (!$ami_UseFacebook) {
			return FALSE;
		}

		// MAYBE LOGGED as FACEBOOK?
		try {
			$facebook = new Facebook(array('appId' => '142764589077335','secret' => 'b1da5f70416eed03e55c7b2ce7190bd6','cookie' => TRUE));
			$fb_session = $facebook->getSession();

			// Session based API call.
			if ($fb_session) {
				$fb_uid = $facebook->getUser();
				//$facebook->api('/me');
				$fb_sid = md5($fb_session['session_key']);

				$db = DB::singleton();

				// delete all expires from session DB
				$db->query('DELETE FROM session WHERE expire < NOW()');

				// check sid
				$result = $db->numRows('SELECT sid FROM session WHERE facebook_uid=? AND ip=INET_ATON(?) LIMIT 1', $fb_uid, $ip);
				if ($result !== 1) {
					return FALSE;
				}

				// all OK
				// 1. update expire on DB
				$db->query('UPDATE session SET expire=(NOW() + INTERVAL 14 DAY),sid=? WHERE facebook_uid=? AND ip=INET_ATON(?) LIMIT 1', $fb_sid, $fb_uid, $ip);

				// GET sid
				return $fb_sid;
			} else {
				return FALSE;
			}
		} catch (FacebookApiException $e) {
			return FALSE;
		}

		return FALSE;
	}
}


?>
