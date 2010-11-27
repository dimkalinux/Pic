<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';

// GET INPUT DATA
$page = isset($_GET['p']) ? intval($_GET['p'], 10) : 1;

$myfiles_page_template = '<div class="myfiles span-15 last prepend-5 body_block"><div id="trash_block"><div id="trash_status"></div></div><h2>Мои файлы</h2>%s</div>';

// build info
try {
	if ($ami_User['is_guest']) {
		throw new AppLevelException('Для доступа к этой странице необходимо войти в систему');
	}

	list($images, $pages) = get_user_files($ami_User['id'], $page);
	$images .= $pages;
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
//
ami_addScript('jquery-ui-1.8.5.custom.min.js');
ami_addOnDOMReady('PIC.trash.init();');
//
ami_printPage(sprintf($myfiles_page_template, $images), 'myfiles_page');



function get_user_files($user_id, $page) {
	$num_pages = $start_from = $finish_at = $images = 0;

	$searchResultsNum = 42;


	if ($page < 1) {
		$page = 1;
	}

	// GET NUM ROWS
	$db = DB::singleton();
	$row = $db->getRow("SELECT COUNT(*) AS NUM FROM pic WHERE owner_id=?", $user_id);
	$results_num = intval($row['NUM'], 10);

	$num_pages = ceil($results_num / $searchResultsNum);
	if ($page > $num_pages) {
		$page = 1;
	}

	$start_from = $searchResultsNum * ($page - 1);
	$finish_at = min(($start_from + $searchResultsNum), ($results_num));

	$data = $db->getData("SELECT * FROM pic WHERE owner_id=? ORDER BY id DESC LIMIT $start_from, $searchResultsNum", $user_id);
	if ($data) {
		$images = '<p id="no_files_message" class="hide">У вас еще нет ни одного загруженного файла</p>';
		foreach ($data as $pic) {
			$images .= '<a title="'.ami_htmlencode($pic['filename']).'"" href="'.ami_link('links_image_owner', array($pic['id_key'], $pic['delete_key'], PIC_IMAGE_SIZE_MIDDLE)).'"><img id="img_'.$pic['id_key'].'" rel="'.ami_link('delete_image', array($pic['id_key'], $pic['delete_key'])).'" src="'.pic_getImageLink($pic['storage'], $pic['location'], $pic['hash_filename'], PIC_IMAGE_SIZE_GALLERY).'" alt="'.ami_htmlencode($pic['filename']).'"></a>';
		}
	} else {
		$images = '<p id="no_files_message">У вас еще нет ни одного загруженного файла</p>';
	}

	// CREATE PAGES BLOCK
	$pages = ami_paginate($num_pages, $page, create_function('$page', 'return ami_link("myfiles_page", $page);'), '');

	return array($images, $pages);
}




?>
