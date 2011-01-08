<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';


try {
	$ami_PageTitle = 'Обратная связь';

	$feedback_form_action = ami_link('feedback');
	$csrf = ami_MakeFormToken($feedback_form_action);
	$async = isset($_GET['async']);

	// OLD VALUES
	$email_old_value = isset($_POST['e']) ? ami_htmlencode($_POST['e']) : '';
	$message_old_value = isset($_POST['m']) ? ami_htmlencode($_POST['m']) : '';

	// EMAIL PART
	$email_hiiden = $email_block = '';
	if (!$ami_User['is_guest']) {
		$email_hiiden = '<input type="hidden" name="e" value="'.ami_htmlencode($ami_User['email']).'">';
	} else {
		$email_block = '<div class="formRow">
			<label for="e" id="label_e">Ваш e-mail, на который будет выслан ответ</label><br>
			<input type="email" class="text" id="e" name="e" tabindex="2" maxlength="128" value="'.$email_old_value.'">
		</div>';
	}

	$form = <<<FMB
<div class="span-10 last prepend-5 body_block last">
	%s
	<h2>Обратная связь</h2>

	<form method="post" action="$feedback_form_action" name="feedback" accept-charset="utf-8">
		<p>
			<input type="hidden" name="form_sent" value="1">
			<input type="hidden" name="csrf_token" value="$csrf">
			$email_hiiden
		</p>

		<div class="formRow">
			<label for="m" id="label_m">Сообщение</label><br>
			<textarea class="text" id="m" name="m" tabindex="1" row="5">$message_old_value</textarea>
		</div>

		$email_block

		<div class="formRow buttons">
			<input class="button" type="submit" name="do" value="Отправить" tabindex="3">
		</div>
	</form>

</div>
FMB;

	$email_text = <<<FMB
Новое сообщение от пользователя «%s»

%s
FMB;

	if (isset($_GET['ok'])) {
		ami_show_message('Сообщение отправлено', 'Спасибо. Ваше сообщение отправлено администратору сервиса.');
		exit();
	} else 	if (isset($_POST['form_sent'])) {
		// 1. check csrf
		if (!ami_CheckFormToken($csrf)) {
			throw new InvalidInputDataException('Действие заблокировано системой безопасности');
		}

		$ip = $ami_User['ip'];
		$email = isset($_POST['e']) ? mb_substr(mb_strtolower(ami_trim($_POST['e'])), 0, 80) : FALSE;
		$message = isset($_POST['m']) ? mb_substr($_POST['m'], 0, 2000) : FALSE;

		if (!$message) {
			throw new InvalidInputDataException('Напишите хоть пару строчек текста');
		}

		$db = DB::singleton();
		$db->query("INSERT INTO feedback (message,email,ip,date) VALUES(?,?,INET_ATON(?),NOW())", $message, $email, $ip);

		// SEND E-MAIL
		if (!$email) {
			$email = 'которому не надо отвечать';
		}
		ami_SendEmail(PIC_FEEDBACK_EMAIL, 'Обратная связь от pic.lg.ua', sprintf($email_text, $email, $message));

		// is async request
		if ($async) {
			ami_async_response(array('error'=> 0, 'message' => ''), AMI_ASYNC_JSON);
		} else {
			ami_redirect(ami_link('feedback_ok'));
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
		ami_addOnDOMReady('AMI.utils.init_form($("form[name=feedback]"));');
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


ami_addOnDOMReady('AMI.utils.init_form($("form[name=feedback]"));');
ami_printPage(sprintf($form, ''));

?>
