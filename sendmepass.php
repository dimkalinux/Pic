<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';


$ami_PageTitle = 'Вход в систему';

$login_facebook_form_action = ami_link('login_facebook');

$login_form_action = ami_link('login');
$csrf = ami_MakeFormToken($login_form_action);
$async = isset($_GET['async']);
$register_link = ami_link('register');

// OLD VALUES
$email_value = isset($_POST['e']) ? ami_htmlencode($_POST['e']) : '';


// FACEBOOK PART
$facebook_block = '';
if ($ami_UseFacebook) {
	$facebook_block = <<<FMB
	<p class="span-12 append-6 last">
		<hr>
		Если вы пользователь сервиса Фейсбук, используйте его — регистрация займет 1 секунду!
		<br><fb:login-button perms="email" autologoutlink="true" size="medium" background="white" length="short"></fb:login-button>
	</p>

	<div id="fb-root"></div>
	<script>
		window.fbAsyncInit = function() {
			// Init
			FB.init({ appId: '142764589077335', status: true, cookie: true, xfbml: true });

			// Event
			FB.Event.subscribe('auth.login', function(response) {
				PIC.utils.makeGETRequest('$login_facebook_form_action')
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
}

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
			<input type="text" class="text" id="e" name="e" tabindex="1" maxlength="128" value="$email_value">
		</div>

		<div class="formRow">
			<label for="p" id="label_p">Пароль</label><br>
			<input type="password" class="text" id="p" name="p" tabindex="2" maxlength="128">
		</div>

		<div class="formRow buttons">
			<input type="submit" name="do" value="Войти" tabindex="3">
		</div>
	</form>

	<div class="prepend-top">
		<a href="$register_link">Регистрация</a>
	</div>

	$facebook_block
</div>
FMB;

try {
	if (isset($_POST['form_sent']) || isset($_GET['facebook'])) {
		if (isset($_GET['facebook'])) {
			// Create our Application instance (replace this with your appId and secret).
			$facebook = new Facebook(array('appId' => '142764589077335','secret' => 'b1da5f70416eed03e55c7b2ce7190bd6','cookie' => TRUE,));
			$fb_session = $facebook->getSession();

			$me = null;
			// Session based API call.
			if ($fb_session) {
				$uid = $facebook->getUser();
				$me = $facebook->api('/me');

				if (!$me) {
					throw new InvalidInputDataException('Ошибка на стороне Фейсбука');
				}

				// REGISTER new USER
				$db = DB::singleton();

				// CHECK EMAIL
				$row = $db->getRow('SELECT id FROM users WHERE facebook_uid=? LIMIT 1', $uid);
				if (!$row) {
					// FIRST LOGIN - NOT REGISTERED
					ami_redirect(ami_link('register_facebook'));
				}

				$user_id = $row['id'];

				// LOGIN as FACEBOOK USER
				$o_ami_user = new AMI_User();
				$o_ami_user->facebook_login($user_id, $me['email'], 0, md5($session['session_key']), $uid, $me['name']);

				// EXIT
				if ($async) {
					ami_async_response(array('error'=> 0, 'message' => ''), AMI_ASYNC_JSON);
				} else {
					ami_redirect(ami_link('root'));
				}
			} else {
				throw new InvalidInputDataException('Ошибка на стороне Фейсбука');
			}
		}


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

		// check password twice for FACEBOOked
		if ('-' == $password) {
			throw new InvalidInputDataException('Вы не можете войти с паролем. Используйте вход с Фейсбука ');
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

		// CHECK PASSWORD
		$t_hasher = new PasswordHash(12, FALSE);
		if (!$t_hasher->CheckPassword($password, $user_password_hash)) {
			throw new InvalidInputDataException('Неправильная пара почта-пароль! Авторизоваться не удалось.');
		}

		// LOGIN to SYSTEM
		$o_ami_user = new AMI_User();
		$o_ami_user->login($user_id, $user_email, $is_admin);

		// is async request
		if ($async) {
			ami_async_response(array('error'=> 0, 'message' => ''), AMI_ASYNC_JSON);
		} else {
			ami_redirect(ami_link('root'));
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


ami_addOnDOMReady('PIC.utils.init_form($("form[name=login]"));');
ami_printPage(sprintf($form, ''));

?>
