<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';

$images = '';
$myfiles_page_template = '<div class="myfiles span-15 last prepend-5 body_block"><h2>Мои файлы</h2>%s</div>';

// build info
try {
	if ($ami_User['is_guest']) {
		throw new AppLevelException('Для доступа к этой странице необходимо войти в систему');
	}

	if ($ami_User['id'] == AMI_GUEST_UID) {
		throw new AppLevelException('Сбой в системе авторизации');
	}

	$db = DB::singleton();
	$data = $db->getData("SELECT * FROM pic WHERE owner_id=? ORDER BY id DESC", $ami_User['id']);

	if ($data) {
		foreach ($data as $pic) {
			$images .= '<a href="'.ami_link('links_image_owner', array($pic['id_key'], $pic['delete_key'], PIC_IMAGE_SIZE_MIDDLE)).'" title=""><img src="'.pic_getImageLink($pic['storage'], $pic['location'], $pic['hash_filename'], PIC_IMAGE_SIZE_GALLERY).'"></a>';
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


// SET PAGE TITLE as FILENAME
$ami_PageTitle = 'Мои файлы';
ami_printPage(sprintf($myfiles_page_template, $images), 'myfiles_page');
?>
