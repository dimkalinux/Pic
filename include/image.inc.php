<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit;
}

require AMI_ROOT.'include/phpThumb/phpthumb.class.php';


class Image {
	const JPEG = IMAGETYPE_JPEG;
	const PNG = IMAGETYPE_PNG;
	const GIF = IMAGETYPE_GIF;
	const TIFF_II = IMAGETYPE_TIFF_II;
	const TIFF_MM = IMAGETYPE_TIFF_MM;
	const BMP = IMAGETYPE_BMP;

	private $image;
	private $format;
	private $phpThumbFormat;
	private $phpThumbOriginalFormat;
	private $width;
	private $height;
	private $p_width;
	private $p_height;
	private $p_size;
	private $multi_upload;


	public function __construct($file, $multi_upload) {
	    if (!is_file($file)) {
			throw new Exception("Файл '$file' не найден.");
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
				$this->format = self::TIFF_II;
				break;

			case self::TIFF_MM:
				$this->format = self::TIFF_MM;
				break;


			case self::BMP:
				$this->format = self::BMP;
				break;

			default:
				throw new Exception("Неизвестный формат файла.");
				break;
		}

		$this->image = $file;
		$this->width = $info[0];
		$this->height = $info[1];
		//
		$this->multi_upload = $multi_upload;
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
				$this->phpThumbOriginalFormat = 'jpeg';
				break;

			case self::PNG:
				$ext = 'png';
				$this->phpThumbFormat = 'png';
				$this->phpThumbOriginalFormat = 'png';
				break;

			case self::TIFF_II:
			case self::TIFF_MM:
				$ext = 'tif';
				$this->phpThumbFormat = 'png';
				$this->phpThumbOriginalFormat = 'tif';
				break;

			case self::BMP:
				$ext = 'bmp';
				$this->phpThumbFormat = 'png';
				$this->phpThumbOriginalFormat = 'bmp';
				break;

			case self::GIF:
				$ext = 'gif';
				$this->phpThumbFormat = 'gif';
				$this->phpThumbOriginalFormat = 'gif';
				break;

			default:
				throw new Exception("Неизвестный формат файла.");
				break;
		}

