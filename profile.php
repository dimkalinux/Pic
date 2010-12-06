<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';

try {
	if ($ami_User['is_guest']) {
		throw new AppLevelException('Для доступа к этой странице необходимо <a href="'.ami_link('login').'">войти в систему</a>');
	}


	$header = ami_htmlencode($ami_User['profile_name']);
	$logout_link = '<a href="'.$ami_User['logout_link'].'">Выйти из системы</a>';
	$settings_link = '<a href="'.ami_link("settings").'">Настройки</a>';
	$password_change_link = '<a href="'.ami_link("password_change").'">Изменить пароль</a>';


	// LOGIN BLOCK
	$session_info_block = '';
	$db = DB::singleton();
	$row = $db->getRow('SELECT sid,INET_NTOA(ip) AS ip,check_ip FROM session WHERE sid=? AND uid=? LIMIT 1', $ami_User['sid'], $ami_User['id']);
	if ($row) {
		if (intval($row['check_ip'], 10) === 1) {
			$session_info_block = 'Вход в систему с айпи-адреса '.$row['ip'].' (c привязкой)';
		} else {
			$session_info_block = 'Вход в систему с айпи-адреса '.$row['ip'].' (без привязки)';
		}
	}

	// FACEBOOK
	$facebook_connect_link = '';
	$fb_info = AMI_User_Info::getUserFB_info($ami_User['id']);

	if (!$fb_info) {
		$facebook_profile_block = '<a href="'.ami_link('profile_facebook').'">Привязать акаунт к Фейсбуку</a><br/>';
	} else {
		// FACEBOOK LOGOUT LINK
		$facebook_profile_block = 'Используется акаунт <a title="Перейти в Фейсбук" href="'.ami_htmlencode($fb_info['link']).'">'.ami_htmlencode($fb_info['name']).'</a>';
	}


	// NUM SESSIONS
	$num_active_sessions = $db->numRows('SELECT sid FROM session WHERE uid=?', $ami_User['id']);
	$session_info_block .= '<br>'.$num_active_sessions.' '.ami_Pon($num_active_sessions, 'активная сессия', 'активных сессии', 'активных сессий');



	$db = DB::singleton();
	$row = $db->getRow("SELECT COUNT(*) AS n, SUM(size) AS s FROM pic WHERE owner_id=?", $ami_User['id']);
	if (!$row) {
		throw new AppLevelException('Неизвестный пользователь');
	}

	$num_files = $row['n'];
	$num_bytes = ami_format_filesize($row['s']);

	$myfiles_link = '';
	if ($num_files > 0) {
		$myfiles_link = '<a href="'.ami_link('myfiles').'">Все мои файлы</a>';
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


$out = <<<FMB
	<div class="span-15 last prepend-5 body_block">
		<h2>$header</h2>

		<h3>Статистика</h3>

		<p>
			Загруженных файлов: $num_files<br>
			Используется: $num_bytes
		</p>

		<p>
			$myfiles_link
		</p>

		<h3>Действия</h3>
		<p>
			$settings_link<br>
			$password_change_link
		</p>

		<h3>Фейсбук</h3>
		<p>
			$facebook_profile_block
		</p>

		<h3>Сессия</h3>
		$session_info_block
		<p><br/>$logout_link</p>
	</div>
FMB;

// SET PAGE TITLE as FILENAME
$ami_PageTitle = 'Мой профиль';
ami_printPage($out, 'myfiles_page');
?>
