<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

define('AMI_PAGE_TYPE', 'message_page');

require AMI_ROOT.'functions.inc.php';
require AMI_ROOT.'header.php';
?>
	<div class="span-15 last prepend-5">
		<ul id="menu">
			<li><a href="<?echo ami_link('root'); ?>" title="Вернуться на главную страницу">На главную</a></li>
			<li class="current">О проекте</li>
		</ul>
	</div>

	<div class="span-15 last prepend-5 body_block">
		<h3>О проекте</h3>

		<p>Pic.lg.ua&nbsp;&mdash; хостинг картинок, позволяет хранить картинки размером до&nbsp;10&nbsp;мегабайт.
		После загрузки картинки на&nbsp;сайт, вы&nbsp;получаете ссылки на&nbsp;ваш файл,
		которые можно опубликовать на&nbsp;форумах, блогах или любых других сайтах.</p>

		<p>Время хранения файлов не&nbsp;ограничено.</p>

		<p>За&nbsp;содержимое файлов отвечают лишь те, кто заливал файл.
		Администрация никак не&nbsp;контролирует их&nbsp;содержимое и&nbsp;не&nbsp;выслушивает
		претензии по&nbsp;этому поводу.</p>

		<p>Сервис предоставляется &laquo;as&nbsp;is&raquo;, администрация не&nbsp;может гарантировать
		работоспособность	сервиса или сохранность файлов. Но&nbsp;мы&nbsp;делаем всё,
		чтобы проблем с&nbsp;файлами не&nbsp;возникало.</p>


		<h4>Приватность</h4>
		<p>Логи с&nbsp;информацией о&nbsp;тех, кто загружал или скачивал файлы не&nbsp;существуют.
		Мы&nbsp;не&nbsp;поддерживаем пиратов, но&nbsp;уважаем право людей на&nbsp;анонимность.
		Однако файлы, которые нарушают авторские права, могут быть удалены, по&nbsp;требованию правообладателя.</p>


		<h4>Обратная связь</h4>
		<p>Форум: <a href="http://forum.iteam.ua/topic/19377/">forum.iteam.ua/topic/19377/</a><br>
		Электронная почта: <a href="mailto:webmaster@iteam.lg.ua">webmaster@iteam.lg.ua</a></p>
	</div>
<?php
require AMI_ROOT.'footer.php';
?>
