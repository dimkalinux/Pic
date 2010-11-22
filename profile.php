<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';

try {
	$header = ami_htmlencode($ami_User['profile_name']);
	$logout_link = '<a href="'.ami_link("logout").'">Выйти из системы</a>';
	$settings_link = '<a href="'.ami_link("settings").'">Настройки</a>';
	$password_change_link = '<a href="'.ami_link("password_change").'">Изменить пароль</a>';


	//getLogoutUrl
	$ami_logout_url = '';
	if ($ami_UseFacebook && $ami_User['facebook_uid']) {
		$facebook = new Facebook(array('appId' => '142764589077335','secret' => 'b1da5f70416eed03e55c7b2ce7190bd6','cookie' => TRUE));
		$facebook_logout_url = $facebook->getLogoutUrl(array('next'=> ami_link('logout_facebook')));
		$ami_logout_url = ami_link('logout_facebook');

		$logout_link = <<<FMB
	<div>
		<a href="$facebook_logout_url" onclick="FB.logout(); return false;">Выйти из системы</a>
	</div>
FMB;
	}

	// LOGIN BLOCK
	$session_info_block = '';
	$db = DB::singleton();
	$row = $db->getRow('SELECT sid,INET_NTOA(ip) AS ip,check_ip FROM session WHERE sid=? AND uid=? LIMIT 1', $ami_User['sid'], $ami_User['id']);
	if ($row) {
		if (intval($row['check_ip'], 10) === 1) {
			$session_info_block = 'Вход в систему с айпи-адреса '.$row['ip'].' (c привязкой)';
		} else {
			$session_info_block = 'Вход в систему с айпи-адреса '.$row['ip'].' (без привязки)';
		}
	}
	// NUM SESSIONS
	$num_active_sessions = $db->numRows('SELECT sid FROM session WHERE uid=?', $ami_User['id']);
	$session_info_block .= '<br>'.$num_active_sessions.' '.ami_Pon($num_active_sessions, 'активная сессия', 'активных сессии', 'активных сессий');




	// TIWTTER
	$twitter_block = '';
	$twitter_status = '<a href="'.ami_link('twitter').'">Присоединиться к твитеру</a>';
	$mytwits_link = '';
	try {
		$twitter_user = new AMI_User_Twitter($ami_User['id']);
		if ($twitter_user->connected()) {
			$twitter_user_tokens = $twitter_user->get_oauth_tokens();

			/* Create a TwitterOauth object with consumer/user tokens. */
			$connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $twitter_user_tokens['oauth_token'], $twitter_user_tokens['oauth_token_secret']);

			/* If method is set change API call made. Test is called by default. */
			$twitter_user_info = $connection->get('account/verify_credentials');
			if (200 === $connection->http_code) {
				$twitter_status = 'Используется акаунт @<a href="http://twitter.com/'.ami_htmlencode($twitter_user_info->screen_name).'">'.ami_htmlencode($twitter_user_info->screen_name).'</a><br><a href="'.ami_link('twitter_disconnect').'">Отключить твитер</a>';
			}

			$mytwits_link = '<br><a href="'.ami_link('mytwits').'">Все мои твиты</a>';
		}

		$twitter_block = <<<FMB
		<h3>Твитер</h3>
		<p>$twitter_status</p>
FMB;
	} catch (Exception $e) {

	}


// FACEBOOK
$facebook_block = '';
$login_facebook_form_action = '';
if ($ami_UseFacebook) {
	$facebook_block = <<<AMI
		<div id="fb-root"></div>
		<script>
			window.fbAsyncInit = function() {
				// Init
				FB.init({ appId: '142764589077335', status: true, cookie: true, xfbml: true });

				// Event
				FB.Event.subscribe('auth.logout', function(response) {
					PIC.utils.makeGETRequest('$ami_logout_url');
				});
			};

			// LOAD
			(function () {
				var e = document.createElement('script');
				e.src = document.location.protocol + '//connect.facebook.net/ru_RU/all.js';
				e.async = true;
				document.getElementById('fb-root').appendChild(e);
			}());
		</script>
AMI;
}


// build info
	if ($ami_User['is_guest']) {
		throw new AppLevelException('Для доступа к этой странице необходимо <a href="'.ami_link('login').'">войти в систему</a>');
	}

	$db = DB::singleton();
	$row = $db->getRow("SELECT COUNT(*) AS n, SUM(size) AS s FROM pic WHERE owner_id=?", $ami_User['id']);
	if (!$row) {
		throw new AppLevelException('Неизвестныйй пользователь');
	}

	$num_files = $row['n'];
	$num_bytes = ami_format_filesize($row['s']);

	$myfiles_link = '';
	if ($num_files > 0) {
		$myfiles_link = '<a href="'.ami_link('myfiles').'">Все мои файлы</a>';
	}


	// FACEBOOK PART
	$facebook_connect_block = '';
	if ($ami_UseFacebook && empty($ami_User['facebook_uid'])) {
		$facebook_connect_block = <<<FMB
			<h3>Фейсбук</h3>
			<p class="span-10 append-6 last">
				Если вы пользователь сервиса Фейсбук, используйте его для входа — это займет всего 1&nbsp;секунду!
				<br><fb:login-button onlogin="PIC.utils.makeGETRequest('$login_facebook_form_action');" perms="email" autologoutlink="true" size="medium" background="white" length="short"></fb:login-button>
			</p>
FMB;
	}

} catch (AppLevelException $e) {
	if (isset($_POST['async'])) {
		exit(json_encode(array('error'=> 1, 'message' => $error_message)));
	} else {
		ami_show_error_message($e->getMessage());
	}
} catch (Exception $e) {
	if (isset($_POST['async'])) {
		exit(json_encode(array('error'=> 1, 'message' => $error_message)));
	} else {
		ami_show_error($e->getMessage());
	}
}


$out = <<<FMB
	<div class="span-15 last prepend-5 body_block">
		<h2>$header</h2>

		<h3>Статистика</h3>

		<p>
			Загруженных файлов: $num_files<br>
			Используется: $num_bytes<br>
		</p>

		<p>
			$myfiles_link
			$mytwits_link
		</p>

		<!-- FACEBOOK PART -->
		$facebook_connect_block
		$facebook_block

		<!-- TWITTER PART -->
		$twitter_block

		<h3>Действия</h3>
		<p>
			$settings_link<br>
			$password_change_link
		</p>

		<h3>Сессия</h3>
		$session_info_block
		<p>$logout_link</p>
	</div>
FMB;

// SET PAGE TITLE as FILENAME
$ami_PageTitle = 'Мой профиль';
ami_printPage($out, 'myfiles_page');
?>
