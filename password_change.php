<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';

try {
	$ami_PageTitle = 'Изменение пароля';

	if ($ami_User['is_guest']) {
		ami_redirect(ami_link('password_reset'));
		exit();
	}


	$pass_form_action = ami_link('password_change');
	$csrf = ami_MakeFormToken($pass_form_action);
	$async = isset($_GET['async']);


	if (isset($_GET['ok'])) {
		ami_show_message($ami_PageTitle, 'Пароль успешно изменён.');
	}

	$form = <<<FMB
<div class="span-13 last prepend-5 body_block last">
	%s
	<h2>Изменение пароля</h2>

	<form method="post" action="$pass_form_action" name="password_change" accept-charset="utf-8" autocomplete="off">
		<p>
			<input type="hidden" name="form_sent" value="1">
			<input type="hidden" name="csrf_token" value="$csrf">
		</p>
		<div class="formRow">
			<label for="p" id="label_p">Текущий пароль</label><br>
			<input type="text" class="text" id="p" name="p" tabindex="1" maxlength="1024">
		</div>
		<div class="formRow">
			<label for="np" id="label_np">Новый пароль</label><br>
			<input type="text" class="text" id="np" name="np" tabindex="2" maxlength="1024">
		</div>
		<div class="formRow buttons">
			<input class="button" type="submit" name="do" value="Далее" tabindex="3">
		</div>
	</form>
</div>
FMB;

	if (isset($_POST['form_sent'])) {
		// 1. check csrf
		if (!ami_CheckFormToken($csrf)) {
			throw new InvalidInputDataException('Действие заблокировано системой безопасности');
		}

		$password_current = isset($_POST['p']) ? $_POST['p'] : FALSE;
		$password_new = isset($_POST['np']) ? $_POST['np'] : FALSE;

		// CHECK CURRENT PASSWORD
		if ((utf8_strlen($password_current) < 1) || (utf8_strlen($password_current) > 1024)) {
			throw new InvalidInputDataException('Вы ввели некорректный текущий пароль');
		}

		$db = DB::singleton();
		$row = $db->getRow('SELECT password FROM users WHERE id=? LIMIT 1', $ami_User['id']);
		if (!$row) {
			throw new InvalidInputDataException('Вы ввели некорректный текущий пароль');
		}

		// CHECK CURRENT PASSWORD
		$t_hasher = new PasswordHash(12, FALSE);
		if (!$t_hasher->CheckPassword($password_current, $row['password'])) {
			throw new InvalidInputDataException('Вы ввели некорректный текущий пароль');
		}

		// CHECK NEW PASSWORD
		if ((utf8_strlen($password_new) < 1) || (utf8_strlen($password_new) > 1024)) {
			throw new InvalidInputDataException('Вы ввели некорректный новый пароль');
		}

		if ($password_new == $password_current) {
			throw new InvalidInputDataException('Пароли не должны совпадать');
		}

		// OK - CHANGE PASSWORD in DB
		// CREATE NEW PASSWORD
		$t_hasher = new PasswordHash(12, FALSE);
		$cryptNewPassword = $t_hasher->HashPassword($password_new);

		// SAVE new PASS to DB
		$db->query("UPDATE users SET password=? WHERE id=? LIMIT 1", $cryptNewPassword, $ami_User['id']);

		// is async request
		if ($async) {
			ami_async_response(array('error'=> 0, 'message' => ''), AMI_ASYNC_JSON);
		} else {
			ami_redirect(ami_link('password_change_ok'));
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
		ami_addOnDOMReady('AMI.utils.init_form($("form[name=password_change]"));');
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


ami_addOnDOMReady('AMI.utils.init_form($("form[name=password_change]"));');
ami_printPage(sprintf($form, ''));

?>
