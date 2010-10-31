<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';



$key_group = isset($_GET['g']) ? ami_get_safe_string_len($_GET['g'], 32) : FALSE;
$key_id = isset($_GET['k']) ? ami_get_safe_string_len($_GET['k'], 32) : FALSE;
$key_delete = isset($_GET['d']) ? ami_get_safe_string_len($_GET['d'], 32) : FALSE;

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
	$mainPictureBlock = '';
	if ($key_id) {
		foreach ($data as $pic) {
			if ($pic['id_key'] != $key_id) {
				continue;
			}

			$mainPictureBlock = '<a href="'.pic_getImageLink($pic['storage'], $pic['location'], $pic['hash_filename'], PIC_IMAGE_SIZE_ORIGINAL).'"><img class="fancy_image" src="'.pic_getImageLink($pic['storage'], $pic['location'], $pic['hash_filename'], PIC_IMAGE_SIZE_PREVIEW).'" alt="'.ami_htmlencode($pic['filename']).'"/></a>';

			// CREATE LINk to ORIGINAL
			$header_original_link = '<li><a id="header_original_link" href="'.pic_getImageLink($pic['storage'], $pic['location'], $pic['hash_filename'], PIC_IMAGE_SIZE_ORIGINAL).'" title="Скачать оригинал">'.$pic['width'].'&#8202;x&#8202;'.$pic['height'].'&nbsp;'.ami_format_filesize($pic['size']).'</a></li>';

			// SET PAGE TITLE as FILENAME
			$ami_PageTitle = ami_htmlencode($pic['filename']);
		}

		if ($mainPictureBlock == '') {
			throw new AppLevelException('Ссылка повреждена');
		}
	} else {
		// JUST SHOW first PIC
		foreach ($data as $pic) {
			$key_id = $pic['id_key'];
			$mainPictureBlock = '<a href="'.pic_getImageLink($pic['storage'], $pic['location'], $pic['hash_filename'], PIC_IMAGE_SIZE_ORIGINAL).'"><img class="fancy_image" src="'.pic_getImageLink($pic['storage'], $pic['location'], $pic['hash_filename'], PIC_IMAGE_SIZE_PREVIEW).'" alt="'.ami_htmlencode($pic['filename']).'"/></a>';

			// CREATE LINk to ORIGINAL
			$header_original_link = '<li><a id="header_original_link" href="'.pic_getImageLink($pic['storage'], $pic['location'], $pic['hash_filename'], PIC_IMAGE_SIZE_ORIGINAL).'" title="Скачать оригинал">'.$pic['width'].'&#8202;x&#8202;'.$pic['height'].'&nbsp;'.ami_format_filesize($pic['size']).'</a></li>';

			// SET PAGE TITLE as FILENAME
			$ami_PageTitle = ami_htmlencode($pic['filename']);
			break;
		}
	}


	// LINKS

	$links_link = ami_link('links_group_image', array($key_group, PIC_IMAGE_SIZE_MIDDLE));
	if (FALSE !== $key_delete) {
		// FULL LINK FOR OWNER
		$links_link = ami_link('links_group_image_owner', array($key_group, $key_delete, PIC_IMAGE_SIZE_MIDDLE));
	}


	// BUILD GALLERY
	$galleryBlock = '';
	foreach ($data as $pic) {
		if ($pic['id_key'] == $key_id) {
			$galleryBlock .= '<a rel="'.pic_getImageLink($pic['storage'], $pic['location'], $pic['hash_filename'], PIC_IMAGE_SIZE_PREVIEW).
				'*'.pic_getImageLink($pic['storage'], $pic['location'], $pic['hash_filename'], PIC_IMAGE_SIZE_ORIGINAL).
				'*'.$pic['width'].
				'*'.$pic['height'].
				'*'.$pic['size'].
				'" href="'.ami_link('show_group_image_preselect', array($pic['group_id'], $pic['id_key'])).
				'"><img class="active" src="'.pic_getImageLink($pic['storage'], $pic['location'], $pic['hash_filename'], PIC_IMAGE_SIZE_GALLERY).
				'" alt="'.ami_htmlencode($pic['filename']).'"/></a>';
			continue;
		}

		$galleryBlock .= '<a rel="'.pic_getImageLink($pic['storage'], $pic['location'], $pic['hash_filename'], PIC_IMAGE_SIZE_PREVIEW).
				'*'.pic_getImageLink($pic['storage'], $pic['location'], $pic['hash_filename'], PIC_IMAGE_SIZE_ORIGINAL).
				'*'.$pic['width'].
				'*'.$pic['height'].
				'*'.$pic['size'].
				'" href="'.ami_link('show_group_image_preselect', array($pic['group_id'], $pic['id_key'])).
				'"><img src="'.pic_getImageLink($pic['storage'], $pic['location'], $pic['hash_filename'], PIC_IMAGE_SIZE_GALLERY).
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


$ami_Menu['links'] = '<li><a href="'.$links_link.'" title="Получить ссылки на эти файлы">Ссылки</a></li>';
$ami_Menu['original'] = $header_original_link;

//

$out = <<<FMB
<div class="span-24 body_block center last">
	<div id="gallery_block">
		$galleryBlock
	</div>

	<div id="img_block">
		$mainPictureBlock
	</div>
</div>
FMB;



ami_addOnDOMReady('PIC.ajaxify.gallery_change_image(); PIC.utils.preload_gallery_images();');
ami_printPage($out, 'show_page');
exit();

?>
