<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit();
}


class Upload_file extends Upload_base {
	public function __construct($file, $multi_upload, $user_id, $auto_shorten_service, $api_key_id, $upload_options) {
		if (!isset($file)) {
			throw new Exception("Файл '".ami_htmlencode($file)."' не найден");
		}

		foreach (array('upload_name', 'upload_content_type', 'upload_size', 'upload_path') as $key) {
			if (!isset($file[$key]) || !is_scalar($file[$key])) {
				$this->error = UPLOAD_ERR_NO_FILE;
				throw new Exception("В запросе отсутствует поле '$key'");
			}
		}

		$this->name = $file['upload_name'];
		$this->tmpName = $file['upload_path'];
		$this->error = 0;
		$this->user_id = $user_id;
		$this->_auto_shorten_service = $auto_shorten_service;
		$this->_api_key_id = $api_key_id;

		$this->skip_check_size = ami_GetOptions($upload_options, UPLOAD_FLAG_SKIP_FILESIZE_CHECK, FALSE);

		if ($this->skip_check_size) {
			$this->size = 0;
		} else {
			$this->size = $file['upload_size'];
		}
	}

	public function proccess_upload() {
		return;
	}
}


?>
