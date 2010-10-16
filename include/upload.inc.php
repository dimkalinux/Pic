<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit;
}

require AMI_ROOT.'include/image.inc.php';
require AMI_ROOT.'include/upload_file.inc.php';

class Upload {
	private $file;
	private $upload_uid;
	private $upload_delete_key;
	private $_auto_shorten_service;
	private $_auto_shorten_need;

	public function __construct($files, $async, $user, $use_api, $reduce_original=0) {
		global $pic_MaxUploadSize, $pic_BaseURL, $pic_DefaultPreviewSize;

		$multi_upload = (bool)/**/(count($files) > 1);

		// GENERATE UPLAOD GROUP KEY
		$db = DB::singleton();
		$this->upload_uid = $db->create_uniq_hash_key_range('group_id', 'pic', 4, 12);
		$this->upload_delete_key = ami_GenerateRandomHash(8);

		//
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


		foreach ($files as $file) {
			$upload_file = new Upload_file($file, $multi_upload, $user['id'], $this->_auto_shorten_service);

			// 1. CHECK SIZE
			if ($upload_file->getSize() < 1) {
				throw new AppLevelException('Получен пустой файл');
			}

			if ($upload_file->getSize() > $pic_MaxUploadSize) {
				throw new AppLevelException("Неверный размер файла ");
			}

			// 2. CHECK FORMAT
			if (FALSE === $upload_file->isImage()) {
				throw new AppLevelException('Неверный формат файла1');
			}

			$upload_storage_info = $this->get_upload_dir();

			$uploadDir = $upload_storage_info['dir'];
			$uploadStorage = $upload_storage_info['storage'];
			$uploadLocation = $upload_storage_info['location'];

			$uploadOriginalFilename = $uploadDir.'/'.$this->get_hash_filename($upload_file->getFilename().$upload_file->getSize());

			// 3. MOVE original TO STORAGE
			$upload_file->move($uploadOriginalFilename);

			// 4.
			$upload_image = new Image($uploadOriginalFilename, $multi_upload);

			// 5.
			$upload_image->setFileExt();
			$uploadHashedFilename = $upload_image->getFileName();
			$uploadFilename = $upload_file->getFileName();

			if ($reduce_original > 0) {
				$upload_image->resizeOriginal($reduce_original);
			}

			// 6.
			$upload_image->process_thumbs();

			// 7. ADD to DB
			$uploaded_return_info = $upload_file->save_in_db($uploadLocation, $uploadStorage, $uploadFilename, $uploadHashedFilename, $upload_image->getWidth(), $upload_image->getHeight(), $upload_image->getPreview_Width(), $upload_image->getPreview_Height(), $upload_image->getPreview_Size(), $this->upload_uid, $this->upload_delete_key);
		}

		// CREATE LINK
		if (is_array($files) && count($files) > 1) {
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

		// EXIT
		if ($use_api) {
			// RETURN UPLOADED image INFO
			ami_async_response(array('error'=> 0, 'info' => $uploaded_image_info), AMI_ASYNC_JSON);
		} else {
			if ($async) {
				ami_async_response(array('error'=> 0, 'url' => $view_uploaded_image_link), AMI_ASYNC_JSON);
			} else {
				ami_redirect($view_uploaded_image_link);
			}
		}
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
