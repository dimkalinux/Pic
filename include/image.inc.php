<?php

// Make sure no one attempts to run this script "directly"
if (!defined('UP')) {
	exit;
}

require UP_ROOT.'include/phpThumb/phpthumb.class.php';


class Image {
	const JPEG = IMAGETYPE_JPEG;
	const PNG = IMAGETYPE_PNG;
	const GIF = IMAGETYPE_GIF;
	const TIFF_II = IMAGETYPE_TIFF_II;
	const TIFF_MM = IMAGETYPE_TIFF_MM;
	const TIFF = IMAGETYPE_TIFF;
	const BMP = IMAGETYPE_BMP;

	private $image;
	private $format;
	private $phpThumbFormat;
	private $width;
	private $height;
	private $p_width;
	private $p_height;
	private $p_size;


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

			case self::TIFF_II:
			case self::TIFF_MM:
			case self::TIFF:
				$this->format = self::TIFF;
				break;

			case self::BMP:
				$this->format = self::BMP;
				break;

			default:
				throw new Exception("Unknown image type or file '$file' not found.");
				break;
		}

		$this->image = $file;
		$this->width = $info[0];
		$this->height = $info[1];
	}


	public function __destruct() {
		// REMOVE FILE if EXIST

	}


	public function getWidth() {
		return $this->width;
	}

	public function getHeight() {
		return $this->height;
	}

	public function getFileName() {
		return basename($this->image);
	}

	public function getPreview_Width() {
		return $this->p_width;
	}

	public function getPreview_Height() {
		return $this->p_height;
	}

	public function getPreview_Size() {
		return $this->p_size;
	}

	public function setFileExt() {
		$ext = '';

		switch ($this->format) {
			case self::JPEG:
				$ext = 'jpg';
				$this->phpThumbFormat = 'jpeg';
				break;

			case self::PNG:
				$ext = 'png';
				$this->phpThumbFormat = 'png';
				break;

			case self::TIFF:
				$ext = 'tif';
				$this->phpThumbFormat = 'png';
				break;

			case self::BMP:
				$ext = 'bmp';
				$this->phpThumbFormat = 'png';
				break;

			case self::GIF:
				$ext = 'gif';
				$this->phpThumbFormat = 'gif';
				break;

			default:
				throw new Exception("Unknown image type or file '$file' not found.");
				break;
		}

		$newImage = $this->image.'.'.$ext;
		if (!rename($this->image, $newImage)) {
			throw new Exception("Can not set image ext.");
		} else {
			$this->image = $newImage;
		}
	}


	public function process_thumbs() {
		$this->create_small_thumbs();
		$this->create_medium_thumbs();
		$this->create_preview();
	}



	private function create_small_thumbs() {
		global $pic_image_small_height, $pic_image_small_width, $pic_image_small_quality;

		$just_make_link = FALSE;
		if (($pic_image_small_height >= $this->height) && ($pic_image_small_width >= $this->width)) {
			$just_make_link = TRUE;
		}

		$this->create_thumbs($pic_image_small_width, $pic_image_small_height, $pic_image_small_quality, $this->get_prefixed_name_for_thumbs('sm', $this->image), $just_make_link);
	}

	private function create_medium_thumbs() {
		global $pic_image_medium_height, $pic_image_medium_width, $pic_image_medium_quality;

		$just_make_link = FALSE;
		if (($pic_image_medium_height >= $this->height) && ($pic_image_medium_width >= $this->width)) {
			$just_make_link = TRUE;
		}

		$this->create_thumbs($pic_image_medium_width, $pic_image_medium_height, $pic_image_medium_quality, $this->get_prefixed_name_for_thumbs('md', $this->image), $just_make_link);
	}

	private function create_preview() {
		global $pic_image_preview_height, $pic_image_preview_width, $pic_image_preview_quality;

		$just_make_link = FALSE;
		if (($pic_image_preview_height >= $this->height) && ($pic_image_preview_width >= $this->width)) {
			$just_make_link = TRUE;
		}

		$this->create_thumbs($pic_image_preview_width, $pic_image_preview_height, $pic_image_preview_quality, $this->get_prefixed_name_for_thumbs('pv', $this->image), $just_make_link);

		// UPDATE preview INFO
		$preview_image = $this->get_prefixed_name_for_thumbs('pv', $this->image);
		$info = @/**/getimagesize($preview_image);
		$this->p_width = $info[0];
		$this->p_height = $info[1];
		$this->p_size = @/**/filesize($preview_image);
	}


	private function get_prefixed_name_for_thumbs($prefix, $original_name) {
		$path_parts = pathinfo($original_name);

		$filename = $path_parts['dirname'].'/'.$prefix.'_'.$path_parts['basename'];

		// CHANGE ext for TIFF & BMP
		switch ($this->format) {
			case self::TIFF:
			case self::BMP:
				$filename = pic_replaceFileExtension($filename, 'png');
				break;

			default:
				break;
		}

		return $filename;
	}


	private function create_thumbs($width, $height, $quality, $file, $just_make_link=FALSE) {
		global $pic_image_autorotate;

		// MAKE link EXCEPT TIFF & BMP
		if (($just_make_link === TRUE) && ($this->format != self::TIFF) && ($this->format != self::BMP)) {
			if (!link($this->image, $file)) {
				throw new Exception('Ошибка при создании превью');
			}
			return TRUE;
		}

		$phpThumb = new phpThumb();
		//
		$phpThumb->setSourceFilename($this->image);
		//
		$phpThumb->w = $width;
		$phpThumb->h = $height;
		$phpThumb->q = $quality;

		if ($pic_image_autorotate) {
			$phpThumb->ar = 'x';
		}
		//
		$phpThumb->config_output_format = $this->phpThumbFormat;
		//
		$phpThumb->config_error_die_on_error = FALSE;
		//
		$phpThumb->config_allow_src_above_docroot = TRUE;

		if (!$phpThumb->GenerateThumbnail()) {
			throw new Exception('Ошибка при создании превью');
		}

		if (!$phpThumb->RenderToFile($file)) {
			throw new Exception('Ошибка при сохранении превью');
		}
	}
}

?>
