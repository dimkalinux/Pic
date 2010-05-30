<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';

$header = ami_htmlencode($ami_User['email']);
$logout_link = ami_link('logout');
$myfiles_link = ami_link('myfiles');


// build info
try {
	if ($ami_User['is_guest']) {
		throw new AppLevelException('Для доступа к этой странице необходимо войти в систему');
	}

	$db = DB::singleton();
	$row = $db->getRow("SELECT COUNT(*) AS n, SUM(size) AS s FROM pic WHERE owner_id=?", $ami_User['id']);
	if (!$row) {
		throw new AppLevelException('Неизвестныйй пользователь');
	}

	$num_files = $row['n'];
	$num_bytes = ami_format_filesize($row['s']);

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
	<div class="myfiles span-15 last prepend-5 body_block">
		<h2>$header</h2>
		<h3>Статистика</h3>
			<p>Загруженно файлов: $num_files<br>
			Используется: $num_bytes<br>
			</p>
			<p><a href="$myfiles_link">Просмотреть все мои файлы</a></p>

		<h3>Действия</h3>
		<a href="$logout_link">Выйти из системы</a>
	</div>
FMB;

// SET PAGE TITLE as FILENAME
$ami_PageTitle = 'Мой профиль';
ami_printPage($out, 'myfiles_page');
?>
