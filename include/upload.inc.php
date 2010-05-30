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

	public function __construct($files, $async, $user_id) {
		global $pic_MaxUploadSize, $pic_BaseURL, $pic_DefaultPreviewSize;

		$multi_upload = (bool)/**/(count($files) > 1);

		// GENERATE UPLAOD GROUP KEY
		$db = DB::singleton();
		$this->upload_uid = $db->create_uniq_hash_key('group_id', 16, 'pic');
		$this->upload_delete_key = ami_GenerateRandomHash(16);

		foreach ($files as $file) {
			$upload_file = new Upload_file($file, $multi_upload, $user_id);

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
			// 6.
			$upload_image->process_thumbs();

			// 7. ADD to DB
			$uploaded_return_info = $upload_file->save_in_db($uploadLocation, $uploadStorage, $uploadFilename, $uploadHashedFilename, $upload_image->getWidth(), $upload_image->getHeight(), $upload_image->getPreview_Width(), $upload_image->getPreview_Height(), $upload_image->getPreview_Size(), $this->upload_uid, $this->upload_delete_key);
		}

		if (is_array($files) && count($files) > 1) {
			$view_uploaded_image_link = ami_link('links_group_image_owner', array($this->upload_uid, $uploaded_return_info['delete_key'], PIC_IMAGE_SIZE_SMALL));
		} else {
			$view_uploaded_image_link = ami_link('links_image_owner', array($uploaded_return_info['key'], $uploaded_return_info['delete_key'], PIC_IMAGE_SIZE_SMALL));
		}
		if ($async) {
			ami_async_response(array('error'=> 0, 'url' => $view_uploaded_image_link), AMI_ASYNC_JSON);
		} else {
			ami_redirect($view_uploaded_image_link);
		}
	}


	private function get_hash_filename($filename) {
		return hash('crc32', $filename);
	}


	private function get_upload_dir() {
		global $pic_UploadBaseDir;

		$storage = $this->get_storage();
		$max_try = 10;

		$uploadBaseDir = $pic_UploadBaseDir.$storage;

		do {
			$image_path_hash = $this->generate_image_upload_save_path(32);
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
