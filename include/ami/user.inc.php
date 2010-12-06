<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit;
}


//
class AMI_User {
	public function get_CurrentUser() {
		$user = array(
			'email' 		=> '',
			'sid'			=> FALSE,
			'ip' 			=> '',
			'id' 			=> 0,
			'login' 		=> '',
			'is_admin' 		=> FALSE,
			'is_guest' 		=> TRUE,
			'geo' 			=> 'world',
			'profile_name' 	=> '',
			'logout_link'	=> '',
		);

		$is_logged = $this->logged(FALSE, TRUE);

		if ($is_logged !== FALSE) {
			// GET USERINFO from SESSIOn BY SID
			$userinfo = $this->getUserInfo($is_logged);
			//
			$user['sid'] = $is_logged;
			$user['id'] = $userinfo['uid'];
			$user['is_guest'] = FALSE;
			$user['email'] = $userinfo['email'];
			$user['is_admin'] = (bool) $userinfo['is_admin'];
			$user['profile_name'] = $userinfo['profile_name'];
			$user['logout_link'] = $userinfo['logout_link'];
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

		$row = $db->getRow("SELECT uid,email,admin,profile_link,logout_link FROM session WHERE sid=? LIMIT 1", $sid);
		if ($row) {
			$userinfo['email'] = $row['email'];
			$userinfo['is_admin'] = $row['admin'];
			$userinfo['uid'] = intval($row['uid'], 10);
			$userinfo['profile_name'] = $row['profile_link'];
			$userinfo['logout_link'] = $row['logout_link'];
		}

		return $userinfo;
	}



	public function login($uid, $email, $is_admin, $check_ip, $logout_url) {
		global $ami_LoginCookieName, $ami_LoginCookieSalt;

		if ($uid == AMI_GUEST_UID) {
			throw new InvalidInputDataException('Попытка входа с гостевым UID');
		}

		$db = DB::singleton();
		$sid = $db->create_uniq_hash_key_range('sid', 'session', 32, 32);

		// expires
		$expire = time() + 1209600;
		$dbExpire = 'NOW() + INTERVAL 14 DAY';

  		$db->query("DELETE FROM session WHERE sid=? AND uid=?", $sid, $uid);
	   	$db->query("INSERT INTO session VALUES(?, ?, INET_ATON(?), $dbExpire, ?, ?, ?, ?, ?)", $sid, $uid, ami_GetIP(), $email, $is_admin, $email, $check_ip, $logout_url);

		// set login cookie
		$login_hash = base64_encode($uid.'|'.$sid.'|'.$expire.'|'.sha1($ami_LoginCookieSalt.$uid.$sid.$expire));
		ami_SetCookie($ami_LoginCookieName, $login_hash, $expire);

		return $login_hash;
	}


	public function logout() {
		global $ami_LoginCookieName, $ami_LoginCookieSalt;

		if (!isset($_COOKIE[$ami_LoginCookieName])) {
			return FALSE;
		}

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
		$result = $db->numRows('SELECT sid FROM session WHERE sid=? AND uid=? LIMIT 1', $sid, $uid);
		if ($result !== 1) {
			return FALSE;
		}

		// all OK
		// 1. delete from session DB
		$db->query('DELETE FROM session WHERE sid=? AND uid=? LIMIT 1', $sid, $uid);

		// 2. set logouted cookie
		$expire += 1209600;
		$randomSID = ami_GenerateRandomHash(32);

		//
		$login_hash = base64_encode('0|'.$randomSID.'|'.$expire.'|'.sha1($ami_LoginCookieSalt.'0'.$randomSID.$expire));
		ami_SetCookie($ami_LoginCookieName, $login_hash, $expire);
	}


	public function logged($login_hash=FALSE, $try_facebook=TRUE) {
		global $ami_LoginCookieName, $ami_LoginCookieSalt, $ami_UseFacebook;

		$ip = ami_GetIP();

		//
		do {
			if (!$login_hash && !isset($_COOKIE[$ami_LoginCookieName])) {
				break;
			}

			// HASH FROM PARAM or COOKIE?
			if ($login_hash) {
				list($uid, $sid, $expire, $checksum) = explode('|', base64_decode($login_hash), 4);
			} else {
				list($uid, $sid, $expire, $checksum) = explode('|', base64_decode($_COOKIE[$ami_LoginCookieName]), 4);
			}

			// safe data
			$uid = intval($uid, 10);
			$expire = intval($expire, 10);

			// logouted cookie?
			if ($uid === 0) {
				break;
			}

			// check checksum
			if ($checksum != sha1($ami_LoginCookieSalt.$uid.$sid.$expire)) {
				$log = Logger::singleton();
				$log->info('logged(). Invalid cookie checksum');
				break;
			}

			$db = DB::singleton();

			// delete all expires from session DB
			$db->query('DELETE FROM session WHERE expire < NOW()');

			// check sid
			$row = $db->getRow('SELECT sid,INET_NTOA(ip) AS ip,check_ip FROM session WHERE sid=? AND uid=? LIMIT 1', $sid, $uid);
			if (!$row) {
				break;
			}

			// CHECK IP?
			if (intval($row['check_ip'], 10) === 1) {
				if ($row['ip'] != $ip) {
					break;
				}
			}

			// all OK
			// 1. update expire on DB and Cookie
			$db->query('UPDATE session SET expire=(NOW() + INTERVAL 14 DAY) WHERE sid=? AND uid=? AND ip=INET_ATON(?) LIMIT 1', $sid, $uid, $ip);
			if (!$login_hash) {
				$expire = time() + 1209600;
				ami_SetCookie($ami_LoginCookieName, base64_encode($uid.'|'.$sid.'|'.$expire.'|'.sha1($ami_LoginCookieSalt.$uid.$sid.$expire)), $expire);
			}

			// 2. return SID
			return $sid;
		} while(0);


		// NOT LOGGED - try facebook
		if ($try_facebook) {
			try {
				$fb_me = null;
				$facebook = new Facebook(array('appId' => FACEBOOK_APP_ID,'secret' => FACEBOOK_APP_SECRET,'cookie' => TRUE));
				$fb_session = $facebook->getSession();

				// GET INFO
				if ($fb_session) {
					$fb_uid = $facebook->getUser();
					$fb_me = $facebook->api('/me');
					$fb_logout_url = $facebook->getLogoutUrl(array('next'=> ami_link('logout')));

					// LOGGED ON FACEBOOK
					if ($fb_me) {
						// CHECK IS OUR USER
						$db = DB::singleton();
						$row = $db->getRow('SELECT id,email FROM users WHERE fb_uid=? LIMIT 1', $fb_uid);
						if ($row) {
							// IS OUR USER - try login
							$login_hash = self::login($row['id'], $row['email'], 0, FALSE, $fb_logout_url);

							// CALL LOGGED AGAIN
							return self::logged($login_hash, FALSE);
						}
					}
				}
			} catch (FacebookApiException $e) {
				$log = Logger::singleton();
				$log->error('Logged(). Фейсбук вернул ошибку: '.$e->getMessage());
				return FALSE;
			}
		}

		return FALSE;
	}
}


?>
