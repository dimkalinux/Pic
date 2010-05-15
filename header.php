<?php

// Make sure no one attempts to run this script "directly"
if (!defined('UP')) {
	exit;
}

if (empty($page_title)) {
	$page_title = 'ПИК';
}

if (empty($page_name)) {
	$page_name = 'main_page';
}

// Send no-cache headers
header('Expires: Thu, 21 Jul 1977 07:30:00 GMT');	// When yours truly first set eyes on this world! :)
header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');		// For HTTP/1.0 compability
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="ru-RU" dir="ltr">
<head>
	<title><?php echo $page_title; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link rel="stylesheet" type="text/css" href="<?php echo CSS_BASE_URL; ?>style/style.css"/>
	<!--<link rel="icon" type="image/png" href="/favicon.png">-->
	<!--[if IE]><link rel="stylesheet" type="text/css" href="<?php echo CSS_BASE_URL; ?>style/ie_style.css" /><![endif]-->
</head>
<?php flush(); ?>
<body id="<?php echo $page_name; ?>">
<div id="wrap">
	<div id="primary">
<?php define('UP_HEADER', 1); ?>
