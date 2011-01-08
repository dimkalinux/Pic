<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit;
}

class Image_Resizer_IM extends Image_Resizer {

	public function resize() {
		global $pic_useImageThumbsGammaCorrection;

		$this->createTempFile('/tmp/1/');

		$dimensions = $this->width.'x'.$this->height;

		$quality_cmd_part = '';
		if (isset($this->quality) && $this->quality > 0) {
			$quality_cmd_part = '-quality '.$this->quality;
		}

		$output_depth_cmd_part = '';
		if ($this->format != 'jpeg') {
			$output_depth_cmd_part = '-depth 8';
		}

		// with gamma correction
		if ($pic_useImageThumbsGammaCorrection) {
			$cmd_line = sprintf('/usr/bin/convert %s -depth 16 -gamma 0.454545 -filter lanczos -resize %s -gamma 2.2 '.$quality_cmd_part.' -sampling-factor 1x1 '.$output_depth_cmd_part.' %s', escapeshellarg($this->src_file), $dimensions, escapeshellarg($this->tmp_file));
		} else {
			// without gamma correction
			$cmd_line = sprintf('/usr/bin/convert %s -resize %s '.$quality_cmd_part.' %s', escapeshellarg($this->src_file), $dimensions, escapeshellarg($this->tmp_file));
		}

		ami_debug('resize cmd: '.$cmd_line);

		exec($cmd_line, $output, $return_code);

		$this->moveTempFileToDst();
	}


	public function thumbs() {
		global $pic_useImageThumbsGammaCorrection;

		$this->createTempFile('/tmp/1/');

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


		// with gamma correction
		if ($pic_useImageThumbsGammaCorrection) {
			$cmd_line = sprintf('/usr/bin/convert %s -depth 16 -gamma 0.454545 -filter lanczos '.$quality_cmd_part.' -resize %s -gamma 2.2 -sampling-factor 1x1 '.$output_depth_cmd_part.' %s', escapeshellarg($this->src_file), $dimensions, escapeshellarg($this->tmp_file));
		} else {
			// without gamma correction
			$cmd_line = sprintf('/usr/bin/convert %s -resize %s '.$quality_cmd_part.' %s', escapeshellarg($this->src_file), $dimensions, escapeshellarg($this->tmp_file));
		}

		ami_debug('thumbs cmd: '.$cmd_line);

		exec($cmd_line, $output, $return_code);

		$this->moveTempFileToDst();
	}
}
