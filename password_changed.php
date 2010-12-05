<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';

$ami_PageTitle = 'Изменение пароля';

if ($ami_User['is_guest']) {
	ami_redirect(ami_link('login'));
	exit();
}

ami_show_message('Изменение пароля', 'Вы первый раз вошли с новым паролем.<br>Вы можете изменить его на более удобный или оставить таким.<br><a href="'.ami_link('password_change').'">Изменить пароль</a>');

?>
