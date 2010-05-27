<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';


$key_id = isset($_GET['k']) ? ami_get_safe_string_len($_GET['k'], 32) : FALSE;
$key_delete = isset($_GET['d']) ? ami_get_safe_string_len($_GET['d'], 32) : 0;
$preview_size = isset($_GET['s']) ? intval($_GET['s'], 10) : PIC_IMAGE_SIZE_MIDDLE;

// build info
try {
	if (!$key_id) {
		throw new AppLevelException('Недостаточно параметров в запросе');
	}


	$db = DB::singleton();

	if ($key_delete == 0) {
		$row = $db->getRow("SELECT * FROM pic WHERE id_key=? LIMIT 1", $key_id);
	} else {
		$row = $db->getRow("SELECT * FROM pic WHERE id_key=? AND delete_key=? LIMIT 1", $key_id, $key_delete);
	}

	if (!$row) {
		throw new AppLevelException('Ссылка не&nbsp;верна или устарела.<br/>Возможно файл был удалён.');
	}
} catch (AppLevelException $e) {
	if (isset($_POST['async'])) {
		exit(json_encode(array('error'=> 1, 'message' => $error_message)));
	} else {
		ami_show_error_message($e->getMessage());
	}
} catch (Exception $e) {
	if (isset($_POST['async'])) {
		exit(json_encode(array('error'=> 1, 'message' => $error_message)));
	} else {
		ami_show_error($e->getMessage());
	}
}

$storage = ami_get_safe_string($row['storage']);
$location = ami_get_safe_string($row['location']);
$hash_filename = $row['hash_filename'];
$filename = ami_htmlencode($row['filename']);


$home_link = ami_link('root');
$about_link = ami_link('about');
$preview_link = pic_getImageLink($storage, $location, $hash_filename, $preview_size);

$delete_link = '';
if ($key_delete) {
	$delete_link = '<li><a href="'.ami_link('delete_image', array($key_id, $key_delete)).'" title="Удалить это файл" id="delete_image"><span class="icon" id="view_delete"></span></a></li>';
}

$preview_link_small = '<li>200px</li>';
if ($preview_size != PIC_IMAGE_SIZE_SMALL) {
	$preview_link_small = '<li><a href="'.ami_link('links_image_owner', array($key_id, $key_delete, PIC_IMAGE_SIZE_SMALL)).'" title="Маленькая картинка">200px</a></li>';
}

$preview_link_middle = '<li>500px</li>';
if ($preview_size != PIC_IMAGE_SIZE_MIDDLE) {
	$preview_link_middle = '<li><a href="'.ami_link('links_image_owner', array($key_id, $key_delete, PIC_IMAGE_SIZE_MIDDLE)).'" title="Большая картинка">500px</a></li>';
}

//
$preview_link_preview = ami_link('links_image_owner', array($key_id, $key_delete, PIC_IMAGE_SIZE_PREVIEW));
//
$show_link = ami_link('show_image', $key_id);

//
$input_link_html = ami_htmlencode('<a href="'.$show_link.'"><img src="'.pic_getImageLink($storage, $location, $hash_filename, $preview_size).'" alt="'.$filename.'"></a>');
$input_link_bbcode = ami_htmlencode('[url='.$show_link.'][img]'.pic_getImageLink($storage, $location, $hash_filename, $preview_size).'[/img][/url]');
$input_link_original = ami_htmlencode(pic_getImageLink($storage, $location, $hash_filename, PIC_IMAGE_SIZE_ORIGINAL));

$out = <<<FMB
<div class="span-17 prepend-5 last">
	<ul id="menu">
		<li><a href="$home_link" title="Вернуться на главную страницу">На главную</a></li>
		<li><a href="$about_link" title="">О проекте</a></li>
	</ul>
</div>

<div class="span-3 body_block" id="main_block">
	<ul id="image_menu">
		$preview_link_small
		$preview_link_middle
		<li><a href="http://twitter.com/home?status=$show_link" title="Опубликовать картинку в Твиттере">twitter</a></li>
		$delete_link
	</ul>
</div>

<div class="span-16 body_block last" id="links_wrap">
	<div id="img_block">
		<a href="$show_link" title="Перейти к просмотру"><img class="fancy_image" src="$preview_link" alt="$filename"/></a>
	</div>
</div>

<div class="span-17 prepend-5 last" id="links_block">
	<div class="links_row clear">
		<label for="html">для сайта</label>
		<input class="span-12" size="35" value="$input_link_html" readonly="readonly" type="text" id="html" onclick="this.select()"/>
	</div>
	<div class="links_row clear">
		<label for="bbcode">для форума</label>
		<input class="span-12" size="35" value="$input_link_bbcode" readonly="readonly" type="text" id="bbcode" onclick="this.select()"/>
	</div>
	<div class="links_row clear">
		<label for="show">для просмотра</label>
		<input class="span-12" size="35" value="$show_link" readonly="readonly" type="text" id="show" onclick="this.select()"/>
	</div>
	<div class="links_row clear">
		<label for="original" >прямая ссылка на оригинал</label>
		<input class="span-12" size="35" value="$input_link_original" readonly="readonly" type="text" id="original" onclick="this.select()"/>
	</div>
</div>
FMB;

ami_addOnDOMReady('PIC.ajaxify.delete_image();');
ami_printPage($out, 'links_page');
?>
