<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit();
}


class Upload_file {
	/* @var string */
	private $name;

	/* @var string */
	private $type;

	/* @var string */
	private $size;

	/* @var string */
	private $tmpName;

	/* @var int */
	private $error;

	private $user_id;

	private $_auto_shorten_service;


	public function __construct($file, $multi_upload, $user_id, $auto_shorten_service) {
		if (!isset($file)) {
			throw new Exception("Файл '$file' не найден.");
		}

		foreach (array('upload_name', 'upload_content_type', 'upload_size', 'upload_path') as $key) {
			if (!isset($file[$key]) || !is_scalar($file[$key])) {
				$this->error = UPLOAD_ERR_NO_FILE;
				throw new Exception("В запросе отсутствует поле '$key'");
			}
		}

		$this->name = $file['upload_name'];
		$this->size = $file['upload_size'];
		$this->tmpName = $file['upload_path'];
		$this->error = 0;
		$this->user_id = $user_id;
		$this->_auto_shorten_service = $auto_shorten_service;

	}

	public function __destruct() {
		// RM TMP files
		if (is_file($this->tmpName)) {
			@/**/unlink($this->tmpName);
		}
	}

	public function getSize() {
		return $this->size;
	}

	public function getFilename() {
		return $this->name;
	}


	public function save_in_db($location, $storage, $filename, $hashed_filename, $width, $height, $p_width, $p_height, $p_size, $key_group, $key_delete) {
		$db = DB::singleton();
		$image_key = $db->create_uniq_hash_key_range('key', 'pic', 4, 12);

		$image_delete_key = $key_delete;
		$image_location = $location;
		$image_storage = $storage;
		$image_filename = $filename;
		$image_hashed_filename = $hashed_filename;
		$image_size = $this->size;
		$image_width = $width;
		$image_height = $height;

		//
		$image_short_url = '';
		if ($this->_auto_shorten_service !== FALSE) {
			try {
				$url_short = new URL_Shortener($this->_auto_shorten_service);
				$image_short_url = $url_short->shorten(ami_link('show_image', $image_key));
			} catch (Exception $e) {
				$image_short_url = '';
			}
		}

		$db->query("INSERT INTO pic VALUES ('', ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $key_group, $image_key, $image_delete_key, $image_location, $image_storage, $image_filename, $image_hashed_filename, $image_size, $image_width, $image_height, $p_width, $p_height, $p_size, $this->user_id, $image_short_url);

		return array('key' => $image_key, 'delete_key' => $image_delete_key);
	}


	/**
	 * Returns the MIME content type of an uploaded file.
	 * @return string
	 */
	public function getContentType() {
		if ($this->isOk() && $this->type === NULL) {
			$info = getimagesize($this->tmpName);
			if (isset($info['mime'])) {
				$this->type = $info['mime'];

			} elseif (extension_loaded('fileinfo')) {
				$this->type = finfo_file(finfo_open(FILEINFO_MIME), $this->tmpName);

			} elseif (function_exists('mime_content_type')) {
				$this->type = mime_content_type($this->tmpName);
			}

			if (!$this->type) {
				$this->type = 'application/octet-stream';
			}
		}
		return $this->type;
	}


	/**
	 * Is there any error?
	 * @return bool
	 */
	public function isOk() {
		return $this->error === UPLOAD_ERR_OK;
	}


	/**
	 * Move uploaded file to new location.
	 * @param  string
	 * @return HttpUploadedFile  provides a fluent interface
	 */
	public function move($dest) {
		$func = is_uploaded_file($this->tmpName) ? 'move_uploaded_file' : 'rename';
		if (!$func($this->tmpName, $dest)) {
			throw new Exception("Unable to move uploaded file '$this->tmpName' to '$dest'.");
		}

		//
		chmod($dest, 0444);
	}


	/**
	 * Is uploaded file GIF, PNG or JPEG?
	 * @return bool
	 */
	public function isImage() {
		return in_array($this->getContentType(), array('image/gif', 'image/png', 'image/jpeg', 'image/tiff', 'image/bmp'), TRUE);
	}


}

?>
