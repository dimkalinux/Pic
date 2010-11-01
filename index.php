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
			<input type="hidden" name="api_key" value="9c0bd1dd6c88765017378c8a">
			<div class="formRow">
			<?php
				if (isset($_GET['link'])) {
					$form_desc = 'укажите ссылку на файл';
			?>
				<input type="text" name="url" tabindex="1">
			<?php
				} else {
					$form_desc = 'jpeg, png, gif, tiff, bmp до&nbsp;10&nbsp;мегабайт';
			?>
				<input type="file" name="upload" tabindex="1" multiple="true" accept="image/*">
			<?php } ?>
				<input type="submit" name="do" value="Загрузить" tabindex="2"><br>
				<span class="input_description quiet"><?php echo $form_desc; ?></span>
			</div>
			<div class="formRow">
				<a id="advanced_link" href="<?php if (isset($_GET['advanced'])) { echo ami_link('root'); } else { echo ami_link('advanced_root'); } ?>"><?php if (isset($_GET['advanced'])) { echo 'скрыть настройки'; } else { echo 'настройки'; } ?></a>
			</div>
			<div id="advanced_options" class="<?php if (isset($_GET['advanced'])) { echo ' '; } else { echo 'hide'; } ?>">
				<div class="formRow">
					<label for="reduce_original">уменьшить оригинал до</label><br>
					<div>
						<input id="reduce_original" name="reduce_original" type="text" class="span-1_5" size="4">
						<div id="input_label_desc">пикселей</div>
					</div>
				</div>
			</div>
		</form>
		<div id="upload_status">&nbsp;</div>
		<div id="footer" class="clear prepend-top">
			<ul id="sitenav">
				<li class="copyright">©&nbsp;<a href="http://iteam.ua/">Айтим</a></li>
				<li class="first"><a href="http://portal.iteam.ua/" title="Портал сети Айтим">Портал</a></li>
				<li><a href="http://forum.iteam.ua/" title="Форум сети Айтим">Форум</a></li>
				<li><a href="http://up.iteam.ua/" title="Файлообменный сервис">АП</a></li>
<?php if ($ami_User['geo'] != 'world'): ?>
				<li><a href="http://film.lg.ua/" title="">Фильмы</a></li>
				<li><a href="http://serial.iteam.ua/" title="Сайт популярных сериалов">Сериалы</a></li>
<?php endif; ?>
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
