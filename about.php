<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}


$ami_PageTitle = 'Зачем это всё?';

require AMI_ROOT.'functions.inc.php';
require AMI_ROOT.'header.php';
?>
	<div class="span-15 last prepend-5 body_block">
		<h2>О проекте</h2>

		<p>Pic.lg.ua&nbsp;&mdash; хостинг картинок, позволяет хранить картинки размером до&nbsp;10&nbsp;мегабайт.
		После загрузки картинки на&nbsp;сайт, вы&nbsp;получаете ссылки на&nbsp;ваш файл,
		которые можно опубликовать на&nbsp;форумах, блогах или любых других сайтах.</p>

		<p>Время хранения файлов не&nbsp;ограничено.</p>

		<p>Работает без Адоуб Флеш технологии.<br>В современных браузерах работает мультизагрузка файлов.</p>

		<p>За&nbsp;содержимое файлов отвечают лишь те, кто заливал файл.
		Администрация никак не&nbsp;контролирует их&nbsp;содержимое и&nbsp;не&nbsp;выслушивает
		претензии по&nbsp;этому поводу.</p>

		<p>Сервис предоставляется &laquo;as&nbsp;is&raquo;, администрация не&nbsp;может гарантировать
		работоспособность	сервиса или сохранность файлов. Но&nbsp;мы&nbsp;делаем всё,
		чтобы проблем с&nbsp;файлами не&nbsp;возникало.</p>

		<h3>Дополнительные программы</h3>
		<p><a href="<?php echo ami_link('about_ext'); ?>">Дополнения для браузеров</a></p>

		<h3>Обновление</h3>
		<p><a href="<?php echo ami_link('about_updates'); ?>">Список изменений</a></p>


		<h3>Обратная связь</h3>
		<p>Форум: <a href="http://forum.iteam.ua/topic/19377/">forum.iteam.ua/topic/19377/</a><br>
		Электронная почта: <a href="mailto:webmaster@iteam.lg.ua">webmaster@iteam.lg.ua</a></p>
	</div>
<?php
require AMI_ROOT.'footer.php';
?>
