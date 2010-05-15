<?php

if (!defined('UP_ROOT')) {
	define('UP_ROOT', './');
}

require UP_ROOT.'functions.inc.php';



$key_id = isset($_GET['k']) ? get_safe_string_len($_GET['k'], 16) : FALSE;

// build info
try {
	if (!$key_id) {
		throw new AppLevelException('Недостаточно параметров в запросе');
	}


	$db = DB::singleton();
	$row = $db->getRow("SELECT * FROM pic WHERE id_key=? LIMIT 1", $key_id);

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
$file_date = $row['uploaded'];

$o_size = $row['size'];
$o_size_text = format_filesize($row['size']);
$p_size = $row['p_size'];
$p_size_text = format_filesize($row['p_size']);

$o_width = $row['width'];
$o_height = $row['height'];
$p_width = $row['p_width'];
$p_height = $row['p_height'];

$home_link = ami_link('root');
$show_link = ami_link('show_image', $key_id);
$view_link = ami_link('view_image', array($key_id, IMAGE_SIZE_MIDDLE));
$preview_link = pic_getImageLink($storage, $location, $hash_filename, IMAGE_SIZE_PREVIEW);
$original_link = pic_getImageLink($storage, $location, $hash_filename, IMAGE_SIZE_ORIGINAL);
//

$out = <<<FMB
	<div id="header">
		<!--<span class="text-cap">$filename</span>-->
		<ul class="inline tabs" id="image_info">
			<li><a href="$home_link" title="Вернуться на главную страницу">На главную</a></li>
			<li><a href="$view_link" title="Получить ссылки на этот файл">Ссылки</a></li>
			<!--<li>{$p_width}x{$p_height} $p_size_text</li>-->
			<li><a href="$original_link" title="Скачать оригинал">{$o_width}x{$o_height} $o_size_text</a></li>
		</ul>
	</div>
	<div id="img_block">
		<img class="fancy_image" src="$preview_link" alt="$filename"/>
	</div>
FMB;

ami_printPage($out, 'show_page');
exit();

?>
