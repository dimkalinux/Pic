<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit;
}

class Image_Resizer_IM extends Image_Resizer {

	public function resize() {
		$this->createTempFile(PIC_TMP_DIR);

		$dimensions = $this->width.'x'.$this->height;

		$quality_cmd_part = '';
		if (isset($this->quality) && $this->quality > 0) {
			$quality_cmd_part = '-quality '.$this->quality;
		}

		$output_depth_cmd_part = '';
		if ($this->format != 'jpeg') {
			$output_depth_cmd_part = '-depth 8';
		}

		// SHARPEN?
		$sharpen_cmd_part = '';
		if (TRUE === PIC_USE_IMAGE_SHARPEN) {
			if (($this->width * $this->height) / ($this->src_width * $this->src_height) < PIC_USE_IMAGE_SHARPEN_THRESHOLD) {
				$sharpen_cmd_part = '-unsharp '.PIC_USE_IMAGE_SHARPEN_PARAM;
			}
		}

		// with gamma correction
		if (PIC_USE_IMAGE_THUMBS_GAMMA_CORRECTION) {
			$cmd_line = sprintf('/usr/bin/convert %s -depth 16 -gamma 0.454545 -filter lanczos -resize %s -gamma 2.2 '.$quality_cmd_part.' -sampling-factor 1x1 '.$sharpen_cmd_part.' '.$output_depth_cmd_part.' '.$this->format.':%s', escapeshellarg($this->src_file), $dimensions, escapeshellarg($this->tmp_file));
		} else {
			// without gamma correction
			$cmd_line = sprintf('/usr/bin/convert %s -resize %s '.$quality_cmd_part.' '.$sharpen_cmd_part.' '.$this->format.':%s', escapeshellarg($this->src_file), $dimensions, escapeshellarg($this->tmp_file));
		}

		exec($cmd_line, $output, $return_code);

		$this->moveTempFileToDst();
	}


	public function thumbs() {
		$this->createTempFile(PIC_TMP_DIR);

		$dimensions = $this->width.'x'.$this->height;

		//
		$quality_cmd_part = '';
		if (isset($this->quality) && $this->quality > 0) {
			$quality_cmd_part = '-quality '.$this->quality;
		}

		$output_depth_cmd_part = '';
		if ($this->format != 'jpeg') {
			$output_depth_cmd_part = '-depth 8';
		}

		// SHARPEN?
		$sharpen_cmd_part = '';
		if (TRUE === PIC_USE_IMAGE_SHARPEN) {
			if (($this->width * $this->height) / ($this->src_width * $this->src_height) < PIC_USE_IMAGE_SHARPEN_THRESHOLD) {
				$sharpen_cmd_part = '-unsharp '.PIC_USE_IMAGE_SHARPEN_PARAM;
			}
		}


		// with gamma correction
		if (PIC_USE_IMAGE_THUMBS_GAMMA_CORRECTION) {
			$cmd_line = sprintf('/usr/bin/convert %s -depth 16 -gamma 0.454545 -filter lanczos '.$quality_cmd_part.' -resize %s -gamma 2.2 -sampling-factor 1x1 '.$sharpen_cmd_part.' '.$output_depth_cmd_part.' '.$this->format.':%s', escapeshellarg($this->src_file), $dimensions, escapeshellarg($this->tmp_file));
		} else {
			// without gamma correction
			$cmd_line = sprintf('/usr/bin/convert %s -resize %s '.$quality_cmd_part.' '.$sharpen_cmd_part.' '.$this->format.':%s', escapeshellarg($this->src_file), $dimensions, escapeshellarg($this->tmp_file));
		}

		exec($cmd_line, $output, $return_code);

		$this->moveTempFileToDst();
	}
}
