<?

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';

$ami_PageTitle = 'Регистратура';

$register_form_action = ami_link('register');
$csrf = ami_MakeFormToken($register_form_action);
$async = isset($_GET['async']);

// OLD VALUES
$email_value = isset($_POST['e']) ? ami_htmlencode($_POST['e']) : '';

if (isset($_GET['ok'])) {
	ami_show_message('Спасибо', 'Вы успешно зарегистрировались.');
}




$form = <<<FMB
<div class="span-15 last prepend-5 body_block">
	%s
	<h2>Регистратура</h2>
	<p>Используйте адрес электронной почты в качестве логина</p>
	<form method="POST" action="$register_form_action" name="register" accept-charset="utf-8" autocomplete="off">
	<input type="hidden" name="form_sent" value="1"/>
	<input type="hidden" name="csrf_token" value="$csrf"/>

	<div class="formRow">
		<label for="e" id="label_e" class="$emailLabelClass">Электронная почта</label><br>
		<input type="text" class="text" id="e" name="e" tabindex="1" maxlength="128" minlength="1" required="1" value="$email_value">
	</div>

	<div class="formRow">
		<label for="p" id="label_p" class="$passwordLabelClass">Пароль</label><br>
		<input type="text" class="text" id="p" name="p" tabindex="2" maxlength="128" required="3">
	</div>

	<div class="formRow buttons">
		<input type="submit" name="do" value="Зарегистрироваться" tabindex="3">
	</div>
	</form>
</div>
FMB;

try {
	if (isset($_POST['form_sent'])) {
		// 1. check csrf
		if (!ami_CheckFormToken($csrf)) {
			throw new InvalidInputDataException('Действие заблокировано системой безопасности');
		}

		$email = isset($_POST['e']) ? mb_strtolower(ami_trim($_POST['e'])) : FALSE;
		$password = isset($_POST['p']) ? $_POST['p'] : FALSE;


		// check email
		if (!ami_IsValidEmail($email)) {
			throw new InvalidInputDataException('Вы ввели некорректный адрес эл.&nbsp;почты');
		}

		// check password
		if ((utf8_strlen($password) < 1) || (utf8_strlen($password) > 1024)) {
			throw new InvalidInputDataException('Вы ввели некорректный пароль');
		}


		$db = DB::singleton();
		$result = $db->numRows('SELECT id FROM users WHERE email=? LIMIT 1', $email);
		if ($result !== 0) {
			throw new InvalidInputDataException('Такой адрес эл.&nbsp;почты уже зарегистрирован');
		}

		$t_hasher = new PasswordHash(12, FALSE);
		$cryptPassword = $t_hasher->HashPassword($password);

		$db->query("INSERT INTO users VALUES('', ?, ?, NOW(), 0)", $email, $cryptPassword);
		$user_id = $db->lastID();

		// MAKE LOGIN
		User::login($user_id, $email, 0);

		// is async request
		if ($async) {
			ami_async_response(array('error'=> 0, 'message' => ''), AMI_ASYNC_JSON);
		} else {
			ami_redirect(ami_link('register_ok'));
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

ami_addOnDOMReady('PIC.utils.init_form($("form[name=register]"));');
ami_printPage(sprintf($form, ''));

?>
