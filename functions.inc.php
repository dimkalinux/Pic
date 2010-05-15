<?php

if (!defined('UP_ROOT')) {
	exit('The constant UP_ROOT must be defined.');
}


mb_internal_encoding("UTF-8");

// Reverse the effect of register_globals
up_unregister_globals();

// Ignore any user abort requests
ignore_user_abort(TRUE);

// Attempt to load the configuration file config.php
if (file_exists(UP_ROOT.'config.inc.php')) {
	include UP_ROOT.'config.inc.php';
}

if (!defined('UP')) {
	die("Файл конфигурации «config.inc.php» не найден или повреждён.");
}

// Block prefetch requests
if (isset($_SERVER['HTTP_X_MOZ']) && $_SERVER['HTTP_X_MOZ'] == 'prefetch') {
	header('HTTP/1.1 403 Prefetching Forbidden');

	// Send no-cache headers
	header('Expires: Thu, 21 Jul 1977 07:30:00 GMT');	// When yours truly first set eyes on this world! :)
	header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
	header('Cache-Control: post-check=0, pre-check=0', FALSE);
	header('Pragma: no-cache');		// For HTTP/1.0 compability

	exit;
}

if (DEBUG === TRUE) {
	error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
}

// GLOBAL VARIABLES
$addScript = $onDOMReady = array();

// LOAD UTF-8 FUNCTIONS
require UP_ROOT.'include/utf8/utf8.php';
require UP_ROOT.'include/utf8/ucwords.php';
require UP_ROOT.'include/utf8/trim.php';

// LOAD ALL LIBS
require UP_ROOT.'include/exceptions.inc.php';
require UP_ROOT.'include/url.inc.php';
require UP_ROOT.'include/common.inc.php';
require UP_ROOT.'include/db.inc.php';
require UP_ROOT.'include/logger.inc.php';


function get_safe_string($str) {
    return preg_replace ("/[^a-z0-9]/i", "", $str);
}

function get_safe_string_len($str, $maxLength) {
    return substr(preg_replace("/[^a-z0-9]/i", "", $str), 0, $maxLength);
}


function pic_htmlencode($str) {
	return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

function pic_htmldecode($str) {
	return htmlspecialchars_decode($str, ENT_QUOTES);
}

// Trim whitespace including non-breaking space
function portal_trim($str, $charlist = " \t\n\r\x0b\xc2\xa0") {
    return utf8_trim($str, $charlist);
}

function ami_link($link, $args = null) {
    global $amiBaseUrl, $ami_urls;

    $gen_link = $ami_urls[$link];
    if ($args == null) {
	$gen_link = $amiBaseUrl.'/'.$gen_link;
    } else if (!is_array($args)) {
	$gen_link = $amiBaseUrl.'/'.str_replace('$1', $args, $gen_link);
    } else {
	for ($i = 0; isset($args[$i]); ++$i) {

	    $gen_link = str_replace('$'.($i + 1), $args[$i], $gen_link);
	}
	$gen_link = $amiBaseUrl.'/'.$gen_link;
    }

    return $gen_link;
}

function ami_show_error_message($message) {
    $out = <<<FMB
    <div id="status">&nbsp;</div>
    <h1>Ошибка</h1>
    <div class="message">$message</div>
FMB;
    ami_printPage($out);
    exit();
}


function clear_stat_cache() {
	$cache = new Cache();
	$cache->clearStat();
}



function get_client_ip() {
	if (isset($_SERVER['REMOTE_ADDR'])) {
		return $_SERVER['REMOTE_ADDR'];
	} else {
		return null;
	}
}


function get_geo() {
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
function up_unregister_globals()
{
	$register_globals = @ini_get('register_globals');
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

// Generates a valid CSRF token for use when submitting a form to $target_url
// $target_url should be an absolute URL and it should be exactly the URL that the user is going to
// Alternately, if the form token is going to be used in GET (which would mean the token is going to be
// a part of the URL itself), $target_url may be a plain string containing information related to the URL.
function generate_form_token($target_url) {
	return sha1(str_replace('&amp;', '&', $target_url).get_client_ip());
}

function check_form_token($csrf='ss11254BINGO') {
	if (!isset($_REQUEST['csrf_token'])) {
		return FALSE;
	}

	return ($csrf === $_REQUEST['csrf_token']);
}

function format_filesize($bytes, $quoted=FALSE) {
	$span_start = ($quoted === TRUE) ? '<span class=\"filesize\">' : '<span class="filesize">';

	if ($bytes < 1024) {
		return "${bytes}&nbsp;".$span_start.'б</span>';
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
    while (@ob_end_clean());

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" dir="ltr">
<head>
<title>Error</title>
</head>
<body style="width: 35em; margin: 40px; color: #2b2b2b; background: #fff; font:13px/1.331 arial,helvetica,clean,sans-serif; *font-size:small; /* for IE */ 	*font:x-small; /* for IE in quirks mode */">
<h2>Роковая ошибка сервиса</h2>
<hr/>
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

</body>
</html>
<?php
	exit;
}

//
// Validate an e-mail address
//
function is_valid_email($email) {
	if (utf8_strlen($email) > 128) {
		return FALSE;
	}

	return preg_match('/^(([^<>()[\]\\.,;:\s@"\']+(\.[^<>()[\]\\.,;:\s@"\']+)*)|("[^"\']+"))@((\[\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\])|(([a-zA-Z\d\-]+\.)+[a-zA-Z]{2,}))$/ui', $email);
}


function ami_printPage($content, $page_name='main_page') {
	global $base_url, $user, $page_title;

	if (!defined('UP_ROOT')) {
		die('Not defined UP_ROOT');
	}

	if (!defined('UP_HEADER')) {
		require_once UP_ROOT.'header.php';
	}

	echo $content;

	if (!defined('UP_FOOTER')) {
		require_once UP_ROOT.'footer.php';
	}
}

function getServerLoad() {
	$load = sys_getloadavg();
	return $load[0];
}

function httpError404() {
	global $base_url;

	header("Location: {$base_url}404.html");
	exit();
}

function safeUnlink($file) {
	if (file_exists($file)) {
		unlink($file);
	}
}


function get_microtime() {
	list($usec, $sec) = explode(' ', microtime());
	return ((float)$usec + (float)$sec);
}

function generate_random_hash($maxLength=null) {
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
	    $entropy .= uniqid(mt_rand(), true);

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


function pic_getImageLink($storage, $location, $hashed_filename, $size) {
    switch ($size) {
	case IMAGE_SIZE_SMALL:
	    $image_link = 'sm_'.$hashed_filename;
	    break;

	case IMAGE_SIZE_MIDDLE:
	    $image_link = 'md_'.$hashed_filename;
	    break;

	case IMAGE_SIZE_PREVIEW:
	    $image_link = 'pv_'.$hashed_filename;
	    break;

	case IMAGE_SIZE_ORIGINAL:
	    $image_link = $hashed_filename;
	    break;

	default:
	    throw new Exception('Out of range');
	    break;
    }

    return ami_link('image', array($storage, $location, $image_link));
}

?>
