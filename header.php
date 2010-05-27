<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit;
}

if (empty($ami_PageTitle)) {
	$ami_PageTitle = 'Пик';
}

if (defined('AMI_PAGE_TYPE')) {
	$ami_PageType = AMI_PAGE_TYPE;
} else {
	$ami_PageType = 'main_page';
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
	<title><?php echo $ami_PageTitle; ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<link rel="stylesheet" href="<?php echo AMI_CSS_BASE_URL; ?>style/blueprint/screen.css" type="text/css" media="screen, projection">
	<link rel="stylesheet" href="<?php echo AMI_CSS_BASE_URL; ?>style/blueprint/print.css" type="text/css" media="print">
	<!--[if lt IE 8]><link rel="stylesheet" href="<?php echo AMI_CSS_BASE_URL; ?>style/blueprint/ie.css" type="text/css" media="screen, projection"><![endif]-->
	<link rel="stylesheet" href="<?php echo AMI_CSS_BASE_URL; ?>style/style.css" type="text/css" media="screen, projection">
	<link rel="shortcut icon" type="image/x-icon" href="<?php echo AMI_JS_BASE_URL; ?>favicon.ico">
</head>
<?php flush(); ?>
<body id="<?php echo $ami_PageType; ?>">
	<div class="container _showgrid">
<?php define('AMI_HEADER', 1); ?>
