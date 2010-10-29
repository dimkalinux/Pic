<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

$ami_PageTitle = 'Дополнения для браузеров';

require AMI_ROOT.'functions.inc.php';
require AMI_ROOT.'header.php';
?>
	<div class="span-15 last prepend-5 body_block">
		<h2><?php echo $ami_PageTitle; ?></h2>

		<h3>Фаирфокс</h3>
		<p>
			<strong>PIC_uploader</strong><br>
			Дополнение позволяет удобно загружать картинки из&nbsp;браузера на&nbsp;наш сервис.
			<br>
			<a href="https://addons.mozilla.org/ru/firefox/addon/247548/">Установить последнюю версию дополнения Pic_uploader</a>
		</p>
	</div>
<?php
require AMI_ROOT.'footer.php';
?>
