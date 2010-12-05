<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';


try {
	$ami_PageTitle = 'Вход в систему';

	$login_facebook_form_action = ami_link('login_facebook');
	$login_form_action = ami_link('login');
	$csrf = ami_MakeFormToken($login_form_action);
	$async = isset($_GET['async']);
	$register_link = ami_link('register');
	$password_reset_link = ami_link('password_reset');

	// OLD VALUES
	$email_value = isset($_POST['e']) ? ami_htmlencode($_POST['e']) : '';
	$dont_check_ip = isset($_POST['i']) ? TRUE : FALSE;

	$check_ip_input = 'value="0"';
	if ($dont_check_ip) {
		$check_ip_input = 'checked="checked" value="1"';
	}


	// MAYBE ALREADY LOGGED IN?
	if (!$ami_User['is_guest']) {
		ami_redirect(ami_link('root'));
	}

	$facebook_app_id = FACEBOOK_APP_ID;
	$facebook_block = <<<FMB
	<div class="span-12 append-6 last prepend-top">
		<hr>
		<p>Если вы пользователь сервиса Фейсбук, используйте его для входа или регистрации на сайте<br/>
		<fb:login-button perms="email" autologoutlink="true" size="medium" background="white" length="short">Войти через Фейсбук</fb:login-button>
		</p>
	</div>

	<div id="fb-root"></div>

	<script>
		window.fbAsyncInit = function() {
			var already = false;

			// Init
			FB.init({ appId: '$facebook_app_id', status: true, cookie: true, xfbml: true });

			// Event
			FB.Event.subscribe('auth.login', function(response) {
				if (!already) {
					already = true;
					document.location = '$login_facebook_form_action';
					return;
				}
			});

			// Event
			FB.Event.subscribe('auth.statusChange', function(response) {
				if (response.status == 'connected') {
					if (!already) {
						already = true;
						document.location = '$login_facebook_form_action';
						return;
					}
				}
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
FMB;


	$form = <<<FMB
<div class="span-10 last prepend-5 body_block last">
	%s
	<h2>Вход в систему</h2>

	<form method="post" action="$login_form_action" name="login" accept-charset="utf-8">
		<p>
			<input type="hidden" name="form_sent" value="1">
			<input type="hidden" name="csrf_token" value="$csrf">
		</p>

		<div class="formRow">
			<label for="e" id="label_e">Электронная почта</label><br>
			<input type="email" class="text" id="e" name="e" tabindex="1" maxlength="128" value="$email_value">
		</div>

		<div class="formRow">
			<label for="p" id="label_p">Пароль</label><br>
			<input type="password" class="text" id="p" name="p" tabindex="2" maxlength="128">
		</div>

		<div class="formRow">
			<label for="i" id="label_i" class="unbold">
				<input type="checkbox" id="i" name="i" tabindex="3" $check_ip_input>
				Не привязываться к айпи-адресу
			</label>
		</div>


		<div class="formRow buttons">
			<input class="button" type="submit" name="do" value="Войти" tabindex="3">
		</div>
	</form>

	$facebook_block

	<div class="prepend-top">
		<a href="$register_link">Регистрация</a><br>
		<a href="$password_reset_link" title="">Изменение пароля</a>
	</div>
</div>
FMB;

	if (isset($_POST['form_sent'])) {
		// 1. check csrf
		if (!ami_CheckFormToken($csrf)) {
			throw new InvalidInputDataException('Действие заблокировано системой безопасности');
		}

		$email = isset($_POST['e']) ? mb_strtolower(ami_trim($_POST['e'])) : FALSE;
		$password = isset($_POST['p']) ? $_POST['p'] : FALSE;


		// check email
		if (!ami_IsValidEmail($email) || ($password === FALSE)) {
			throw new InvalidInputDataException('Неправильная пара почта-пароль! Авторизоваться не удалось.');
		}

		$db = DB::singleton();
		$row = $db->getRow('SELECT id,password,email,admin FROM users WHERE email=? LIMIT 1', $email);
		if (!$row) {
			throw new InvalidInputDataException('Неправильная пара почта-пароль! Авторизоваться не удалось.');
		}

		$user_id = $row['id'];
		$user_password_hash = $row['password'];
		$user_email = $row['email'];
		$is_admin = $row['admin'];
		$redirect_after_login = ami_link('root');

		// CHECK PASSWORD
		$t_hasher = new PasswordHash(12, FALSE);
		if (!$t_hasher->CheckPassword($password, $user_password_hash)) {
			// MAYBE is NEW password?
			$row = $db->getRow('SELECT uid,password FROM users_new_password WHERE uid=? LIMIT 1', $user_id);
			if (!$row) {
				throw new InvalidInputDataException('Неправильная пара почта-пароль! Авторизоваться не удалось.');
			}

			$user_password_hash = $row['password'];
			if ($t_hasher->CheckPassword($password, $user_password_hash)) {
				// VALID new PASSWORD
				$db->query('UPDATE users SET password=? WHERE id=?', $user_password_hash, $user_id);
				$db->query('DELETE FROM users_new_password WHERE uid=?', $user_id);

				//
				$redirect_after_login = ami_link('password_loged_with_new');
			} else {
				throw new InvalidInputDataException('Неправильная пара почта-пароль! Авторизоваться не удалось.');
			}
		}

		// LOGIN to SYSTEM
		$o_ami_user = new AMI_User();
		$o_ami_user->login($user_id, $user_email, $is_admin, !/**/$dont_check_ip);

		// is async request
		if ($async) {
			ami_async_response(array('error'=> 0, 'message' => ''), AMI_ASYNC_JSON);
		} else {
			ami_redirect($redirect_after_login);
		}
	} else 	if (isset($_GET['facebook'])) {
		$fb_me = null;
		$facebook = new Facebook(array('appId' => FACEBOOK_APP_ID,'secret' => FACEBOOK_APP_SECRET,'cookie' => TRUE));
		$fb_session = $facebook->getSession();

		// Session based API call.
		if ($fb_session) {
			try {
				// GET INFO
				$fb_uid = $facebook->getUser();
				$fb_me = $facebook->api('/me');
			} catch (FacebookApiException $e) {
				throw new AppLevelException('Ошибка Фейсбука: '.$e->getMessage());
			}

			if ($fb_me) {
				$db = DB::singleton();

				// CHECK FB_UID
				$row = $db->getRow('SELECT id,email FROM users WHERE fb_uid=? LIMIT 1', $fb_uid);
				if ($row) {
					// LOGIN as FACEBOOK USER
					$o_ami_user = new AMI_User();
					$o_ami_user->login($row['id'], $row['email'], 0, FALSE);

					// EXIT
					if ($async) {
						ami_async_response(array('error'=> 0, 'message' => ''), AMI_ASYNC_JSON);
					} else {
						ami_redirect(ami_link('root'));
					}
				} else {
					// USER NOT REGISTERED
					ami_redirect(ami_link('register_facebook'));
				}
			}
		}
	}
} catch (AppLevelException $e) {
	if ($async) {
		ami_async_response(array('error'=> 1, 'message' => $e->getMessage()), AMI_ASYNC_JSON);
	} else {
		ami_show_error_message($e->getMessage());
	}
} catch (InvalidInputDataException $e) {
	if ($async) {
		ami_async_response(array('error'=> 1, 'message' => $e->getMessage()), AMI_ASYNC_JSON);
	} else {
		ami_addOnDOMReady('AMI.utils.init_form($("form[name=login]"));');
		ami_printPage(sprintf($form, '<div class="span-20"><div class="error span-10 last">'.$e->getMessage().'</div></div>'));
		exit();
	}
} catch (Exception $e) {
	if ($async) {
		ami_async_response(array('error'=> 1, 'message' => $e->getMessage()), AMI_ASYNC_JSON);
	} else {
		ami_show_error($e->getMessage());
	}
}


ami_addOnDOMReady('AMI.utils.init_form($("form[name=login]"));');
ami_printPage(sprintf($form, ''));

?>
