<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit();
}

if (empty($ami_PageTitle)) {
	$ami_PageTitle = 'Пик';
}

if (defined('AMI_PAGE_TYPE')) {
	$ami_PageType = AMI_PAGE_TYPE;
} else {
	$ami_PageType = 'main_page';
}

$ami_CurrentPage = basename($_SERVER['PHP_SELF']);

//
$ami_MenuTemplate = '<div class="span-18 last prepend-5 last"><ul id="menu">%s</ul></div>';

// INDEX PAGE
if ($ami_CurrentPage == 'index.php') {
	ami_array_insert($ami_Menu, 0, '<li class="current">На главную</li>', 'root');
} else {
	ami_array_insert($ami_Menu, 0, '<li><a href="'.ami_link('root').'" title="Вернуться на главную страницу">На главную</a></li>', 'root');
}

// ABOUT PAGE
if (in_array($ami_CurrentPage, array('index.php', 'about.php', 'login.php', 'register.php', 'myfiles.php', 'profile.php', 'links.php', 'links_group.php', 'upload.php', 'settings.php'))) {
	if ($ami_CurrentPage == 'about.php') {
		ami_array_insert($ami_Menu, 1, '<li class="current">О проекте</li>', 'about');
	} else {
		ami_array_insert($ami_Menu, 1, '<li><a href="'.ami_link('about').'" title="Зачем это всё?">О проекте</a></li>', 'about');
	}
}

if ($ami_User['is_guest']) {
	if (in_array($ami_CurrentPage, array('index.php', 'about.php', 'login.php', 'register.php'))) {
		// LOGIN
		if ($ami_CurrentPage == 'login.php') {
			$ami_Menu['login'] = '<li class="current">Вход</li>';
		} else {
			$ami_Menu['login'] = '<li><a href="'.ami_link('login').'" title="Войти в систему">Вход</a></li>';
		}

		// REGISTER
		if ($ami_CurrentPage == 'register.php') {
			$ami_Menu['register'] =  '<li class="current">Регистрация</li>';
		}
	}
} else {
	if ($ami_CurrentPage == 'profile.php') {
		$ami_Menu['profile'] = '<li class="current">'.ami_htmlencode($ami_User['profile_name']).'</li>';
	} else {
		$ami_Menu['profile'] = '<li><a href="'.ami_link('profile').'" title="Мой профиль">'.ami_htmlencode($ami_User['profile_name']).'</a></li>';
	}
}

// SEND NO-CACHE HEADERS
header('Expires: Thu, 21 Jul 1977 07:30:00 GMT');	// When yours truly first set eyes on this world! :)
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');		// For HTTP/1.0 compability
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="ru-RU" dir="ltr">
<head>
	<title><?php echo $ami_PageTitle; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" href="<?php echo AMI_CSS_BASE_URL; echo (AMI_PRODUCTION) ? 'css' : 'css'; ?>/style.combined.css" type="text/css" media="screen, projection">
	<!--[if lt IE 8]><link rel="stylesheet" href="<?php echo AMI_CSS_BASE_URL; echo (AMI_PRODUCTION) ? 'c' : 'css'; ?>/blueprint/ie.css" type="text/css" media="screen, projection"><![endif]-->
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo AMI_JS_BASE_URL; ?>favicon.ico">
</head>
<?php flush(); ?>
<body id="<?php echo $ami_PageType; ?>" class="kern">
	<div class="container _showgrid">
<?php
	echo(sprintf($ami_MenuTemplate, implode('', $ami_Menu)));
	define('AMI_HEADER', 1);
?>
