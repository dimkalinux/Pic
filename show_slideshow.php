<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';
define('AMI_PAGE_TYPE', 'slide_show');



$key_group = isset($_GET['g']) ? ami_get_safe_string_len($_GET['g'], 32) : FALSE;

// build info
try {
	if (!$key_group) {
		throw new AppLevelException('Недостаточно параметров в запросе');
	}

	$db = DB::singleton();
	$data = $db->getData("SELECT * FROM pic WHERE group_id=? ORDER BY id", $key_group);

	if (!$data) {
		throw new AppLevelException('Ссылка не&nbsp;верна или устарела.<br/>Возможно файлы были удалёны.');
	}

	// GET main PICTURE
	$galleryBlock = '';

	// JUST SHOW first PIC
	foreach ($data as $pic) {
		$key_id = $pic['id_key'];
		$galleryBlock .= '<span class="images" thumbs_id="'.$pic['id'].'" src="'.pic_getImageLink($pic['storage'], $pic['location'], $pic['hash_filename'], PIC_IMAGE_SIZE_PREVIEW).'" alt="'.ami_htmlencode($pic['filename']).'"></span>';
	}

	// BUILD GALLERY
	$thumbsBlock = '';
	foreach ($data as $pic) {
		$thumbsBlock .= '<a><img thumbs_id="'.$pic['id'].'" src="'.pic_getImageLink($pic['storage'], $pic['location'], $pic['hash_filename'], PIC_IMAGE_SIZE_GALLERY).
				'" alt="'.ami_htmlencode($pic['filename']).'"/></a>';
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


$ami_Menu['links'] = '<li><a href="'.ami_link('links_group_image', array($key_group, PIC_IMAGE_SIZE_SMALL)).'" title="Получить ссылки на эти файлы">Ссылки</a></li>';

//

$out = <<<FMB
<div class="span-24 body_block center last">
	<div id="slideshow_block">$galleryBlock</div>
</div>
<div id="thumbs_block" class="span-24 center last">
	<div id="control"></div>
	$thumbsBlock
</div>
FMB;


ami_addOnWindowReady('PIC.slideshow.init();');
ami_printPage($out, 'show_page');
exit();

?>
