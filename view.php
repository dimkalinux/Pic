<?php

if (!defined('UP_ROOT')) {
	define('UP_ROOT', './');
}

require UP_ROOT.'functions.inc.php';



$key_id = isset($_GET['k']) ? get_safe_string_len($_GET['k'], 16) : FALSE;
$key_delete = isset($_GET['d']) ? get_safe_string_len($_GET['d'], 16) : FALSE;
$preview_size = isset($_GET['s']) ? intval($_GET['s'], 10) : PREVIEW_SIZE_MIDDLE;

// build info
try {
	if (!$key_id || !$key_delete) {
		throw new AppLevelException('Недостаточно параметров в запросе');
	}


	$db = DB::singleton();

	if ($key_delete) {
		$row = $db->getRow("SELECT * FROM pic WHERE id_key=? AND delete_key=? LIMIT 1", $key_id, $key_delete);
	} else {
		$row = $db->getRow("SELECT * FROM pic WHERE id_key=? LIMIT 1", $key);
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

$storage = get_safe_string($row['storage']);
$location = get_safe_string($row['location']);
$hash_filename = $row['hash_filename'];
$filename = pic_htmlencode($row['filename']);
$filesize = $row['size'];
$filesize_text = format_filesize($row['size']);
$file_date = $row['uploaded'];

$preview_link = pic_getImageLink($storage, $location, $hash_filename, $preview_size);
$delete_link = ami_link('delete_image', array($key_id, $key_delete));
//
$preview_link_small = ami_link('view_image', array($key_id, $key_delete, IMAGE_SIZE_SMALL));
$preview_link_middle = ami_link('view_image', array($key_id, $key_delete, IMAGE_SIZE_MIDDLE));
$preview_link_preview = ami_link('view_image', array($key_id, $key_delete, IMAGE_SIZE_PREVIEW));
//
$show_link = ami_link('show_image', $key_id);

//
$input_link_html = pic_htmlencode('<a href="'.$show_link.'"><img src="'.pic_getImageLink($storage, $location, $hash_filename, $preview_size).'" alt="'.$filename.'"></a>');
$input_link_bbcode = pic_htmlencode('[url='.$show_link.'][img]'.pic_getImageLink($storage, $location, $hash_filename, $preview_size).'[/img][/url]');
$input_link_original = pic_htmlencode(pic_getImageLink($storage, $location, $hash_filename, IMAGE_SIZE_ORIGINAL));

$out = <<<FMB
	<a href="$show_link"><img class="fancy_image" src="$preview_link" alt="$filename"/></a>

	<ul class="inline tabs" id="image_tabs">
		<li><a href="$preview_link_small">200px</li>
		<li><a href="$preview_link_middle">500px</a></li>
		<li class="separate"><a href="$delete_link">удалить</a></li>
	</ul>

	<div id="links_block">
		<div class="links_row">
			<label for="html">для сайта или блога</label>
			<input size="35" value="$input_link_html" readonly="readonly" type="text" id="html" onclick="this.select()"/>
		</div>

		<div class="links_row">
			<label for="bbcode">для форума</label>
			<input size="35" value="$input_link_bbcode" readonly="readonly" type="text" id="bbcode" onclick="this.select()"/>
		</div>
		<div class="links_row">
			<label for="show">для просмотра</label>
			<input size="35" value="$show_link" readonly="readonly" type="text" id="show" onclick="this.select()"/>
		</div>
		<div class="links_row">
			<label for="original">прямая ссылка на оригинал</label>
			<input size="35" value="$input_link_original" readonly="readonly" type="text" id="original" onclick="this.select()"/>
		</div>
	</div>
FMB;

ami_printPage($out, 'view_page');
exit();

?>
