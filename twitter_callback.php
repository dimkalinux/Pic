<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';

session_start();

/* If the oauth_token is old redirect to the connect page. */
if (isset($_REQUEST['oauth_token']) && $_SESSION['oauth_token'] !== $_REQUEST['oauth_token']) {
	$_SESSION['oauth_status'] = 'oldtoken';
	ami_redirect(ami_link('twitter_clear'));
}

/* Create TwitteroAuth object with app key/secret and token key/secret from default phase */
$connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

/* Request access tokens from twitter */
$access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);
if (empty($access_token)) {
	throw new InvalidInputDataException('123');
}

/* Save the access tokens. Normally these would be saved in a database for future use. */
//$_SESSION['access_token'] = $access_token;
$twitter_user = new AMI_User_Twitter($ami_User['id']);
$twitter_user->add_user_to_db($access_token['oauth_token'], $access_token['oauth_token_secret'], $access_token['user_id'], $access_token['screen_name']);

/* Remove no longer needed request tokens */
unset($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

/* If HTTP response is 200 continue otherwise send to connect page to retry */
if (200 == $connection->http_code) {
  /* The user has been verified and the access tokens can be saved for future use */
  $_SESSION['status'] = 'verified';
  ami_redirect(ami_link('profile'));
} else {
  /* Save HTTP status for error dialog on connnect page.*/
  ami_redirect(ami_link('twitter_clear'));
}

?>
