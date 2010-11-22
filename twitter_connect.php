<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';


try {
	if (isset($_POST['form_sent'])) {
		if (!ami_CheckFormToken(ami_MakeFormToken(ami_link('twitter_connect')))) {
			throw new InvalidInputDataException('Действие заблокировано системой безопасности');
		}

		// CHECK CONFIG

		// CLEAR SESSION
		session_start();
		session_destroy();

		/* Start session and load library. */
		session_start();

		/* Build TwitterOAuth object with client credentials. */
		$connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET);

		/* Get temporary credentials. */
		$request_token = $connection->getRequestToken(TWITTER_OAUTH_CALLBACK);

		/* Save temporary credentials to session. */
		$_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
		$_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

		/* If last connection failed don't display authorization link. */
		switch ($connection->http_code) {
		  case 200:
		    $url = $connection->getAuthorizeURL($token);
		    ami_redirect($url);
		    break;

		  default:
			throw new InvalidInputDataException('Нет доступа к сайту Твитера.<br><a href="'.ami_link('twitter_connect').'">Перезагрузите эту страницу</a> или повторите попытку позже.');
		    break;
		}
	} else {
		throw new InvalidInputDataException('К этой странице запрещён прямой доступ');
	}
} catch (InvalidInputDataException $e) {
	ami_show_error_message($e->getMessage());
}


?>
