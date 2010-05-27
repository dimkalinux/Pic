<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';
require AMI_ROOT.'header.php';
?>
	<div class="span-15 last prepend-5">
		<ul id="menu">
			<li class="current">На главную</li>
			<li><a href="<?echo ami_link('about'); ?>" title="Скачать оригинал">О проекте</a></li>
		</ul>
	</div>

	<div class="span-15 last prepend-5 body_block">
		<h3>Загрузить картинки на вечное хранение</h3>

		<form method="post" action="/upload/" name="upload" enctype="multipart/form-data" accept-charset="utf-8">
			<div class="formRow">
				<input type="file" name="upload" tabindex="1" multiple="true">
				<input type="submit" name="do" value="Загрузить" tabindex="2">
			</div>
			<div class="formRow">
				<div class="input_description quiet">jpeg, png, gif, tiff, bmp до&nbsp;10&nbsp;мегабайт</div>
			</div>
		</form>
		<div id="upload_status">&nbsp;</div>

		<div id="footer" class="clear prepend-top">
			<ul id="sitenav">
				<li class="copyright">©&nbsp;<? date_default_timezone_set(AMI_CONFIG_TIMEZONE); echo(date("Y")); ?> <a href="http://iteam.ua/">iTeam</a></li>
				<li class="first"><a href="http://portal.iteam.net.ua/">Портал</a></li>
				<li><a href="http://forum.iteam.net.ua/">Форум</a></li>
				<li><a href="http://up.iteam.net.ua/">АП</a></li>
				<li><a href="http://film.lg.ua/">Фильмы</a></li>
				<li><a href="http://serial.iteam.net.ua/">Сериалы</a></li>
				<li><a href="http://hosting.iteam.lg.ua/">Хостинг</a></li>
			</ul>
		</div>
	</div>
<?php

ami_addOnDOMReady('PIC.upload.init();');

require AMI_ROOT.'footer.php';
?>
