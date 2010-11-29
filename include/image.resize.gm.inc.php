<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit;
}

require AMI_ROOT.'include/phpThumb/phpthumb.class.php';


class Image_Resizer_GM extends Image_Resizer {

	public function resize() {
		$this->createTempFile('/tmp/1/');

		$dimensions = $this->width.'x'.$this->height;

		$quality_cmd_part = '';
		if (isset($this->quality) && $this->quality > 0) {
			$quality_cmd_part = '-quality '.$this->quality;
		}

		$cmd_line = sprintf('/usr/bin/gm convert -resize %s '.$quality_cmd_part.' +profile "*" %s %s', $dimensions, escapeshellarg($this->src_file), escapeshellarg($this->tmp_file));
		exec($cmd_line, $output, $return_code);

		$this->moveTempFileToDst();
	}


	public function thumbs() {
		$this->createTempFile('/tmp/1/');

		$dimensions = $this->width.'x'.$this->height;

		//
		$quality_cmd_part = '';
		if (isset($this->quality) && $this->quality > 0) {
			$quality_cmd_part = '-quality '.$this->quality;
		}

		$cmd_line = sprintf('/usr/bin/gm convert -size %s '.$quality_cmd_part.' -resize %s +profile "*" %s %s', $dimensions, $dimensions, escapeshellarg($this->src_file), escapeshellarg($this->tmp_file));
		exec($cmd_line, $output, $return_code);

		$this->moveTempFileToDst();
	}
}
