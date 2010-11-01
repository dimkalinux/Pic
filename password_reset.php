<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';

try {
	$ami_PageTitle = 'Изменение пароля';

	if ($ami_User['is_guest'] === FALSE) {
		ami_redirect(ami_link('password_change'));
		exit();
	}

	$pass_form_action = ami_link('password_reset');
	$csrf = ami_MakeFormToken($pass_form_action);
	$async = isset($_GET['async']);

	// OLD VALUES
	$email_value = isset($_POST['e']) ? ami_htmlencode($_POST['e']) : '';

	if (isset($_GET['ok'])) {
		ami_show_message($ami_PageTitle, 'На указанный вами адрес выслан новый пароль.<br>Дождитесь письма и заходите с новым паролем.');
	}



	$form = <<<FMB
<div class="span-13 last prepend-5 body_block last">
	%s
	<h2>Изменение пароля</h2>
	<p>Восстановить забытый пароль невозможно, потому что он&nbsp;хранится в&nbsp;зашифрованном виде.	Но&nbsp;его можно поменять.	Укажите адрес своей электронной почты, с&nbsp;которым вы&nbsp;регистрировались ранее, чтобы получить новый пароль.</p>

	<form method="post" action="$pass_form_action" name="password_reset" accept-charset="utf-8">
		<p>
			<input type="hidden" name="form_sent" value="1">
			<input type="hidden" name="csrf_token" value="$csrf">
		</p>

		<div class="formRow">
			<label for="e" id="label_e">Электронная почта</label><br>
			<input type="text" class="text" id="e" name="e" tabindex="1" maxlength="128" value="$email_value">
		</div>

		<div class="formRow buttons">
			<input class="button" type="submit" name="do" value="Далее" tabindex="2">
		</div>
	</form>
</div>
FMB;

	$email_text = <<<FMB
Новый пароль для входа на сайт: %s


Pic.lg.ua — хостинг картинок
______________________________________________________________________
webmaster@iteam.lg.ua | http://pic.lg.ua
FMB;


	if (isset($_POST['form_sent'])) {
		// 1. check csrf
		if (!ami_CheckFormToken($csrf)) {
			throw new InvalidInputDataException('Действие заблокировано системой безопасности');
		}

		$email = isset($_POST['e']) ? mb_strtolower(ami_trim($_POST['e'])) : FALSE;

		// check email
		if (!ami_IsValidEmail($email)) {
			throw new InvalidInputDataException('Неправильный адрес почты');
		}

		$db = DB::singleton();
		$row = $db->getRow('SELECT id,email FROM users WHERE email=? LIMIT 1', $email);
		if (!$row) {
			throw new InvalidInputDataException('Пользователь с таким адресом не найден');
		}

		$user_id = $row['id'];
		$user_email = $row['email'];

		// CREATE NEW PASSWORD
		$t_hasher = new PasswordHash(12, FALSE);
		$_pass = new Random_Password;
		$password = $_pass->create(16, 22);
		$cryptPassword = $t_hasher->HashPassword($password);

		// DELETE OLD new passwords FOR THIS USER
		$db->query("DELETE FROM users_new_password WHERE uid=?", $user_id);

		// SAVE new PASS to DB
		$db->query("INSERT INTO users_new_password VALUES('', ?, ?, NOW())", $user_id, $cryptPassword);

		// SEND E-MAIL
		ami_SendEmail($user_email, 'Новый пароль', sprintf($email_text, $password));

		// is async request
		if ($async) {
			ami_async_response(array('error'=> 0, 'message' => ''), AMI_ASYNC_JSON);
		} else {
			ami_redirect(ami_link('password_reset_ok'));
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
		ami_addOnDOMReady('AMI.utils.init_form($("form[name=password_reset]"));');
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


ami_addOnDOMReady('AMI.utils.init_form($("form[name=password_reset]"));');
ami_printPage(sprintf($form, ''));

?>
