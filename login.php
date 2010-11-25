<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';


try {
	$ami_PageTitle = 'Вход в систему';

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
	if ($ami_User['is_guest'] === FALSE) {
		$logout_link = ami_link('logout');
		$profile_link = '<a href="'.ami_link('profile').'" title="Мой профиль">'.ami_htmlencode($ami_User['profile_name']).'</a>';

		$page = <<<FMB
		<div class="span-16 last prepend-5 body_block last">
			<h2>Вход в систему</h2>

			<p>Вы сейчас залогинены в системе под акаунтом $profile_link</p>
			<p><a href="$logout_link">Выйти из системы</a></p>
		</idv>
FMB;
		ami_printPage($page);
		exit();
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
		$redirect_after_login = ami_link('root');

		// CHECK PASSWORD
		$t_hasher = new PasswordHash(12, FALSE);
		if (!$t_hasher->CheckPassword($password, $user_password_hash)) {
			// MAYBE is NEw password?
			$row = $db->getRow('SELECT uid,password FROM users_new_password WHERE uid=? LIMIT 1', $user_id);
			if (!$row) {
				throw new InvalidInputDataException('Неправильная пара почта-пароль! Авторизоваться не удалось.');
			}

			$user_password_hash = $row['password'];
			if ($t_hasher->CheckPassword($password, $user_password_hash)) {
				// VALID new PASSWORD
				$db->query('UPDATE users SET password=? WHERE id=?', $user_password_hash, $user_id);
				$db->query('DELETE FROM users_new_password WHERE uid=?', $user_id);
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
