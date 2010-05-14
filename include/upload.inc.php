<?php

// Make sure no one attempts to run this script "directly"
if (!defined('UP')) {
	exit;
}


class Upload {

	public function __construct($file) {
		$upload_file = new Upload_file($file);

		// 1. CHECK SIZE
		if ($upload_file->getSize() < 1) {
			throw new Exception('Получен пустой файл');
		}

		if ($upload_file->getSize() > $picMaxUploadSize) {
			throw new Exception('Неверный размер файла');
		}

		// CHECK FORMAT
		if (!$upload_file->isImage()) {
			throw new Exception('Неверный формат файла');
		}
	}



	public function get_upload_dir() {
		$uploadBaseDir = $this->get_storage();
		$max_try = 10;

		do {
			$image_path_hash = generate_image_upload_save_path(48);
			$full_dir = $uploadBaseDir.'/'.$image_path_hash;

			if (is_dir($full_dir)) {
				$max_try--;
				continue;
			} else {
				// CREATE DIR
				if (mkdir($full_dir, 0700)) {
					return $full_dir;
				}
			}
		} while ($max_try > 0);

		return FALSE;
	}

	private function generate_image_upload_save_path($maxLength=null) {
	    $entropy = '';

	    // try ssl first
	    if (function_exists('openssl_random_pseudo_bytes')) {
	        $entropy = openssl_random_pseudo_bytes(64, $strong);
	        // skip ssl since it wasn't using the strong algo
	        if($strong !== true) {
	            $entropy = '';
	        }
	    }

	    // add some basic mt_rand/uniqid combo
	    $entropy .= uniqid(mt_rand(), true);

	    // try to read from the windows RNG
	    if (class_exists('COM')) {
	        try {
	            $com = new COM('CAPICOM.Utilities.1');
	            $entropy .= base64_decode($com->GetRandom(64, 0));
	        } catch (Exception $ex) { }
	    }

	    // try to read from the unix RNG
	    if (is_readable('/dev/urandom')) {
	        $h = fopen('/dev/urandom', 'rb');
	        $entropy .= fread($h, 64);
	        fclose($h);
	    }

	    $hash = hash('whirlpool', $entropy);
	    if ($maxLength) {
	        return substr($hash, 0, $maxLength);
	    }
	    return $hash;
	}


	private function get_storage() {
		global $picStorages, $picUploadBaseDir;

		$storage_id = array_rand(array_flip($array), $n);
		$fullUploadDir = $picUploadBaseDir.$storage_id;

		if (!is_dir($fullUploadDir)) {
			throw new Exception("Upload base dir '$fullUploadDir' not exists");
		}

		return $fullUploadDir;
	}
}

?>
