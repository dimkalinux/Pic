<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit;
}

require AMI_ROOT.'include/image.resize.inc.php';
require AMI_ROOT.'include/image.resize.gm.inc.php';
require AMI_ROOT.'include/image.resize.im.inc.php';

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
				throw new Exception("неизвестный формат файла");
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
				throw new Exception("неизвестный формат файла.");
				break;
		}

		$newImage = ami_change_filename_ext($this->image, $ext);
		if ($newImage == $this->image) {
			$newImage = $this->image.'.'.$ext;
		}

		if (!rename($this->image, $newImage)) {
			throw new Exception("невозможно изменить расширение файла");
		} else {
			$this->image = $newImage;
		}
	}

	public function resizeOriginal($size) {
		$path_parts = pathinfo($this->image);
		$tmp_filename = $path_parts['dirname'].'/tmp_'.$path_parts['basename'];

		$ir = new Image_Resizer_IM;

		$ir->set_SourceFilename($this->image);
		$ir->set_DestFilename($tmp_filename);
		$ir->set_OutputFormat($this->phpThumbOriginalFormat);
		$ir->set_OutputDimensions($size, $size);
		$ir->set_OutputQuality(PIC_IMAGE_RESIZE_ORIGINAL_QUALITY);

		$ir->resize();

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
		// 1 - preview
		$preview_file = $this->create_preview();

		// FOR BIG IMAGES MAKE THUMBS FROM PREVIEW
		if (($this->p_width * $this->p_height) < 250000) {
			$preview_file = FALSE;
		}

		if (FALSE === PIC_USE_IMAGE_THUMBS_OPTIMIZE) {
			$preview_file = FALSE;
		}

		$this->create_small_thumbs($preview_file);
		$this->create_medium_thumbs($preview_file);
		$this->create_gallery_thumbs($preview_file);
	}

	private function create_gallery_thumbs($src_file) {
		$just_make_link = FALSE;

		if ((PIC_IMAGE_GALLERY_HEIGHT >= $this->height) && (PIC_IMAGE_GALLERY_WIDTH >= $this->width)) {
			$just_make_link = TRUE;
		}

		$this->create_thumbs(PIC_IMAGE_GALLERY_WIDTH, PIC_IMAGE_GALLERY_HEIGHT, PIC_IMAGE_GALLERY_QUALITY, $this->get_prefixed_name_for_thumbs('gl', $this->image), $just_make_link, $src_file, FALSE);
	}

	private function create_small_thumbs($src_file) {
		$just_make_link = FALSE;

		if ((PIC_IMAGE_SMALL_HEIGHT >= $this->height) && (PIC_IMAGE_SMALL_WIDTH >= $this->width)) {
			$just_make_link = TRUE;
		}

		$this->create_thumbs(PIC_IMAGE_SMALL_WIDTH, PIC_IMAGE_SMALL_HEIGHT, PIC_IMAGE_SMALL_QUALITY, $this->get_prefixed_name_for_thumbs('sm', $this->image), $just_make_link, $src_file, FALSE);
	}

	private function create_medium_thumbs($src_file) {
		$just_make_link = FALSE;

		if ((PIC_IMAGE_MEDIUM_HEIGHT >= $this->height) && (PIC_IMAGE_MEDIUM_WIDTH >= $this->width)) {
			$just_make_link = TRUE;
		}

		$this->create_thumbs(PIC_IMAGE_MEDIUM_WIDTH, PIC_IMAGE_MEDIUM_HEIGHT, PIC_IMAGE_MEDIUM_QUALITY, $this->get_prefixed_name_for_thumbs('md', $this->image), $just_make_link, $src_file, FALSE);
	}

	private function create_preview() {
		$just_make_link = $prefer_speed = FALSE;

		if ((PIC_IMAGE_PREVIEW_HEIGHT >= $this->height) && (PIC_IMAGE_PREVIEW_WIDTH >= $this->width)) {
			$just_make_link = TRUE;
		}

		$original_d = $this->height * $this->width;
		$result_d = PIC_IMAGE_PREVIEW_HEIGHT * PIC_IMAGE_PREVIEW_WIDTH;
		if (($original_d > $result_d) && intval(($original_d / $result_d), 10) > 4) {
			$prefer_speed = TRUE;
		}

		$result_file = $this->create_thumbs(PIC_IMAGE_PREVIEW_WIDTH, PIC_IMAGE_PREVIEW_HEIGHT, PIC_IMAGE_PREVIEW_QUALITY, $this->get_prefixed_name_for_thumbs('pv', $this->image), $just_make_link, FALSE, $prefer_speed);

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


	private function create_thumbs($width, $height, $quality, $file, $just_make_link=FALSE, $src_file=FALSE, $prefer_speed) {
		// MAKE link EXCEPT TIFF & BMP
		if (($just_make_link === TRUE) && ($this->format != self::TIFF_II) && ($this->format != self::TIFF_MM) && ($this->format != self::BMP)) {
			if (!link($this->image, $file)) {
				// BUG
				throw new Exception('Ошибка при создании превью');
			}
			return $this->image;
		}

		//
		if (FALSE === $src_file) {
			$src_file = $this->image;
		}

		$ir = new Image_Resizer_IM;

		$ir->set_SourceFilename($src_file);
		$ir->set_DestFilename($file);
		$ir->set_OutputFormat($this->phpThumbFormat);
		$ir->set_OutputDimensions($width, $height);
		$ir->set_OutputQuality($quality);

		if ($prefer_speed) {
			$ir->thumbs();
		} else {
			$ir->resize();
		}

		return $file;
	}
}

?>
