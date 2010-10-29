<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';

$header = ami_htmlencode($ami_User['profile_name']);
$logout_link = '<a href="'.ami_link("logout").'">Выйти из системы</a>';
$settings_link = '<a href="'.ami_link("settings").'">Настройки</a>';
$settings_form_action = ami_link("settings_save");
$csrf = ami_MakeFormToken($settings_form_action);


$ok_message = '';
if (isset($_GET['ok'])) {
	$ok_message = '<div class="span-20"><div class="success span-10 last">Настройки сохранены</div></div>';
}

// build info
try {
	if ($ami_User['is_guest']) {
		throw new AppLevelException('Для доступа к этой странице необходимо <a href="'.ami_link('login').'">войти в систему</a>');
	}

	// SAVE?
	if (isset($_POST['form_sent'])) {
		// 1. check csrf
		if (!ami_CheckFormToken($csrf)) {
			throw new InvalidInputDataException('Действие заблокировано системой безопасности');
		}

		//
		$user_selected_service = isset($_POST['settings_short_links_service']) ? intval(ami_trim($_POST['settings_short_links_service']), 10) : URL_SHORTENER_NONE;
		AMI_User_Info::setConfigValue($ami_User['id'], 'shortener_service', $user_selected_service);

		//
		$settings_short_links_auto = isset($_POST['settings_short_links_auto']) ? intval(ami_trim($_POST['settings_short_links_auto']), 10) : 0;
		if ($user_selected_service === URL_SHORTENER_NONE) {
			$settings_short_links_auto = 0;
		}
		AMI_User_Info::setConfigValue($ami_User['id'], 'shortener_auto', $settings_short_links_auto);

		ami_redirect(ami_link('settings_ok'));
	}


	// JUST SHOW PAGE
	$url_shortener_select_items = '';
	$user_prefered_service = AMI_User_Info::getConfigValue($ami_User['id'], 'shortener_service', URL_SHORTENER_BITLY);
	$shorteners = array(
		//URL_SHORTENER_NONE => 'Не использовать',
		URL_SHORTENER_BITLY => 'Bit.ly',
		URL_SHORTENER_TINYURL => 'Tinyurl',
		URL_SHORTENER_CLCK => 'Clck.ru',
	);

	foreach($shorteners as $key => $name) {
		$selected = ($key == $user_prefered_service) ? 'selected' : '';
		$url_shortener_select_items .= '<option '.$selected.' value="'.$key.'">'.$name.'</option>';
	}

	//
	$settings_short_links_auto_checked =  (AMI_User_Info::getConfigValue($ami_User['id'], 'shortener_auto', 0) == 1) ? 'checked' : '';

} catch (AppLevelException $e) {
	if (isset($_POST['async'])) {
		exit(json_encode(array('error'=> 1, 'message' => $error_message)));
	} else {
		ami_show_error_message($e->getMessage());
	}
}  catch (InvalidInputDataException $e) {
	if ($async) {
		ami_async_response(array('error'=> 1, 'message' => $e->getMessage()), AMI_ASYNC_JSON);
	} else {
		ami_printPage(sprintf($form, '<div class="span-20"><div class="error span-10 last">'.$e->getMessage().'</div></div>'));
		exit();
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
		$ok_message
		<h2>Настройки</h2>

		<form method="post" action="$settings_form_action" name="save" accept-charset="utf-8">
			<p>
				<input type="hidden" name="form_sent" value="1">
				<input type="hidden" name="csrf_token" value="$csrf">
			</p>
			<h3>Короткие ссылки</h3>
				<div class="">
					<label for="settings_short_links_service">
					сервис коротких ссылок<br>
					<select id="settings_short_links_service" name="settings_short_links_service">
						$url_shortener_select_items
					</select>
					</label>
				</div>
FMB;

//
if ($user_prefered_service != URL_SHORTENER_NONE) {
	$out .= <<<FMB
				<div class="">
					<label for="settings_short_links_auto">
						автоматически сокращать ссылки
						<input id="settings_short_links_auto" name="settings_short_links_auto" type="checkbox" value="1" $settings_short_links_auto_checked>
					</label>
				</div>
FMB;
}

$out .= <<<FMB
			<hr class="prepend-top">
			<input class="button" type="submit" name="do" value="Сохранить" tabindex="2">
		</form>
	</div>
FMB;

// SET PAGE TITLE as FILENAME
$ami_PageTitle = 'Мой профиль';
ami_printPage($out, 'myfiles_page');
?>
