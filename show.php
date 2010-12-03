<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

define('PAGE_WITHOUT_JS', 1);
require AMI_ROOT.'functions.inc.php';



$key_id = isset($_GET['k']) ? ami_get_safe_string_len($_GET['k'], 32) : FALSE;
$key_delete = isset($_GET['d']) ? ami_get_safe_string_len($_GET['d'], 32) : FALSE;

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

	$storage = ami_get_safe_string($row['storage']);
	$location = ami_get_safe_string($row['location']);
	$hash_filename = $row['hash_filename'];
	$filename = ami_htmlencode($row['filename']);
	$file_date = $row['uploaded'];

	$o_size = $row['size'];
	$o_size_text = ami_format_filesize($row['size']);
	$p_size = $row['p_size'];
	$p_size_text = ami_format_filesize($row['p_size']);

	$o_width = $row['width'];
	$o_height = $row['height'];
	$p_width = $row['p_width'];
	$p_height = $row['p_height'];

	// GENERATE LINKS
	$show_link = ami_link('show_image', $key_id);
	$short_link = !empty($row['short_url']) ? $row['short_url'] : FALSE;
	$preview_link = pic_getImageLink($storage, $location, $hash_filename, PIC_IMAGE_SIZE_PREVIEW);
	$original_link = pic_getImageLink($storage, $location, $hash_filename, PIC_IMAGE_SIZE_ORIGINAL);

	$twitter_block = '';

	// LINKS
	$links_link = ami_link('links_image', array($key_id, PIC_IMAGE_SIZE_MIDDLE));
	if (FALSE !== $key_delete) {
		// FULL LINK FOR OWNER
		$links_link = ami_link('links_image_owner', array($key_id, $key_delete, PIC_IMAGE_SIZE_MIDDLE));
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

$ami_Menu['links'] = '<li><a href="'.$links_link.'" title="Получить ссылки на этот файл">Ссылки</a></li>';
$ami_Menu['original'] = '<li><a href="'.$original_link.'" title="Скачать оригинал">'.$o_width.'&#8202;x&#8202;'.$o_height.'&nbsp;'.$o_size_text.'</a></li>';


//

$out = <<<FMB
<div class="span-24 body_block center">
	<div id="img_block">
		<a href="$original_link"><img class="fancy_image" src="$preview_link" alt="$filename"/></a>
	</div>
</div>
FMB;

// SET PAGE TITLE as FILENAME
$ami_PageTitle = $filename;

ami_printPage($out, 'show_page');
exit();

?>
