<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit;
}

require AMI_ROOT.'include/phpThumb/phpthumb.class.php';


class Image_Resizer_IM extends Image_Resizer {

	public function resize() {
		$this->thumbs();
	}


	public function thumbs() {
		$this->createTempFile('/tmp/1/');

		$phpThumb = new phpThumb();

		//
		$phpThumb->setSourceFilename($this->src_file);
		//
		$phpThumb->w = $this->width;
		$phpThumb->h = $this->height;
		$phpThumb->q = $this->quality;

		$phpThumb->config_output_format = $this->format;
		$phpThumb->config_error_die_on_error = FALSE;
		$phpThumb->config_allow_src_above_docroot = TRUE;

		if (!$phpThumb->GenerateThumbnail()) {
			throw new Exception('Ошибка при генерации превью');
		}

		if (!$phpThumb->RenderToFile($this->tmp_file)) {
			throw new Exception('Ошибка при сохранении превью');
		}

		$this->moveTempFileToDst();
	}
}
