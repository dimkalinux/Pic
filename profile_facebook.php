<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';

$facebook_connect_form_action = ami_link('profile_facebook_connect');
$async = isset($_GET['async']);

$facebook_app_id = FACEBOOK_APP_ID;
$form = <<<FMB
<div class="span-10 last prepend-5 body_block last">
	%s
	<h2>Фейсбук конект</h2>

	<p><p>Сейчас вы&nbsp;входите на&nbsp;сайт с&nbsp;помощью пароля. Вы&nbsp;можете привязать ваш акаунт к&nbsp;Фейсбуку и&nbsp;использовать его для входа.</p>
	<p><a href="https://www.facebook.com/help/?page=730">Подробнее о Фейсбук Конект</a></p>
	</p>
	<hr>
	<fb:login-button perms="email" show-faces="true" autologoutlink="true" size="medium" background="white" length="short">Подключить Фейсбук</fb:login-button>
	<div id="fb-root"></div>
	<script>
		window.fbAsyncInit = function() {
			// Init
			FB.init({ appId: '$facebook_app_id', status: true, cookie: true, xfbml: true });

			// Event
			FB.Event.subscribe('auth.login', function(response) {
				document.location = '$facebook_connect_form_action';
				return;
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
</div>
FMB;

try {
	// build info
	if ($ami_User['is_guest']) {
		throw new AppLevelException('Для доступа к этой странице необходимо <a href="'.ami_link('login').'">войти в систему</a>');
	}

	// INIT FACEBOOK
	$fb_me = null;
	$facebook = new Facebook(array('appId' => FACEBOOK_APP_ID,'secret' => FACEBOOK_APP_SECRET,'cookie' => TRUE));
	$fb_session = $facebook->getSession();

	// CONNECT
	if (isset($_GET['c'])) {
		// Session based API call.
		if ($fb_session) {
			try {
				// GET INFO
				$fb_uid = $facebook->getUser();
				$fb_me = $facebook->api('/me');
				$fb_logout_url = $facebook->getLogoutUrl(array('next'=> ami_link('logout')));
			} catch (FacebookApiException $e) {
				throw new AppLevelException('Фейсбук вернул ошибку: '.$e->getMessage());
			}

			if ($fb_me) {
				$db = DB::singleton();

				// CHECK FB_UID
				$db->query('UPDATE users SET fb_uid=?, fb_link=?, fb_name=? WHERE id=? LIMIT 1', $fb_uid, $fb_me['link'], $fb_me['name'], $ami_User['id']);

				// EXIT
				if ($async) {
					ami_async_response(array('error'=> 0, 'message' => ''), AMI_ASYNC_JSON);
				} else {
					ami_redirect(ami_link('profile'));
				}
			} else {
				throw AppLevelException('Не удалось получить информацию от Фейсбука');
			}
		} else {
			throw AppLevelException('Отсутсвует сесия Фейсбука');
		}
	} else {
		// CHECK MAYBE LOGGED
		if ($fb_session) {
			try {
				// GET INFO
				$fb_uid = $facebook->getUser();
				$fb_me = $facebook->api('/me');
				$fb_logout_url = $facebook->getLogoutUrl(array('next'=> ami_link('logout')));
			} catch (FacebookApiException $e) {
				throw new AppLevelException('Фейсбук вернул ошибку: '.$e->getMessage());
			}

			if ($fb_me) {
				$db = DB::singleton();

				// CHECK FB_UID
				$db->query('UPDATE users SET fb_uid=?, fb_link=?, fb_name=? WHERE id=? LIMIT 1', $fb_uid, $fb_me['link'], $fb_me['name'], $ami_User['id']);

				// EXIT
				if ($async) {
					ami_async_response(array('error'=> 0, 'message' => ''), AMI_ASYNC_JSON);
				} else {
					ami_redirect(ami_link('profile'));
				}
			}
		}
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


// SET PAGE TITLE
$ami_PageTitle = 'Фейсбук конект';
ami_printPage(sprintf($form, ''));
?>
