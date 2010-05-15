<?php

// Make sure no one attempts to run this script "directly"
if (!defined('UP')) {
	exit;
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



	public function __construct($file) {
		if (!isset($file)) {
			throw new Exception("File '$file' not found.");
		}

		foreach (array('upload_name', 'upload_content_type', 'upload_size', 'upload_path') as $key) {
			if (!isset($file[$key]) || !is_scalar($file[$key])) {
				$this->error = UPLOAD_ERR_NO_FILE;
				throw new Exception("Not set '$key' in upload");
			}
		}

		$this->name = $file['upload_name'];
		$this->size = $file['upload_size'];
		$this->tmpName = $file['upload_path'];
		$this->error = 0;
	}

	public function __destruct() {
		// ???
	}


	public function getSize() {
		return $this->size;
	}

	public function getFilename() {
		return $this->name;
	}


	public function save_in_db($location, $storage, $filename, $hashed_filename, $width, $height, $p_width, $p_height, $p_size) {
		$image_key = $this->create_uniq_hash_key('key', 16);
		$image_delete_key = generate_random_hash(16);
		$image_location = $location;
		$image_storage = $storage;
		$image_filename = $filename;
		$image_hashed_filename = $hashed_filename;
		$image_size = $this->size;
		$image_width = $width;
		$image_height = $height;

		$db = DB::singleton();
		$db->query("INSERT INTO pic VALUES ('', ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", $image_key, $image_delete_key, $image_location, $image_storage, $image_filename, $image_hashed_filename, $image_size, $image_width, $image_height, $p_width, $p_height, $p_size);

		return array('key' => $image_key, 'delete_key' => $image_delete_key);
	}


	private function create_uniq_hash_key($key_name, $key_length) {
		$t = 10;
		$db = DB::singleton();

		do {
			$hash = generate_random_hash($key_length);
			$row = $db->getRow("SELECT COUNT(*) AS N FROM pic WHERE ?=? LIMIT 1", $key_name, $hash);
			if (intval($row['N'], 10) === 0) {
				return $hash;
			}

			$t--;
		} while($t > 0);
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
		chmod($dest, 0644);
	}


	/**
	 * Is uploaded file GIF, PNG or JPEG?
	 * @return bool
	 */
	public function isImage() {
		return in_array($this->getContentType(), array('image/gif', 'image/png', 'image/jpeg'), TRUE);
	}


}

?>
