<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

$ami_PageTitle = 'Твитер конект';

require AMI_ROOT.'functions.inc.php';

try {
	// CHECK TWITTER STATUS
	$twitter_user = new AMI_User_Twitter($ami_User['id']);
	if ($twitter_user->connected()) {
		$twitter_user_tokens = $twitter_user->get_oauth_tokens();

		/* Create a TwitterOauth object with consumer/user tokens. */
		$connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $twitter_user_tokens['oauth_token'], $twitter_user_tokens['oauth_token_secret']);

		/* If method is set change API call made. Test is called by default. */
		$twitter_user_info = $connection->get('account/verify_credentials');
		if (200 === $connection->http_code) {
			$page = <<<FMB
		<div class="span-15 last prepend-5 body_block">
			<h2>$ami_PageTitle</h2>

			<p>Вы присоединены к твитеру</p>
			<h3>Ваш акаунт на твитере</h3>
			<p></p>
		</div>
FMB;
		} else {
			throw new AppLevelException('Error');
		}
	} else {
		$twitter_connect_form_action = ami_link('twitter_connect');
		$csrf = ami_MakeFormToken($twitter_connect_form_action);

		$back_link = ami_link('profile');


		$page = <<<FMB
		<div class="span-15 last prepend-5 body_block">
			<h2>$ami_PageTitle</h2>

			<p>Конект с твитером позволит вам отправлять сообщения с картинками в твитер прямо с этого сайта.</p>
			<form method="post" action="$twitter_connect_form_action" name="twitter" accept-charset="utf-8">
			<p>
				<input type="hidden" name="form_sent" value="1">
				<input type="hidden" name="csrf_token" value="$csrf">
			</p>
			<div class="formRow buttons">
				<input class="button" type="submit" name="do" value="Присоединиться к твитеру" tabindex="1">
				или <a href="$back_link">вернуться</a>
			</div>
			</form>
		</div>
FMB;

	}

	ami_printPage($page, 'twitter_page');
} catch (AppLevelException $e) {
	ami_show_error_message($e->getMessage());
} catch (Exception $e) {
	ami_show_error($e->getMessage());
}
?>
