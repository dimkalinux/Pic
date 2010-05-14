<?php

// Make sure no one attempts to run this script "directly"
if (!defined('UP')) {
	exit;
}


class Upload {
	private $file;

	public function __construct($file) {
		global $picMaxUploadSize;

		$upload_file = new Upload_file($file);

		// 1. CHECK SIZE
		if ($upload_file->getSize() < 1) {
			throw new Exception('Получен пустой файл');
		}

		if ($upload_file->getSize() > $picMaxUploadSize) {
			throw new Exception("Неверный размер файла ");
		}

		// 2. CHECK FORMAT
		if (!$upload_file->isImage()) {
			throw new Exception('Неверный формат файла');
		}

		$upload_storage_info = $this->get_upload_dir();

		$uploadDir = $upload_storage_info['dir'];
		$uploadStorage = $upload_storage_info['storage'];
		$uploadLocation = $upload_storage_info['location'];

		$uploadOriginalFilename = $uploadDir.'/'.$this->get_hash_filename($upload_file->getFilename().$upload_file->getSize());

		// 3. MOVE original TO STORAGE
		$upload_file->move($uploadOriginalFilename);

		// 4.
		$upload_image = new Image($uploadOriginalFilename);

		// 5.
		$upload_image->setFileExt();
		$uploadHashedFilename = $upload_image->getFileName();
		$uploadFilename = $upload_file->getFileName();
		// 6.
		$upload_image->process_thumbs();

		// 7. ADD to DB
		$upload_file->save_in_db($uploadLocation, $uploadStorage, $uploadFilename, $uploadHashedFilename, $upload_image->getWidth(), $upload_image->getHeight());
	}


	private function get_hash_filename($filename) {
		return hash('crc32', $filename);
	}

	private function get_upload_dir() {
		global $picUploadBaseDir;

		$storage = $this->get_storage();
		$max_try = 10;

		$uploadBaseDir = $picUploadBaseDir.$storage;

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
	    return generate_random_hash($maxLength);
	}


	private function get_storage() {
		global $picStorages, $picUploadBaseDir;

		$storage = array_rand(array_flip($picStorages), 1);
		$fullUploadDir = $picUploadBaseDir.$storage;

		if (!is_dir($fullUploadDir)) {
			throw new Exception("Upload base dir '$fullUploadDir' not exists");
		}

		return $storage;
	}
}

?>
