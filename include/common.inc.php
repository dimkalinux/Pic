<?php

// Make sure no one attempts to run this script "directly"
if (!defined('UP')) {
    exit;
}


class Common {
    protected function getRemoteData($sourceUrl, $connectTimeout=10, $timeout=30) {
	if (DEBUG === TRUE) {
	    $q_start = get_microtime();
	}

	$curl_options = array(
	    CURLOPT_RETURNTRANSFER => TRUE,     // return web page
	    CURLOPT_HEADER         => FALSE,    // don't return headers
	    CURLOPT_FOLLOWLOCATION => FALSE,     // follow redirects
	    CURLOPT_ENCODING       => "UTF-8",       // handle all encodings
	    CURLOPT_USERAGENT      => "iTeam SearchAPI Spider", // who am i
	    CURLOPT_AUTOREFERER    => TRUE,     // set referer on redirect
	    CURLOPT_CONNECTTIMEOUT => $connectTimeout,      // timeout on connect
	    CURLOPT_TIMEOUT        => $timeout,      // timeout on response
		CURLOPT_SSL_VERIFYPEER	=> FALSE,
	    CURLOPT_MAXREDIRS      => 2,       // stop after 10 redirects
	);

	$ch = curl_init($sourceUrl);
	curl_setopt_array($ch, $curl_options);
	$content = curl_exec($ch);
	$err = curl_errno($ch);
	$errmsg  = curl_error($ch);
	$header  = curl_getinfo($ch);
	curl_close($ch);

	if (DEBUG === TRUE) {
	    $q_end = sprintf('%.5f', get_microtime() - $q_start);

	    $log = Logger::singleton();
	    $log->debug($q_end.' — '.$sourceUrl);
	}

	if ($err != 0) {
	    throw new Exception('ошибка: '.$errmsg);
	}

	return $content;
    }

    public function getIndexCount() {
	try {
		$cache = Cache::singleton();
		if (!$num = $cache->get('index_num')) {
			$db = DB::singleton();

			$row = $db->getRow("SELECT COUNT(*) AS NUM FROM data");
			$num = intval($row['NUM'], 10);

			$cache->set($num, 'index_num', 300);
		}

		return $num;
	} catch (Exception $e) {
		return 0;
	}
    }

    public function set_cookie($name, $value, $expire) {
	global $cookie_path, $cookie_domain, $cookie_secure;

	// Enable sending of a P3P header
	header('P3P: CP="CUR ADM"');

	if (version_compare(PHP_VERSION, '5.2.0', '>=')) {
	    setcookie($name, $value, $expire, $cookie_path, $cookie_domain, $cookie_secure, TRUE);
	} else {
	    setcookie($name, $value, $expire, $cookie_path.'; HttpOnly', $cookie_domain, $cookie_secure);
	}
    }



    public static function redirect($url, $html = '', $title = 'Переадресация') {
		header($_SERVER['SERVER_PROTOCOL']." 303 See Other");
		header("Location: ".$url);
		header("Content-type: text/html; charset=UTF-8");
		$hUrl = htmlspecialchars($url);

		$page = <<<PAGE
<!DOCTYPE html><title>{$title}</title>
<script type="text/javascript">function doRedirect(){location.replace({$hUrl});}</script>
<style type="text/css">p{font-family:Arial, sans-serif}</style>
<body onload="doRedirect()" bgcolor="#ffffff" text="#000000" link="#0000cc" vlink="#551a8b" alink="#ff0000">
<noscript><meta http-equiv="refresh" content="1; url='{$hUrl}'"></noscript>
<p>Подождите&hellip;</p>
<p>Если переадресация не сработала, перейдите по <a href="{$hUrl}">ссылке</a> вручную.</p>
{$html}
</body>
PAGE;
		exit($page);
	}


    private function time_to_string($value, $str1, $str2, $str5) {
		if (!$value) {
			return 0;
		}

		$mod = $value % 10;

		if (($value % 100) >= 10 && ($value % 100) <= 19) {
			return $str5;
		}

		if ($mod == 1) {
			return $str1;
		}

		if ($mod >= 2 && $mod <= 4) {
			return $str2;
		}

		return $str5;
	}


