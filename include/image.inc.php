<?php

// Make sure no one attempts to run this script "directly"
if (!defined('UP')) {
	exit;
}


class Image {
	const JPEG = IMAGETYPE_JPEG;
	const PNG = IMAGETYPE_PNG;
	const GIF = IMAGETYPE_GIF;

	private $image;
	private $format;


	public function __construct($file) {
	    if (!is_file($file)) {
			throw new Exception("File '$file' not found.");
		}

		if (!extension_loaded('gd')) {
			throw new Exception("PHP extension GD is not loaded.");
		}

		$info = @/**/getimagesize($file);
		switch ($format = $info[2]) {
			case self::JPEG:
				$this->format = self::JPEG;
				break;

			case self::PNG:
				$this->format = self::PNG;
				break;

			case self::GIF:
				$this->format = self::GIF;
				break;

			default:
				throw new Exception("Unknown image type or file '$file' not found.");
				break;
		}
	}


	public function __destruct() {
		// REMOVE FILE if EXIST

	}

	public function create_small_thumbs() {

	}

	public function create_thumbs() {

	}

	public function create_preview() {

	}


	private function create_thumbs($width, $height) {

	}
}

?>
