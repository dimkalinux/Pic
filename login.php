<?

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';

$ami_PageTitle = 'Вход в систему';

$login_form_action = ami_link('login');
$csrf = ami_MakeFormToken($login_form_action);
$async = isset($_GET['async']);
$register_link = ami_link('register');

// OLD VALUES
$email_value = isset($_POST['e']) ? ami_htmlencode($_POST['e']) : '';


$form = <<<FMB
<div class="span-10 last prepend-5 body_block last">
	%s
	<h2>Вход в систему</h2>

	<form method="POST" action="$login_form_action" name="login" accept-charset="utf-8">
	<input type="hidden" name="form_sent" value="1"/>
	<input type="hidden" name="csrf_token" value="$csrf"/>

	<div class="formRow">
		<label for="e" id="label_e">Электронная почта</label><br>
		<input type="text" class="text" id="e" name="e" tabindex="1" maxlength="128" minlength="1" required="1" value="$email_value">
	</div>

	<div class="formRow">
		<label for="p" id="label_p">Пароль</label><br>
		<input type="password" class="text" id="p" name="p" tabindex="2" maxlength="128" required="3">
	</div>

	<div class="formRow buttons">
		<input type="submit" name="do" value="Войти" tabindex="3">
	</div>
	</form>

	<div class="prepend-top">
		<a href="$register_link ">Регистрация</a>
	</div>
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
		if (!ami_IsValidEmail($email) || ($password === FALSE)) {
			throw new InvalidInputDataException('Вы ввели неверный пароль ');
		}

		$db = DB::singleton();
		$row = $db->getRow('SELECT id,password,email,admin FROM users WHERE email=? LIMIT 1', $email);
		if (!$row) {
			throw new InvalidInputDataException('Вы ввели неверный пароль ');
		}

		$user_id = $row['id'];
		$user_password_hash = $row['password'];
		$user_email = $row['email'];
		$is_admin = $row['admin'];

		// CHECK PASSWORD
		$t_hasher = new PasswordHash(12, FALSE);
		if (!$t_hasher->CheckPassword($password, $user_password_hash)) {
			throw new InvalidInputDataException('Вы ввели неверный пароль');
		}

		// LOGIN to SYSTEM
		User::login($user_id, $user_email, $is_admin);

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
