<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit;
}

function ami_addOnDOMReady($code) {
    global $ami_onDOMReady;
    array_push($ami_onDOMReady, $code);
}

function ami_addOnWindowReady($code) {
    global $ami_onWindowReady;
    array_push($ami_onWindowReady, $code);
}

function ami_addScript($script_name) {
    global $ami_addScript;
    array_push($ami_addScript, $script_name);
}


function ami_async_response($arr_response, $type=AMI_ASYNC_JSON) {
    switch ($type) {
	case AMI_ASYNC_JSON:
	default:
	    ami_JSON_response($arr_response);
	    break;

	case AMI_ASYNC_XML:
	    ami_XML_response($arr_response);
	    break;
    }
}

function ami_JSON_response($arr) {
    header('Pragma: no-cache');
    // header('Content-type: text/x-json');
    exit(json_encode($arr));
}

function ami_XML_response($arr) {
    require_once 'XML/Serializer.php';

    header('Pragma: no-cache');
    header('Content-type: application/xml');

    $serializer_options = array(
	'addDecl' => TRUE,
	'encoding' => 'UTF-8',
	'indent' => '  ',
	'rootName' => 'ami',
	'mode' => 'simplexml'
    );

    $serializer = &new XML_Serializer($serializer_options);
    if ($serializer->serialize($arr)) {
	exit($serializer->getSerializedData());
    } else {
	exit('<?xml version="1.0" encoding="UTF-8"?><ami><error>1</error><message>Internal XML error</message></ami>');
    }
}

