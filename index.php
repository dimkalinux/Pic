<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

$ami_PageTitle = 'Загрузка картинок';

require AMI_ROOT.'functions.inc.php';
require AMI_ROOT.'header.php';
?>
	<div class="span-15 last prepend-5 body_block">
		<h3>Загрузить картинки на вечное хранение</h3>

		<form method="post" action="/upload/" name="upload" enctype="multipart/form-data" accept-charset="utf-8">
			<div class="formRow">
				<input type="file" name="upload" tabindex="1" multiple="true" accept="image/*">
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
				<li class="first"><a href="http://portal.iteam.ua/" title="Портал сети Айтим">Портал</a></li>
				<li><a href="http://forum.iteam.ua/" title="Форум сети Айтим">Форум</a></li>
				<li><a href="http://up.iteam.ua/" title="Файлообменный сервис">АП</a></li>
				<li><a href="http://film.lg.ua/" title="">Фильмы</a></li>
				<li><a href="http://serial.iteam.ua/" title="Сайт популярных сериалов">Сериалы</a></li>
				<li><a href="http://hosting.iteam.lg.ua/" title="">Хостинг</a></li>
			</ul>
		</div>
	</div>
<?php

// JS
ami_addOnDOMReady('PIC.upload.init();');

// FOOTER
require AMI_ROOT.'footer.php';
?>
