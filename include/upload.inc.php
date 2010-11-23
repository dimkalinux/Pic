<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit;
}

define('PIC_UPLOAD_URL', 1);
define('PIC_UPLOAD_FILE', 2);

require AMI_ROOT.'include/image.inc.php';
require AMI_ROOT.'include/upload_base.inc.php';
require AMI_ROOT.'include/upload_file.inc.php';
require AMI_ROOT.'include/upload_url.inc.php';

class Upload {
	private $file;
	private $upload_uid;
	private $upload_delete_key;
	private $_auto_shorten_service;
	private $_auto_shorten_need;
	private $_upload_items;


	public function __construct($upload_items) {
		if (empty($upload_items) || !is_array($upload_items)) {
			throw new Exception('Получен пустой список загрузки');
		}

		$this->_upload_items = $upload_items;
	}


	public function run($user, $reduce_original=0, $upload_type, $api_key_uid, $upload_options) {
		global $pic_MaxUploadSize, $pic_BaseURL, $pic_DefaultPreviewSize;

		$uploaded_return_info = '';
		$num_all_items = count($this->_upload_items);
		$num_ok_items = 0;
		$current_items_num = 0;

		$multi_upload = (bool)/**/($num_all_items > 1);

		// GENERATE UPLAOD GROUP KEY
		$db = DB::singleton();
		$this->upload_uid = $db->create_uniq_hash_key_range('group_id', 'pic', 5, 12);
		$this->upload_delete_key = ami_GenerateRandomHash(8);

		// NEED SHoRTEN LINK?
		if ($user['is_guest']) {
			$this->_auto_shorten_need = FALSE;
			$this->_auto_shorten_service = FALSE;
		} else {
			$this->_auto_shorten_need =  (AMI_User_Info::getConfigValue($user['id'], 'shortener_auto', 0) == 1) ? TRUE : FALSE;
			if ($this->_auto_shorten_need) {
				$this->_auto_shorten_service = AMI_User_Info::getConfigValue($user['id'], 'shortener_service', URL_SHORTENER_BITLY);
			} else {
				$this->_auto_shorten_service = FALSE;
			}
		}

		// NEED CHECK SIZE?
		$skip_check_size = ami_GetOptions($upload_options, UPLOAD_FLAG_SKIP_FILESIZE_CHECK, FALSE);

		// GET API_KEY_ID
		$api_key_id = API_KEY_ID_UNKNOWN;
		if (!empty($api_key_uid)) {
			$api_key = new API_Key;
			$api_key_id = $api_key->get_id_by_key_uid($api_key_uid);
			if (FALSE === $api_key_id) {
				$api_key_id = API_KEY_ID_UNKNOWN;
			}
		}

		// ITERATE upload list
		foreach ($this->_upload_items as $upload_item) {
			try {
				$current_items_num++;

				switch ($upload_type) {
					case PIC_UPLOAD_FILE:
						$uploader = new Upload_file($upload_item, $multi_upload, $user['id'], $this->_auto_shorten_service, $api_key_id, $upload_options);
						break;

					case PIC_UPLOAD_URL:
						$uploader = new Upload_url($upload_item, $multi_upload, $user['id'], $this->_auto_shorten_service, $api_key_id, $upload_options);
						break;

					default:
						throw new Exception('Неизвестный тип загрузки');
				}

				// 2. MAKE FILE LOCAL
				$uploader->proccess_upload();

				// 1. CHECK SIZE
				if ($uploader->getSize() < 1) {
					throw new AppLevelException('Получен пустой файл');
				}

				if ($uploader->getSize() > $pic_MaxUploadSize) {
					throw new AppLevelException('Неверный размер файла');
				}


				// 3. CHECK FORMAT
				if (FALSE === $uploader->isImage()) {
					throw new AppLevelException('Неверный формат файла');
				}

				$upload_storage_info = $this->get_upload_dir();

				$uploadDir = $upload_storage_info['dir'];
				$uploadStorage = $upload_storage_info['storage'];
				$uploadLocation = $upload_storage_info['location'];

				$uploadOriginalFilename = $uploadDir.'/'.$this->get_hash_filename($uploader->getFilename().$uploader->getSize());

				// MOVE original TO STORAGE
				$uploader->move($uploadOriginalFilename);

				//
				$upload_image = new Image($uploadOriginalFilename, $multi_upload);

				//
				$upload_image->setFileExt();
				$uploadHashedFilename = $upload_image->getFileName();
				$uploadFilename = $uploader->getFileName();

				// REDUCE SIZE?
				if ($reduce_original > 0) {
					$upload_image->resizeOriginal($reduce_original);
				}

				// CREATE THUMBS
				$upload_image->process_thumbs();

				// ADD to DB
				$uploaded_return_info = $uploader->save_in_db($uploadLocation, $uploadStorage, $uploadFilename, $uploadHashedFilename, $upload_image->getWidth(), $upload_image->getHeight(), $upload_image->getPreview_Width(), $upload_image->getPreview_Height(), $upload_image->getPreview_Size(), $this->upload_uid, $this->upload_delete_key);

				//
				$num_ok_items++;
			} catch(AppLevelException $e) {
				ami_debug("Catch App local for Exc: $current_items_num $num_all_items $num_ok_items ".$e->getMessage());
				if ($current_items_num >= $num_all_items) {
					// ALL ITEMS PROCCEDED
					if ($num_ok_items < 1) {
						throw new AppLevelException($e->getMessage());
					}
				}
			} catch(Exception $e) {
				ami_debug("Catch Exc local for Exc: $current_items_num $num_all_items $num_ok_items ".$e->getMessage());
				if ($current_items_num >= $num_all_items) {
					// ALL ITEMS PROCCEDED
					if ($num_ok_items < 1) {
						throw new Exception($e->getMessage());
					}
				}
			}
		}


		// CREATE LINK
		if ($num_ok_items > 1) {
			// FOR GUEST make OWNER LINK
			if ($user['is_guest']) {
				$view_uploaded_image_link = ami_link('links_group_image_owner', array($this->upload_uid, $uploaded_return_info['delete_key'], PIC_IMAGE_SIZE_MIDDLE));
			} else {
				$view_uploaded_image_link = ami_link('links_group_image', array($this->upload_uid, PIC_IMAGE_SIZE_MIDDLE));
			}
		} else {
			// FOR GUEST make OWNER LINK
			if ($user['is_guest']) {
				$view_uploaded_image_link = ami_link('links_image_owner', array($uploaded_return_info['key'], $uploaded_return_info['delete_key'], PIC_IMAGE_SIZE_MIDDLE));
			} else {
				$view_uploaded_image_link = ami_link('links_image', array($uploaded_return_info['key'], PIC_IMAGE_SIZE_MIDDLE));
			}
		}

		return array('info' => $uploaded_return_info, 'url' => $view_uploaded_image_link);
	}