function ami_htmlencode($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function ami_htmldecode($str) {
    return htmlspecialchars_decode($str, ENT_QUOTES);
}

function ami_get_safe_string($str) {
    return preg_replace("/[^a-z0-9]/i", "", $str);
}

function ami_get_safe_string_len($str, $maxLength) {
    return utf8_substr(preg_replace("/[^a-z0-9]/i", "", $str), 0, $maxLength);
}


// Trim whitespace including non-breaking space
function ami_trim($str, $charlist = " \t\n\r\x0b\xc2\xa0") {
    return utf8_trim($str, $charlist);
}


// Convert \r\n and \r to \n
function ami_linebreaks($str) {
	return str_replace(array("\r\n", "\r"), "\n", $str);
}


// Inserts $element into $input at $offset
// $offset can be either a numerical offset to insert at (eg: 0 inserts at the beginning of the array)
// or a string, which is the key that the new element should be inserted before
// $key is optional: it's used when inserting a new key/value pair into an associative array
function ami_array_insert(&$input, $offset, $element, $key = null) {
    if ($key == null) {
	$key = $offset;
    }

    // Determine the proper offset if we're using a string
    if (!is_int($offset)) {
	$offset = array_search($offset, array_keys($input), true);
    }

    // Out of bounds checks
    if ($offset > count($input)) {
	$offset = count($input);
    } else if ($offset < 0) {
	$offset = 0;
    }

    $input = array_merge(array_slice($input, 0, $offset), array($key => $element), array_slice($input, $offset));
}


function ami_link($link, $args = null) {
    global $ami_BaseURL, $ami_urls;

    if (!isset($ami_urls[$link])) {
		throw new Exception('Отсутствует идентификатор ссылки: '.ami_htmlencode($link));
    }

    $gen_link = $ami_urls[$link];
    if ($args == null) {
	$gen_link = $ami_BaseURL.'/'.$gen_link;
    } else if (!is_array($args)) {
	$gen_link = $ami_BaseURL.'/'.str_replace('$1', $args, $gen_link);
    } else {
	for ($i = 0; isset($args[$i]); ++$i) {

	    $gen_link = str_replace('$'.($i + 1), $args[$i], $gen_link);
	}
	$gen_link = $ami_BaseURL.'/'.$gen_link;
    }

    return $gen_link;
}


function ami_show_error_message($message) {
    ami_show_message('Ошибка', $message);
}

function ami_show_message($header, $message) {
    $home_link = ami_link('root');
    $about_link = ami_link('about');

    $out = <<<FMB
    <div class="span-15 prepend-5 body_block">
	<h2>$header</h2>
	<div>
	    $message
	    <br><br>
	    <a href="$home_link">Перейти на главную страницу</a>
	</div>
    </div>
FMB;
    ami_printPage($out, 'message_page');
    exit();
}

function ami_GetIP() {
    if (isset($_SERVER['REMOTE_ADDR'])) {
	return $_SERVER['REMOTE_ADDR'];
    } else {
	return null;
    }
}


function ami_GetGEO() {
    $apache_geo = 'world';	// default is 'world'

    if (!function_exists('apache_request_headers')) {
	return $apache_geo;
    }

    $headers = apache_request_headers();
    if ($headers && isset($headers['X-GEO'])) {
	$apache_geo = $headers['X-GEO'];
    }

    return $apache_geo;
}


// Unset any variables instantiated as a result of register_globals being enabled
function ami_unregister_globals() {
    $register_globals = @/**/ini_get('register_globals');
    if ($register_globals === "" || $register_globals === "0" || strtolower($register_globals) === "off") {
	return;
    }

    // Prevent script.php?GLOBALS[foo]=bar
    if (isset($_REQUEST['GLOBALS']) || isset($_FILES['GLOBALS'])) {
	   exit('I\'ll have a steak sandwich and... a steak sandwich.');
    }

    // Variables that shouldn't be unset
    $no_unset = array('GLOBALS', '_GET', '_POST', '_COOKIE', '_REQUEST', '_SERVER', '_ENV', '_FILES');

    // Remove elements in $GLOBALS that are present in any of the superglobals
    $input = array_merge($_GET, $_POST, $_COOKIE, $_SERVER, $_ENV, $_FILES, isset($_SESSION) && is_array($_SESSION) ? $_SESSION : array());
    foreach ($input as $k => $v) {
	if (!in_array($k, $no_unset) && isset($GLOBALS[$k])) {
	    unset($GLOBALS[$k]);
	    unset($GLOBALS[$k]);	// Double unset to circumvent the zend_hash_del_key_or_index hole in PHP <4.4.3 and <5.1.4
	}
    }
}


// Display a simple error message
function ami_show_error() {
    if (!headers_sent()) {
	header('Content-type: text/html; charset=utf-8');
	header('HTTP/1.1 503 Service Temporarily Unavailable');
    }

    $num_args = func_num_args();
    if ($num_args == 3) {
	$message = func_get_arg(0);
	$file = func_get_arg(1);
	$line = func_get_arg(2);
    } else if ($num_args == 2) {
	$file = func_get_arg(0);
	$line = func_get_arg(1);
    } else if ($num_args == 1) {
	$message = func_get_arg(0);
    }

    // Empty all output buffers and stop buffering
    while (@/**/ob_end_clean());

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="ru" xml:lang="ru">
<head profile="http://gmpg.org/xfn/11">
<title>Ошибка на сервере</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<style>
	body { width: 40em; margin: 50px; color: #2b2b2b; background: #fff; font:13px/1.331 arial,helvetica,clean,sans-serif; *font-size:small; /* for IE */ 	*font:x-small; /* for IE in quirks mode */ }
	h1, h2, h3, h4 { font-weight: normal; }
	#wrap { max-width: 700px; text-align: left; }
	p { line-height: 1.5em; }
</style>
</head>
<body>
    <div id="wrap">
	<h1>Ошибка на сервере</h1>
	<div id="page">
<?php

    if (isset($message)) {
	echo '<p>'.$message.'</p>'."\n";
    }

    if ($num_args > 1 && DEBUG === TRUE) {
	if (isset($file) && isset($line)) {
	    echo '<p><em>Ошибка в строке '.$line.' в '.$file.'</em></p>'."\n";
	}
    }

    echo '<p>Мы уже в&nbsp;курсе и&nbsp;стараемся исправить проблему как можно быстрее.<br/>Возвращайтесь немного позже, всё уже будет работать.</p>';
?>
	</div>
    </div>
</body>
</html>
<?php
    exit();
}


function ami_printPage($content, $page_type='message_page') {
    global $ami_BaseURL, $ami_PageTitle, $ami_onDOMReady, $ami_addScript, $ami_onWindowReady, $ami_User, $ami_Menu, $ami_EnablePrintCSS, $ami_Production;

    if (!defined('AMI_ROOT')) {
		die('Not defined AMI_ROOT');
    }

    if (!defined('AMI_PAGE_TYPE')) {
		define('AMI_PAGE_TYPE', $page_type);
    }

    if (!defined('AMI_HEADER')) {
		require_once AMI_ROOT.'header.php';
    }

    echo $content;

    if (!defined('AMI_FOOTER')) {
		require_once AMI_ROOT.'footer.php';
    }
}

function ami_safeFileUnlink($file) {
    if (file_exists($file)) {
	unlink($file);
    }
}

function ami_format_filesize($bytes, $quoted=FALSE) {
    $span_start = ($quoted === TRUE) ? '<span class=\"filesize\">' : '<span class="filesize">';

    $bytes = intval($bytes, 10);

    if ($bytes < 1024) {
	    return "${bytes}&nbsp;".$span_start.'байт</span>';
    } else if ($bytes < 1048576) {
	    return round(($bytes/1024), 1).'&nbsp;'.$span_start.'КБ</span>';
    } else if ($bytes < 1073741824) {
	    return round(($bytes/1048576), 1).'&nbsp;'.$span_start.'МБ</span>';
    } else if ($bytes < 1099511627776) {
	    return round(($bytes/1073741824), 1).'&nbsp;'.$span_start.'ГБ</span>';
    } else {
	    return round(($bytes/1099511627776), 2).'&nbsp;'.$span_start.'ТБ</span>';
    }
}



function ami_GenerateRandomHash($maxLength=null, $strong=FALSE) {
    $entropy = '';

    // try ssl first
    if (function_exists('openssl_random_pseudo_bytes')) {
	$entropy = openssl_random_pseudo_bytes(64, $strong);
	// skip ssl since it wasn't using the strong algo
	if($strong !== true) {
	    $entropy = '';
	}
    }

    // add some basic mt_rand/uniqid combo
    $entropy .= uniqid(mt_rand(), TRUE);

    // try to read from the windows RNG
    if (class_exists('COM')) {
	try {
	    $com = new COM('CAPICOM.Utilities.1');
	    $entropy .= base64_decode($com->GetRandom(64, 0));
	} catch (Exception $ex) { }
    }

    // try to read from the unix RNG
    if (is_readable('/dev/urandom')) {
	$h = fopen('/dev/urandom', 'rb');
	$entropy .= fread($h, 64);
	fclose($h);
    }

    $hash = hash('whirlpool', $entropy);
    if ($maxLength) {
	return substr($hash, 0, $maxLength);
    }
    return $hash;
}

function ami_CreateNewPassword($maxLength=null) {
	return ami_GenerateRandomHash($maxLength);
}


function ami_cleanDir($dir) {
    if (!is_dir($dir)) {
	throw new Exception("Is not dir '$dir'");
    }

    if ($dh = opendir($dir)) {
	while (($current_file = readdir($dh)) !== FALSE) {
	    if ($current_file == '.' || $current_file == '..') {
		continue;
	    }

	    $full_filename = $dir.'/'.$current_file;

	    if (is_file($full_filename)) {
		if (!unlink($full_filename)) {
		    $log = Logger::sigleton();
		    $log->error('removeAllFilesInThisDir cant unlink: '.$full_filename);
		}
	    }
	}
	closedir($dh);
    } else {
	throw new Exception("Can not open dir '$dir'");
    }
}

function ami_redirect($url, $html = '', $title = 'Переадресация') {
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


// Generates a valid CSRF token for use when submitting a form to $target_url
// $target_url should be an absolute URL and it should be exactly the URL that the user is going to
// Alternately, if the form token is going to be used in GET (which would mean the token is going to be
// a part of the URL itself), $target_url may be a plain string containing information related to the URL.
function ami_MakeFormToken($target_url) {
    return sha1(str_replace('&amp;', '&', $target_url).ami_GetIP());
}


function ami_CheckFormToken($csrf='ss11:254BINGOdaf_fd') {
    if (!isset($_REQUEST['csrf_token'])) {
	return FALSE;
    }

    return ($csrf === $_REQUEST['csrf_token']);
}




function ami_SetCookie($name, $value, $expire) {
    global $ami_LoginCookieDomain, $ami_LoginCookiePath, $ami_LoginCookieSecure;

    // Enable sending of a P3P header
    header('P3P: CP="CUR ADM"');

    if (version_compare(PHP_VERSION, '5.2.0', '>=')) {
	setcookie($name, $value, $expire, $ami_LoginCookiePath, $ami_LoginCookieDomain, $ami_LoginCookieSecure, TRUE);
    } else {
	setcookie($name, $value, $expire, $ami_LoginCookiePath.'; HttpOnly', $ami_LoginCookieDomain, $ami_LoginCookieSecure);
    }
}


function ami_Pon($value, $str1, $str2, $str5) {
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


function ami_getRemoteData($sourceUrl, $connectTimeout=5, $timeout=10) {
    $curl_options = array(
	CURLOPT_RETURNTRANSFER 	=> TRUE,     // return web page
	CURLOPT_HEADER         	=> FALSE,    // don't return headers
	CURLOPT_FOLLOWLOCATION 	=> FALSE,     // follow redirects
	CURLOPT_ENCODING       	=> "UTF-8",       // handle all encodings
	CURLOPT_USERAGENT      	=> "AMI_FRAMEWORK", // who am i
	CURLOPT_AUTOREFERER    	=> TRUE,     // set referer on redirect
	CURLOPT_CONNECTTIMEOUT 	=> $connectTimeout,      // timeout on connect
	CURLOPT_TIMEOUT        	=> $timeout,      // timeout on response
	CURLOPT_SSL_VERIFYPEER	=> FALSE,
	CURLOPT_MAXREDIRS      	=> 2,       // stop after 10 redirects
    );

    $ch = curl_init($sourceUrl);
    curl_setopt_array($ch, $curl_options);
    $content = curl_exec($ch);
    $err = curl_errno($ch);
    $errmsg  = curl_error($ch);
    $header  = curl_getinfo($ch);
    curl_close($ch);


    if ($err != 0) {
	throw new Exception($errmsg);
    }

    return $content;
}


/* Debug logging */
function ami_debug($x, $m = null) {
    if (!defined('AMI_DEBUG') || !AMI_DEBUG) {
	return;
    }

    if (!defined('AMI_DEBUG_LOG')) {
	return;
    }


    if (is_writable(dirname(AMI_DEBUG_LOG)) && is_writable(AMI_DEBUG_LOG)) {
	if (is_array($x)) {
	    ob_start();
	    print_r($x);
	    $x = $m . ($m != null ? "\n" : '') . ob_get_clean();
	} else {
	    $x .= "\n";
	}

	error_log($x . "\n", 3, AMI_DEBUG_LOG);
    }
}

function ami_getmicrotime() {
	list($usec, $sec) = explode(" ",microtime());
	return ((float)$usec + (float)$sec);
}


function ami_BuildJS_ScriptSection($scripts) {
	$block = '';

	if (is_array($scripts) && count($scripts) > 0) {
		foreach ($scripts as $script) {
			$block .= '<script src="'.AMI_JS_BASE_URL.'js/'.$script.'" type="text/javascript"></script>';
		}
	}

	return $block;
}


function ami_GetRemoteFileSize($url, $connectTimeout=5, $timeout=5) {
    $size = 0;

    $curl_options = array(
		CURLOPT_HEADER         	=> FALSE,
		CURLOPT_FOLLOWLOCATION 	=> FALSE,     // follow redirects
		CURLOPT_ENCODING       	=> "UTF-8",       // handle all encodings
		CURLOPT_USERAGENT      	=> "PIC.lg.ua", // who am i
		CURLOPT_AUTOREFERER    	=> TRUE,     // set referer on redirect
		CURLOPT_CONNECTTIMEOUT 	=> $connectTimeout,      // timeout on connect
		CURLOPT_TIMEOUT        	=> $timeout,      // timeout on response
		CURLOPT_SSL_VERIFYPEER	=> FALSE,
		CURLOPT_MAXREDIRS      	=> 2,       // stop after 10 redirects
		CURLOPT_NOBODY			=> TRUE,
		CURLOPT_RETURNTRANSFER	=> FALSE,
    );

	// ON ERROR RETURN 0
	try {
	    $ch = curl_init($url);
	    curl_setopt_array($ch, $curl_options);
	    curl_exec($ch);
	    $header = curl_getinfo($ch);
	    curl_close($ch);
	} catch (Exception $e) {
		return $size;
	}

    if (isset($header['download_content_length'])) {
		$size = intval($header['download_content_length'], 10);
    }

    return $size;
}


function ami_CheckIs_URL($url) {
    return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
}

function ami_CheckIs_Email($url) {
    return FALSE;
}

function ami_GetOptions($a_options, $opt_name, $default_value) {
	if (isset($a_options[$opt_name])) {
		return $a_options[$opt_name];
	}

	return $default_value;
}


/*

Human Friendly dates by Invent Partners
We hope you enjoy using this free class.
Remember us next time you need some software expertise!
http://www.inventpartners.com

*/

class HumanRelativeDate{

	private $current_timestamp;
	private $current_timestamp_day;
	private $event_timestamp;
	private $event_timestamp_day;
	private $calc_time = false;   // Are we going to do times, or just dates?
	private $string = 'now';

	private $magic_5_mins = 300;
	private $magic_15_mins = 900;
	private $magic_30_mins = 1800;
	private $magic_1_hour = 3600;
	private $magic_1_day = 86400;
	private $magic_1_week = 604800;

	public function __construct(){

		$this->current_timestamp = time();
		$this->current_timestamp_day = mktime(0,  0 ,  0 , $month = date("n") , $day = date("j") , date("Y"));

	}

	public function getTextForSQLDate($sql_date){

		// Split SQL date into date / time
		@list($date , $time) = explode(' ' , $sql_date);
		// Split date in Y,m,d
		@list($Y,$m,$d) = explode('-' , $date);
		// Check that this is actually a valid date!
		if(@checkdate($m , $d , $Y)){
			// If we have a time, then we can show relative time calcs!
			if(isset($time) && $time){
				$this->calc_time = true;
				// Split tim in H,i,s
				@list($H,$i,$s) = explode(':' , $time);
			} else {
				$this->calc_time = false;
				$H=12;
				$i=0;
				$s=0;
			}
			// Set the event timestamp
			$this->event_timestamp = mktime($H, $i , $s , $m , $d , $Y);
			$this->event_timestamp_day = mktime(0 , 0 , 0 , $m , $d , $Y);

			//Get the string
			$this->getString();
		} else {
			$this->string = 'invalid date';
		}

		return $this->string;

	}

	public function getString(){

		// Is this today
		if($this->event_timestamp_day == $this->current_timestamp_day){
			if($this->calc_time){
				$this->calcTimeDiffString();
				return true;
			} else {
				$this->string = 'today';
				return true;
			}
		} else {
			$this->calcDateDiffString();
			return true;
		}

	}

	protected function calcTimeDiffString(){

		$diff = $this->event_timestamp - $this->current_timestamp;

		// Future events
		if($diff > 0){
			if($diff < $this->magic_5_mins){
				$this->string = 'now';
			} else if ($diff < $this->magic_15_mins){
				$this->string = 'in the next few minutes';
			} else if ($diff < $this->magic_30_mins){
				$this->string = 'in the next half hour';
			} else if ($diff < $this->magic_1_hour){
				$this->string = 'in the next hour';
			} else {
				$this->string = 'today at ' . date('H:i' , $this->event_timestamp);
			}
		}
		// Past Events
		else {
			$diff = abs($diff);
			if($diff < $this->magic_5_mins){
				$this->string = 'минуту назад';
			} else if ($diff < $this->magic_15_mins){
				$this->string = 'несколько минут назад';
			} else if ($diff < $this->magic_30_mins){
				$this->string = 'полчаса назад';
			} else if ($diff < $this->magic_1_hour){
				$this->string = 'менее часа назад';//'in the last hour';
			} else  if ($diff < ($this->magic_1_hour * 2)){
				$this->string = '1 hour ago';
			} else {
				$this->string = floor($diff / $this->magic_1_hour) . ' hours ago';
				//$this->string = 'today at ' . date('H:i' , $this->event_timestamp);
			}

		}

	}

	protected function calcDateDiffString(){

		$diff = $this->event_timestamp_day - $this->current_timestamp_day;

		// Future events
		if($diff > 0){
			//Tomorrow
			if($diff >= $this->magic_1_day && $diff < ($this->magic_1_day * 2)){
				$this->string = 'tomorrow';
				return true;
			} else if($diff <= $this->magic_1_week){
				// Find out if this date is this week or next!
				$current_day = date('w' , $this->current_timestamp_day);
				if($current_day == 0){
					$current_day = 7;
				}
				$event_day = date('w' , $this->event_timestamp_day);
				if($event_day == 0){
					$event_day = 7;
				}
				if($event_day > $current_day){
					$this->string = 'this ' . date('l' , $this->event_timestamp_day);
				} else {
					$this->string = 'next ' . date('l' , $this->event_timestamp_day);
				}
			} else if($diff <= ($this->magic_1_week * 2) ) {
				$this->string = 'a week on ' . date('l' , $this->event_timestamp_day);
			} else {
				$month_diff = $this->calcMonthDiff();
				if($month_diff == 0){
					$this->string = 'later this month';
				} else if($month_diff == 1){
					$this->string = 'next month';
				} else {
					$this->string = 'in ' . $month_diff . ' months';
				}
			}
		}
		// Historical events
		else {
			$diff = abs($diff);
			//Tomorrow
			if($diff >= $this->magic_1_day && $diff < ($this->magic_1_day * 2)){
				$this->string = 'yesterday';
				return true;
			} else if($diff <= $this->magic_1_week){
				$this->string = 'last ' . date('l' , $this->event_timestamp_day);
			} else if($diff <= ($this->magic_1_week * 2) ) {
				$this->string = 'over a week ago ';
			} else {
				$month_diff = $this->calcMonthDiff();
				if($month_diff == 0){
					$this->string = 'earlier this month';
				} else if($month_diff == 1){
					$this->string = 'last month';
				} else {
					if($month_diff > 12){
						$this->string = 'over a year ago';
					} else {
						$this->string = $month_diff . ' months ago';
					}
				}
			}

		}

	}

	protected function calcMonthDiff(){

		$event_month = intval( (date('Y' , $this->event_timestamp_day) * 12) + date('m' , $this->event_timestamp_day));
		$current_month = intval( (date('Y' , $this->current_timestamp_day) * 12) + date('m' , $this->current_timestamp_day));
		$month_diff = abs($event_month - $current_month);
		return $month_diff;

	}

}


?>
