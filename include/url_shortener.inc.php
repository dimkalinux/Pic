<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit;
}


define('URL_SHORTENER_NONE', 0);
define('URL_SHORTENER_BITLY', 1);
define('URL_SHORTENER_CLCK', 2);
define('URL_SHORTENER_TINYURL', 3);


class URL_Shortener {
	//
	private $_strategy;


	public function __construct($service_id=URL_SHORTENER_NONE) {
		switch($service_id) {
			//
			case URL_SHORTENER_BITLY:
				$this->_strategy = new URL_Shortener_Base(new URL_Shortener_bitly);
				break;

			//
			case URL_SHORTENER_CLCK:
				$this->_strategy = new URL_Shortener_Base(new URL_Shortener_clck);
				break;

			case URL_SHORTENER_TINYURL:
				$this->_strategy = new URL_Shortener_Base(new URL_Shortener_tinyurl);
				break;

			//
			default:
				throw new Exception('неизвестный сервис сокращатель ссылок');
				break;
		}
	}

	public function shorten($url) {
		return $this->_strategy->shorten($url);
	}

	public function expand($url) {
		return $this->_strategy->expand($url);
	}
}



// INTERFACE
interface IURL_Shortener {
	//
	function shorten($url);

	//
	function expand($url);
}


// BASE CLASS
class URL_Shortener_Base {
	//
	private $_strategy;


	public function __construct(IURL_Shortener $strategy) {
		$this->_strategy = $strategy;
	}


	public function shorten($url) {
		return $this->_strategy->shorten($url);
	}

	public function expand($url) {
		return $this->_strategy->expand($url);
	}
}



/*
 * Bit.ly implementations
*/
class URL_Shortener_bitly implements IURL_Shortener {
	//
	const API_LOGIN = 'dimkalinux';
	const API_KEY = 'R_2ddc035cd7914f1bc1d6d0c3945401bf';
	const DOMAIN = 'bit.ly'; // or j.mp


	//
	public function shorten($url) {
		$connectURL = 'http://api.bit.ly/v3/shorten?login='.self::API_LOGIN.'&apiKey='.self::API_KEY.'&uri='.urlencode($url).'&format=json';

		// try to get
		try {
			$response = json_decode(ami_getRemoteData($connectURL), TRUE);
		} catch (Exception $e) {
			throw new Exception('ошибка сокращения ссылки: «'.$e->getMessage().'»');
		}


		// check
		if (isset($response['status_code']) && intval($response['status_code'], 10) === 200) {
			return $response['data']['url'];
		}

		// ERROR here
		$this->parse_error($response);
	}


	//
	public function expand($url) {
		$connectURL = 'http://api.bit.ly/v3/expand?login='.self::API_LOGIN.'&apiKey='.self::API_KEY.'&shortUrl='.urlencode($url).'&format=json';

		// try to get
		try {
			$response = json_decode(ami_getRemoteData($connectURL), TRUE);
		} catch (Exception $e) {
			throw new Exception('ошибка сокращения ссылки: «'.$e->getMessage().'»');
		}


		// check for error
		if (isset($response['status_code']) && intval($response['status_code'], 10) === 200) {
			if (isset($response['data']['expand']['long_url'])) {
				return $response['data']['expand']['long_url'];
			} else {
				// URL NOT FOUND
				return FALSE;
			}
		}

		// ERROR here
		$this->parse_error($response);
	}


	private function parse_error($response) {
		$status_message = 'пустой ответ';

		if ($response) {
			// code
			if (isset($response['status_code'])) {
				$status_message = 'код: '.$response['status_code'];
			}

			// txt
			if (isset($response['status_txt'])) {
				$status_message .= ' ('.$response['status_txt'].')';
			}
		}

		throw new Exception('сервер сокращатель ссылок (api.bit.ly) вернул ошибку: «'.$status_message.'»');
	}
}



/*
 *	Clck.ru implementations
 */
class URL_Shortener_clck implements IURL_Shortener {
	//
	public function shorten($url) {
		$connectURL = 'http://clck.ru/--?url='.urlencode($url);

		try {
			$response = ami_getRemoteData($connectURL);
		} catch (Exception $e) {
			throw new Exception('ошибка сокращения ссылки: «'.$e->getMessage().'»');
		}

		if (!empty($response)) {
			if (strpos(ami_trim($response), 'http') === 0) {
				return ami_trim($response);
			}
		}

		// ERROR here
		throw new Exception('сервер сокращатель ссылок (clck.ru) вернул ошибку');
	}


	//
	public function expand($url) {
		return FALSE;
	}
}

/*
 *	Clck.ru implementations
 */
class URL_Shortener_tinyurl implements IURL_Shortener {
	//
	public function shorten($url) {
		$connectURL = 'http://tinyurl.com/api-create.php?url='.urlencode($url);

		try {
			$response = ami_getRemoteData($connectURL);
		} catch (Exception $e) {
			throw new Exception('ошибка сокращения ссылки: «'.$e->getMessage().'»');
		}

		if (!empty($response)) {
			if (strpos(ami_trim($response), 'http') === 0) {
				return ami_trim($response);
			}
		}

		// ERROR here
		throw new Exception('сервер сокращатель ссылок (tinyurl.com) вернул ошибку');
	}


	//
	public function expand($url) {
		return FALSE;
	}
}

?>
