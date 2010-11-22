<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';

try {
	$ami_PageTitle = 'Отключение твитера';

	$form_action = ami_link('twitter_disconnect');
	$csrf = ami_MakeFormToken($form_action);
	$async = isset($_GET['async']);
	$redirect_ok = $back_link = ami_link('profile');

$form = <<<FMB
<div class="span-10 last prepend-5 body_block last">
	<h2>$ami_PageTitle</h2>

	<form method="post" action="$form_action" name="twitter_disconect" accept-charset="utf-8">
		<p>
			<input type="hidden" name="form_sent" value="1">
			<input type="hidden" name="csrf_token" value="$csrf">
		</p>

		<div class="formRow buttons">
			<input class="button" type="submit" name="do" value="Продолжить" tabindex="1">
			или <a href="$back_link">вернуться</a>
		</div>
	</form>
</div>
FMB;

	if (isset($_POST['form_sent'])) {
		// 1. check csrf
		if (!ami_CheckFormToken($csrf)) {
			throw new InvalidInputDataException('Действие заблокировано системой безопасности');
		}

		$twitter_user = new AMI_User_Twitter($ami_User['id']);
		$twitter_user_tokens = $twitter_user->get_oauth_tokens();

		$connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $twitter_user_tokens['oauth_token'], $twitter_user_tokens['oauth_token_secret']);
		$twitter_user_info = $connection->post('account/end_session');

		if (200 !== $connection->http_code) {
			throw new AppLevelException('Нет доступа к сайту Твитера. Повторите попытку позже.');
		}

		$twitter_user->del_user_from_db();

		// is async request
		if ($async) {
			ami_async_response(array('error'=> 0, 'message' => ''), AMI_ASYNC_JSON);
		} else {
			ami_redirect($redirect_ok);
		}
	}

	ami_printPage($form);
} catch (AppLevelException $e) {
	if ($async) {
		ami_async_response(array('error'=> 1, 'message' => $e->getMessage()), AMI_ASYNC_JSON);
	} else {
		ami_show_error_message($e->getMessage());
	}
} catch (Exception $e) {
	if ($async) {
		ami_async_response(array('error'=> 1, 'message' => $e->getMessage()), AMI_ASYNC_JSON);
	} else {
		ami_show_error($e->getMessage());
	}
}



?>