	protected function format_gamers($r) {
		return $r.'&nbsp;'.$this->time_to_string($r, 'игрок', 'игрока', 'игроков');
	}

	protected function format_serials($r) {
		return $r.'&nbsp;'.$this->time_to_string($r, 'сериал', 'сериала', 'cериалов');
	}

	protected function format_episodes($r) {
		return $r.'&nbsp;'.$this->time_to_string($r, 'серия', 'серии', 'серий');
	}

	protected function format_uah($u) {
		$i = intval($u, 10);
		setlocale(LC_MONETARY, 'uk_UA.utf8');
		return money_format('%!n', $u).'&nbsp;'.$this->time_to_string ($i, 'гривна', 'гривны', 'гривен');
	}



	protected function format_search_results($r) {
		if ($r === 0) {
			return ' совпадений для';
		}
		return $this->time_to_string($r, 'совпадение для', 'совпадения для', 'совпадений для');
	}


	protected function fancy_date($a='now', $format='front', $language='ru') {
		date_default_timezone_set(CONFIG_TIMEZONE);
		$date = strtotime($a);

		switch($format) {

			case 'front':
				$monthes = array (
				 'en' => array('January','February','March','April','May','June','July','August','September','October','November','December'),
				 'ru' => array('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря'),
				 'uk' => array('січня','лютого','березня','квітня','травня','червня','липня','серпня','вересня','жовтня','листопада','грудня'),
				);

				$weekday = array (
				 'en' => array('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'),
				 'ru' => array('Понедельник','Вторник','Среда','Четверг','Пятница','Суббота','Воскресенье'),
				 'uk' => array('Понеділок','Вівторок','Середа','Четвер','П’ятниця','Субота','Неділя'),
				);

				switch ($language) {
					case 'ru':
					case 'uk':
						$output = date("j",$date) .' '. $monthes[$language][gmdate("n",$date)-1] .', '. $weekday[$language][gmdate("N",$date)-1];
						break;
					 case 'en':
					default:
						$output = $monthes[$language][gmdate("n",$date)-1] .' '. gmdate("j",$date) .', '. $weekday[$language][gmdate("N",$date)-1];
						break;
				}
				break;

			case 'short':
				$output = date('d.m.Y', $date); //20.02.2010
				break;

			case 'long':
				$output = date('d.m.Y H:i', $date); //20.02.2010
				break;

			default:
				$output = date("j",$date).' '.$monthes[date("n",$date)-1].' '.date("Y",$date).' '.date("H",$date).':'.date("i",$date);
				break;
		}

		return $output;
	}


   	protected function getRemoteDataCached($sourceUrl, $cache_id, $cache_timeout) {
		$cache = Cache::singleton();
		if (!$content = $cache->get($cache_id)) {
			$content = $this->getRemoteData($sourceUrl);

			// ADD TO CACHE
			$cache->set($content, $cache_id, $cache_timeout);
		}

		return $content;
	}


	protected function exitWithError($msg='') {
		exit(json_encode(array('result'=> 0, 'message' => $msg)));
	}


    protected function echoLog($message) {
		date_default_timezone_set("Europe/Zaporozhye");
		echo strftime('%c')." — $message\n";
    }


    protected function echoError($message) {
		date_default_timezone_set("Europe/Zaporozhye");
		echo strftime('%c')." — ERROR: $message\n";
    }


    protected function shortString($string, $length) {
		$string = utf8_trim($string);
		if ((utf8_strlen($string)-4) < $length) {
			return $string;
		}

		$i = 5;
		do {
			$lastChar = mb_substr($string, $length, 1);
			if (in_array($lastChar, array('.', ',', '!', '?', ' '))) {
				$length--;
			} else {
				break;
			}
		} while ($i > 0);

		return mb_substr($string, 0, $length).'…';
    }


    protected function non_empty_array($array) {
		return (is_array($array) && count($array) > 0);
    }


    protected function parseXML($content=FALSE) {
		$xml = @simplexml_load_string($content);

		if ($xml === FALSE) {
			throw new Exception('нет данных');
		}
		return $xml;
	}
}

?>