		$newImage = $this->image.'.'.$ext;
		if (!rename($this->image, $newImage)) {
			throw new Exception("Can not set image ext.");
		} else {
			$this->image = $newImage;
		}
	}

	public function resizeOriginal($size) {
		$path_parts = pathinfo($this->image);
		$tmp_filename = $path_parts['dirname'].'/tmp_'.$path_parts['basename'];

		$phpThumb = new phpThumb();
		//
		$phpThumb->setSourceFilename($this->image);
		//
		$phpThumb->w = $size;
		$phpThumb->h = $size;
		$phpThumb->q = 95;

		$phpThumb->config_output_format = $this->phpThumbOriginalFormat;
		//
		$phpThumb->config_error_die_on_error = FALSE;
		$phpThumb->config_allow_src_above_docroot = TRUE;

		if (!$phpThumb->GenerateThumbnail()) {
			throw new Exception('Ошибка при изменении размера оригинала');
		}

		if (!$phpThumb->RenderToFile($tmp_filename)) {
			throw new Exception('Ошибка при сохранении оригинала');
		}

		// rm original
		ami_safeFileUnlink($this->image);

		// rename tmp to original
		if (!rename($tmp_filename, $this->image)) {
			throw new Exception('Ошибка при изменении размера оригинала');
		}

		// UPDATE IMAGE SIZE
		$info = @/**/getimagesize($this->image);
		$this->width = $info[0];
		$this->height = $info[1];
	}


	public function process_thumbs() {
		global $pic_useImageThumbsOptimize;

		// 1 - preview
		$preview_file = $this->create_preview();

		// FOR BIG IMAGES MAKE THUMBS FROM PREVIEW
		if (($this->p_width * $this->p_height) < 250000) {
			$preview_file = FALSE;
		}

		if (FALSE === $pic_useImageThumbsOptimize) {
			$preview_file = FALSE;
		}

		$this->create_small_thumbs($preview_file);
		$this->create_medium_thumbs($preview_file);
		$this->create_gallery_thumbs($preview_file);
	}

	private function create_gallery_thumbs($src_file) {
		global $pic_image_gallery_height, $pic_image_gallery_width, $pic_image_gallery_quality;

		$just_make_link = FALSE;
		if (($pic_image_gallery_height >= $this->height) && ($pic_image_gallery_width >= $this->width)) {
			$just_make_link = TRUE;
		}

		$this->create_thumbs($pic_image_gallery_width, $pic_image_gallery_height, $pic_image_gallery_quality, $this->get_prefixed_name_for_thumbs('gl', $this->image), $just_make_link, $src_file);
	}

	private function create_small_thumbs($src_file) {
		global $pic_image_small_height, $pic_image_small_width, $pic_image_small_quality;

		$just_make_link = FALSE;
		if (($pic_image_small_height >= $this->height) && ($pic_image_small_width >= $this->width)) {
			$just_make_link = TRUE;
		}

		$this->create_thumbs($pic_image_small_width, $pic_image_small_height, $pic_image_small_quality, $this->get_prefixed_name_for_thumbs('sm', $this->image), $just_make_link, $src_file);
	}

	private function create_medium_thumbs($src_file) {
		global $pic_image_medium_height, $pic_image_medium_width, $pic_image_medium_quality;

		$just_make_link = FALSE;
		if (($pic_image_medium_height >= $this->height) && ($pic_image_medium_width >= $this->width)) {
			$just_make_link = TRUE;
		}

		$this->create_thumbs($pic_image_medium_width, $pic_image_medium_height, $pic_image_medium_quality, $this->get_prefixed_name_for_thumbs('md', $this->image), $just_make_link, $src_file);
	}

	private function create_preview() {
		global $pic_image_preview_height, $pic_image_preview_width, $pic_image_preview_quality;

		$just_make_link = FALSE;
		if (($pic_image_preview_height >= $this->height) && ($pic_image_preview_width >= $this->width)) {
			$just_make_link = TRUE;
		}

		$result_file = $this->create_thumbs($pic_image_preview_width, $pic_image_preview_height, $pic_image_preview_quality, $this->get_prefixed_name_for_thumbs('pv', $this->image), $just_make_link, FALSE);

		// UPDATE preview INFO
		$preview_image = $this->get_prefixed_name_for_thumbs('pv', $this->image);
		$info = @/**/getimagesize($preview_image);
		$this->p_width = $info[0];
		$this->p_height = $info[1];
		$this->p_size = @/**/filesize($preview_image);

		return $result_file;
	}


	private function get_prefixed_name_for_thumbs($prefix, $original_name) {
		$path_parts = pathinfo($original_name);

		$filename = $path_parts['dirname'].'/'.$prefix.'_'.$path_parts['basename'];

		// CHANGE ext for TIFF & BMP
		switch ($this->format) {
			case self::TIFF_II:
			case self::TIFF_MM:
			case self::BMP:
				$filename = pic_replaceFileExtension($filename, 'png');
				break;

			default:
				break;
		}

		return $filename;
	}


	private function create_thumbs($width, $height, $quality, $file, $just_make_link=FALSE, $src_file=FALSE) {
		global $pic_image_autorotate;

		// MAKE link EXCEPT TIFF & BMP
		if (($just_make_link === TRUE) && ($this->format != self::TIFF_II) && ($this->format != self::TIFF_MM) && ($this->format != self::BMP)) {
			if (!link($this->image, $file)) {
				throw new Exception('Ошибка при создании превью');
			}
			return $this->image;
		}

		//
		if (FALSE === $src_file) {
			$src_file = $this->image;
		}

		$phpThumb = new phpThumb();
		//
		$phpThumb->setSourceFilename($src_file);
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
			throw new Exception('Ошибка при генерации превью');
		}

		if (!$phpThumb->RenderToFile($file)) {
			throw new Exception('Ошибка при сохранении превью');
		}

		return $file;
	}

	private function rotate($files, $degree) {

	}
}

?>
