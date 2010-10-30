<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit();
}

class Upload_url extends Upload_base {
	public function __construct($url, $multi_upload, $user_id, $auto_shorten_service, $upload_options) {
		if (!isset($url)) {
			throw new Exception("Файл '".ami_htmlencode($url)."' не найден");
		}

		$this->url = $url;
		$this->skip_check_size = ami_GetOptions($upload_options, UPLOAD_FLAG_SKIP_FILESIZE_CHECK, FALSE);

		if ($this->skip_check_size) {
			$this->size = 0;
		} else {
			$this->size = ami_GetRemoteFileSize($url);
		}

		$this->error = 0;
		$this->user_id = $user_id;
		$this->_auto_shorten_service = $auto_shorten_service;
	}


	public function proccess_upload() {
		global $pic_TMPdir;

		$temp_name = tempnam($pic_TMPdir,'piclgua');
		if (FALSE === $temp_name) {
			throw new Exception('Не удалось создать временный файл');
		}

		ami_debug("tmp: $pic_TMPdir $temp_name");

		$temp_handle = fopen($temp_name, "wb");
		if (FALSE === $temp_handle) {
			throw new Exception('Не удалось открыть временный файл');
		}

		$curl_options = array(
			CURLOPT_RETURNTRANSFER 	=> FALSE,     // return web page
			CURLOPT_HEADER         	=> FALSE,    // don't return headers
			CURLOPT_FOLLOWLOCATION 	=> FALSE,     // follow redirects
			CURLOPT_ENCODING       	=> "UTF-8",       // handle all encodings
			CURLOPT_USERAGENT      	=> "AMI_FRAMEWORK", // who am i
			CURLOPT_AUTOREFERER    	=> TRUE,     // set referer on redirect
			CURLOPT_CONNECTTIMEOUT 	=> 5,      // timeout on connect
			CURLOPT_TIMEOUT        	=> 15,      // timeout on response
			CURLOPT_SSL_VERIFYPEER	=> FALSE,
			CURLOPT_MAXREDIRS      	=> 2,
			CURLOPT_FILE 			=> $temp_handle,
			CURLOPT_BINARYTRANSFER 	=> TRUE,
	    );

		$ch = curl_init($this->url);
	    curl_setopt_array($ch, $curl_options);
	    curl_exec($ch);
	    $err = curl_errno($ch);
	    $errmsg  = curl_error($ch);
	    curl_close($ch);


	    if ($err != 0) {
			throw new Exception('Ошибка CURL: '.$errmsg);
	    }

		$this->tmpName = $temp_name;
		fclose($temp_handle);

		// CHECK REMOTE SIZE === LOCAL FILE SIZE
		if ($this->skip_check_size === FALSE) {
			if (@/**/filesize($temp_name) !== $this->size) {
				throw new Exception('Неверный размер файла');
			}
		} else {
			// JUST SET REAL SIZE
			$this->size = @/**/filesize($temp_name);
		}

		$url_parts = parse_url($this->url);
		$pathTokens = explode('/', $url_parts['path']);
		$name = end($pathTokens);

		if (!empty($name)) {
			$this->name = $name;
		} else {
			// GENERATE RANDOM NAME
			$this->name = ami_GenerateRandomHash(7).'jpg';
		}

		$this->error === UPLOAD_ERR_OK;
	}
}

?>
