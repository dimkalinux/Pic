<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';


$key_group = isset($_GET['g']) ? ami_get_safe_string_len($_GET['g'], 32) : FALSE;
$key_delete = isset($_GET['d']) ? ami_get_safe_string_len($_GET['d'], 32) : FALSE;
$preview_size = isset($_GET['s']) ? intval($_GET['s'], 10) : PREVIEW_SIZE_MIDDLE;
$is_owner = FALSE;

// build info
try {
	if (!$key_group) {
		throw new AppLevelException('Недостаточно параметров в запросе');
	}

	$db = DB::singleton();
	if ($key_delete === FALSE) {
		$data = $db->getData("SELECT * FROM pic WHERE group_id=?", $key_group);
	} else {
		$data = $db->getData("SELECT * FROM pic WHERE group_id=? AND delete_key=?", $key_group, $key_delete);
		$is_owner = TRUE;
	}


	if (!$data) {
		throw new AppLevelException('Ссылка не&nbsp;верна или устарела.<br/>Возможно файл был удалён.');
	}

	// CHECK OWNER
	if (($is_owner === FALSE) && ($ami_User['is_guest'] === FALSE)) {
		$row = $db->getRow("SELECT owner_id FROM pic WHERE group_id=? LIMIT 1", $key_group);
		$is_owner = ($ami_User['id'] === (int) $row['owner_id']);
	}

	// SMALL
	$link = $key_delete ? ami_link('links_group_image_owner', array($key_group, $key_delete, PIC_IMAGE_SIZE_SMALL)) : ami_link('links_group_image', array($key_group, PIC_IMAGE_SIZE_SMALL));
	$preview_link_small = '<li><a href="'.$link.'" title="Маленькая картинка">200px</a></li>';
	if ($preview_size == PIC_IMAGE_SIZE_SMALL) {
		$preview_link_small = '<li>200px</li>';
	}

	// MIDDLE
	$link = $key_delete ? ami_link('links_group_image_owner', array($key_group, $key_delete, PIC_IMAGE_SIZE_MIDDLE)) : ami_link('links_group_image', array($key_group, PIC_IMAGE_SIZE_MIDDLE));
	$preview_link_middle = '<li><a href="'.$link.'" title="Большая картинка">500px</a></li>';
	if ($preview_size == PIC_IMAGE_SIZE_MIDDLE) {
		$preview_link_middle = '<li>500px</li>';
	}

	// GALLERY
	$link = $key_delete ? ami_link('links_group_image_owner', array($key_group, $key_delete, PIC_IMAGE_SIZE_GALLERY)) : ami_link('links_group_image', array($key_group, PIC_IMAGE_SIZE_GALLERY));
	$preview_link_gallery = '<li><a href="'.$link.'" title="Галлерея">Галлерея</a></li>';
	if ($preview_size == PIC_IMAGE_SIZE_GALLERY) {
		$preview_link_gallery = '<li>Галерея</li>';
	}

	// SLIDESHOW
	$link = $key_delete ? ami_link('links_group_image_owner', array($key_group, $key_delete, PIC_IMAGE_SIZE_SLIDESHOW)) : ami_link('links_group_image', array($key_group, PIC_IMAGE_SIZE_SLIDESHOW));
	$preview_link_slideshow = '<li><a href="'.$link.'" title="Слайдшоу">Слайдшоу</a></li>';
	if ($preview_size == PIC_IMAGE_SIZE_SLIDESHOW) {
		$preview_link_slideshow = '<li>Слайдшоу</li>';
	}

	$twitter_link = 'http://twitter.com/home?status='.ami_link('show_group_image', array($key_group));

	$i = $tabindex_html = $tabindex_bbcode = $tabindex_show = $tabindex_original = 0;
	$out = '';

	if ($preview_size == PIC_IMAGE_SIZE_SLIDESHOW) {
		$data = array_reverse($data);
		foreach ($data as $row) {
			//
			$key_id = $row['id_key'];
			$storage = ami_get_safe_string($row['storage']);
			$location = ami_get_safe_string($row['location']);
			$hash_filename = $row['hash_filename'];
			$filename = ami_htmlencode($row['filename']);
			$key_delete = $row['delete_key'];

			$show_group_link = ami_link('show_group_image_slideshow', array($key_group));
			$show_group_text = 'Просмотреть слайдшоу на pic.lg.ua';

			// LINKS
			$input_link_html = ami_htmlencode('<a href="'.$show_group_link.'">'.$show_group_text.'</a>');
			$input_link_bbcode = ami_htmlencode('[url='.$show_group_link.']'.$show_group_text.'[/url]');

			// $gallery_image_url = pic_getImageLink($storage, $location, $hash_filename, PIC_IMAGE_SIZE_GALLERY);
			// $images .= '<img src="'.$gallery_image_url.'" alt="'.$filename.'">';
		}
		$out .= <<<AMI
		<p class="span-14 last append-bottom">
			Выберите необходимые ссылки на <a href="$show_group_link">страницу слайдшоу</a>.
		</p>

		<div class="span-20 last append-bottom prepend-top">
			<div class="span-10 last">
				<div class="links_row">
					<label for="html_$i">для сайта</label>
					<input tabindex="$tabindex_html" class="span-14" size="35" value="$input_link_html" readonly="readonly" type="text" id="html_$i" onclick="this.select()">
				</div>
				<div class="links_row">
					<label for="bbcode_$i">для форума</label>
					<input tabindex="$tabindex_bbcode" class="span-14" size="35" value="$input_link_bbcode" readonly="readonly" type="text" id="bbcode_$i" onclick="this.select()">
				</div>
				<div class="links_row">
					<label for="show_$i">для просмотра</label>
					<input tabindex="$tabindex_show" class="span-14" size="35" value="$show_group_link" readonly="readonly" type="text" id="show_$i" onclick="this.select()">
				</div>
			</div>
		</div>
AMI;

	} else if ($preview_size == PIC_IMAGE_SIZE_GALLERY) {
		// IF GALLERY
		$images = '';

		$data = array_reverse($data);
		foreach ($data as $row) {
			//
			$key_id = $row['id_key'];
			$storage = ami_get_safe_string($row['storage']);
			$location = ami_get_safe_string($row['location']);
			$hash_filename = $row['hash_filename'];
			$filename = ami_htmlencode($row['filename']);
			$key_delete = $row['delete_key'];

			$show_group_link = ami_link('show_group_image_preselect', array($key_group, $key_id));
			$show_group_text = 'Просмотреть '.format_pics(count($data)).' на pic.lg.ua';

			// LINKS
			$input_link_html = ami_htmlencode('<a href="'.$show_group_link.'">'.$show_group_text.'</a>');
			$input_link_bbcode = ami_htmlencode('[url='.$show_group_link.']'.$show_group_text.'[/url]');

			// $gallery_image_url = pic_getImageLink($storage, $location, $hash_filename, PIC_IMAGE_SIZE_GALLERY);
			// $images .= '<img src="'.$gallery_image_url.'" alt="'.$filename.'">';
		}


		$out .= <<<AMI
		<p class="span-14 last append-bottom">
			Код преобразуется в текстовую ссылку, ведущую на <a href="$show_group_link">страницу просмотра</a>
			всех загруженных картинок</a>.
		</p>

		<div class="span-20 last append-bottom prepend-top">
			<div class="span-10 last">
				<div class="links_row">
					<label for="html_$i">для сайта</label>
					<input tabindex="$tabindex_html" class="span-14" size="35" value="$input_link_html" readonly="readonly" type="text" id="html_$i" onclick="this.select()">
				</div>
				<div class="links_row">
					<label for="bbcode_$i">для форума</label>
					<input tabindex="$tabindex_bbcode" class="span-14" size="35" value="$input_link_bbcode" readonly="readonly" type="text" id="bbcode_$i" onclick="this.select()">
				</div>
				<div class="links_row">
					<label for="show_$i">для просмотра</label>
					<input tabindex="$tabindex_show" class="span-14" size="35" value="$show_group_link" readonly="readonly" type="text" id="show_$i" onclick="this.select()">
				</div>
			</div>
		</div>
AMI;

	} else {
		foreach ($data as $row) {
			// INDEX
			$i++;

			// TABINDEX
			$tabindex_html++;
			$tabindex_bbcode++;
			$tabindex_show++;
			$tabindex_original++;

			//
			$key_id = $row['id_key'];
			$storage = ami_get_safe_string($row['storage']);
			$location = ami_get_safe_string($row['location']);
			$hash_filename = $row['hash_filename'];
			$filename = ami_htmlencode($row['filename']);
			$key_delete = $row['delete_key'];

			$preview_link = pic_getImageLink($storage, $location, $hash_filename, PIC_IMAGE_SIZE_SMALL);
			$preview_link_preview = ami_link('links_image_owner', array($key_id, $key_delete, PIC_IMAGE_SIZE_PREVIEW));
			$show_link = ami_link('show_image', $key_id);
			$show_group_link = ami_link('show_group_image_preselect', array($key_group, $key_id));

			// LINKS
			$input_link_html = ami_htmlencode('<a href="'.$show_link.'"><img src="'.pic_getImageLink($storage, $location, $hash_filename, $preview_size).'" alt="'.$filename.'"></a>');
			$input_link_bbcode = ami_htmlencode('[url='.$show_link.'][img]'.pic_getImageLink($storage, $location, $hash_filename, $preview_size).'[/img][/url]');
			$input_link_original = ami_htmlencode(pic_getImageLink($storage, $location, $hash_filename, PIC_IMAGE_SIZE_ORIGINAL));

			$out .= <<<FMB
		<div class="span-20 last append-bottom">
			<div class="span-8">
				<a href="$show_group_link" title="Перейти к просмотру"><img class="fancy_image" src="$preview_link" alt="$filename"></a>
			</div>
			<div class="span-10 last">
				<div class="links_row">
					<label for="html_$i">для сайта</label>
					<input tabindex="$tabindex_html" class="span-10" size="35" value="$input_link_html" readonly="readonly" type="text" id="html_$i" onclick="this.select()">
				</div>
				<div class="links_row">
					<label for="bbcode_$i">для форума</label>
					<input tabindex="$tabindex_bbcode" class="span-10" size="35" value="$input_link_bbcode" readonly="readonly" type="text" id="bbcode_$i" onclick="this.select()">
				</div>
				<div class="links_row">
					<label for="show_$i">для просмотра</label>
					<input tabindex="$tabindex_show" class="span-10" size="35" value="$show_group_link" readonly="readonly" type="text" id="show_$i" onclick="this.select()">
				</div>
				<div class="links_row">
					<label for="original_$i">ссылка на оригинал</label>
					<input tabindex="$tabindex_original" class="span-10" size="35" value="$input_link_original" readonly="readonly" type="text" id="original_$i" onclick="this.select()">
				</div>
			</div>
		</div>
FMB;
		}
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

$delete_link = '';
if ($is_owner === TRUE) {
	$key_delete = $row['delete_key'];
	$delete_link = '<ul id="delete_menu" class="image_menu prepend-top"><li><a href="'.ami_link('delete_group_image', array($key_group, $key_delete)).'" title="Удалить эту группу файлов" id="delete_image"><span class="icon" id="view_delete"></span></a></li></ul>';
}

$page = <<<FMB
<div class="span-3 prepend-1 body_block" id="main_block">
	<ul id="image_menu" class="image_menu">
		$preview_link_small
		$preview_link_middle
		$preview_link_gallery
		$preview_link_slideshow
	</ul>

	<ul id="share_menu" class="image_menu prepend-top">
		<li><a href="http://facebook.com/sharer.php?u=$show_group_link" title="Опубликовать картинки в Фейсбуке">Фейсбук</a></li>
		<li><a href="http://vkontakte.ru/share.php?url=$show_group_link" title="Опубликовать картинки ВКонтакте">Вконтакте</a></li>
		<li><a href="http://twitter.com/home?status=$show_group_link" title="Опубликовать картинки в Твитере">Твитер</a></li>
	</ul>

	$delete_link
</div>

<div class="span-18 prepend-1 last body_block" id="links_group_block">
	$out
</div>
FMB;


ami_addOnDOMReady('PIC.ajaxify.delete_group_image();');
ami_printPage($page, 'links_group_page');

?>
