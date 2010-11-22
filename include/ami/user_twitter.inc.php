<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit;
}


//
class AMI_User_Twitter {
	private $_user_id;

	function __construct($user_id=AMI_GUEST_UID) {
		if (empty($user_id) || intval($user_id, 10) === AMI_GUEST_UID) {
			throw new Exception('AMI_User_Twitter::connected: не верный user_id');
		}

		$this->_user_id = intval($user_id, 10);
	}

	public function connected() {
		$db = DB::singleton();
		$result = $db->numRows('SELECT pic_user_id FROM twitter_oauth WHERE pic_user_id=?', $this->_user_id);
		return (bool)/**/($result === 1);
	}

	public function get_oauth_tokens() {
		$db = DB::singleton();
		$row = $db->getRow('SELECT oauth_token,oauth_token_secret FROM twitter_oauth WHERE pic_user_id=?', $this->_user_id);
		if (!$row) {
			throw new Exception('AMI_User_Twitter::get_oauth_tokens: токены не найдены');
		}

		return array('oauth_token' => $row['oauth_token'], 'oauth_token_secret' => $row['oauth_token_secret']);
	}

	public function add_user_to_db($oauth_token, $oauth_token_secret, $twitter_uid, $twitter_name) {
		$db = DB::singleton();
		$db->query("INSERT INTO twitter_oauth VALUES(?, ?, ?, ?, ?, NOW())", $this->_user_id, $twitter_uid, $oauth_token, $oauth_token_secret, $twitter_name);
	}

	public function del_user_from_db() {
		$db = DB::singleton();
		$db->query("DELETE FROM twitter_oauth WHERE pic_user_id=?", $this->_user_id);
	}
}


class Post_Twitter {
	public static function get_tweet($twitter_post_id) {
		$cache = Cache::singleton();
		$twitter_response = $cache->get('twitt'.$twitter_post_id);

		if (!$twitter_response) {
			echo 'Not from cache';
			$twitter_connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);
			$twitter_response = $twitter_connection->get('statuses/show', array('id' => $twitter_post_id));
			$cache->add($twitter_response , 'twitt'.$twitter_post_id, 3600);
		}
		return $twitter_response;
	}
}

?>
