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

		<h3>PIC_uploader</h3>
		<p>
			Дополнение позволяет удобно загружать картинки из&nbsp;браузера на&nbsp;наш сервис.
			<ul>
				<li><a href="https://addons.mozilla.org/ru/firefox/addon/247548/">Установить последнюю версию для Фаирфокс</a></li>
				<li><a href="http://pic.lg.ua/misc/ext/pic_uploader/chromium/PIC_uploader_chromium.crx">Установить последнюю версию для Гугл Хрома</a></li>
			</ul>
		</p>
	</div>
<?php
require AMI_ROOT.'footer.php';
?>
