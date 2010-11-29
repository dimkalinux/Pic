<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit;
}


class Image_Resizer {
	//
	protected $src_file;
	protected $dst_file;
	protected $width;
	protected $height;
	protected $quality;
	protected $format;
	protected $tmp_file;


	public function __destruct() {
		// REMOVE TMP FILE
		$this->removeTempFile();
	}


	public function set_SourceFilename($filename) {
		//
		if (!file_exists($filename)) {
			throw new Exception('файл не существует');
		}

		//
		if (!is_readable($filename)) {
			throw new Exception('файл не доступен для чтения');
		}

		$this->src_file = $filename;
	}

	public function set_DestFilename($filename) {
		if (file_exists($filename)) {
			throw new Exception('файл результат уже существует');
		}

		$this->dst_file = $filename;
	}


	public function set_OutputDimensions($width, $height) {
		$this->width = intval($width, 10);
		$this->height = intval($height, 10);
	}

	public function set_OutputQuality($quality) {
		$this->quality = intval($quality, 10);
	}


	protected function createTempFile($path) {
		$temp_name = tempnam($path, 'piclgua_ic');
		if (FALSE === $temp_name) {
			throw new Exception('не удалось создать временный файл');
		}

		$this->tmp_file = $temp_name;
	}


	protected function removeTempFile() {
		if (file_exists($this->tmp_file)) {
			unlink($this->tmp_file);
		}

		$this->tmp_file = FALSE;
	}


	public function set_OutputFormat($format) {
		$valid_formats = array('jpeg', 'png', 'gif', 'tif', 'bmp');

		if (empty($format) || !in_array($format, $valid_formats)) {
			throw new Exception('неверный формат результата');
		}

		$this->format = $format;
	}


	protected function moveTempFileToDst() {
		if (!rename($this->tmp_file, $this->dst_file)) {
			throw new Exception('не удалось переименновать временный файл');
		}
	}
}



?>