	private function get_hash_filename($filename) {
		return hash('crc32', $filename);
	}


	private function get_upload_dir() {
		global $pic_UploadBaseDir;


		$max_try = 32;

		do {
			$storage = $this->get_storage();
			$uploadBaseDir = $pic_UploadBaseDir.$storage;

			$image_path_hash = $this->generate_image_upload_save_path(6);
			$full_dir = $uploadBaseDir.'/'.$image_path_hash;

			if (is_dir($full_dir)) {
				$max_try--;
				continue;
			} else {
				// CREATE DIR
				if (mkdir($full_dir, 0700)) {
					return array('dir' => $full_dir, 'storage' => $storage, 'location' => $image_path_hash);
				}
			}
		} while ($max_try > 0);

		return FALSE;
	}


	private function generate_image_upload_save_path($maxLength=null) {
	    return ami_GenerateRandomHash($maxLength);
	}


	private function get_storage() {
		global $pic_UploadStorages, $pic_UploadBaseDir;

		$storage = array_rand(array_flip($pic_UploadStorages), 1);
		$fullUploadDir = $pic_UploadBaseDir.$storage;

		if (!is_dir($fullUploadDir)) {
			throw new Exception("Upload base dir '$fullUploadDir' not exists");
		}

		return $storage;
	}
}

?>
