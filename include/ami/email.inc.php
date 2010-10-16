<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
    exit();
}

function ami_IsValidEmail($email) {
    if (utf8_strlen($email) > 128) {
    	return FALSE;
    }

    return preg_match('/^(([^<>()[\]\\.,;:\s@"\']+(\.[^<>()[\]\\.,;:\s@"\']+)*)|("[^"\']+"))@((\[\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}\])|(([a-zA-Z\d\-]+\.)+[a-zA-Z]{2,}))$/ui', $email);
}


//
// Wrapper for PHP's mail()
//
function ami_SendEmail($to, $subject, $message, $reply_to_email = '', $reply_to_name = '') {
	global $ami_mailUseSMTP, $ami_mailSMTP_Server, $ami_mailSMTP_User, $ami_mailSMTP_Password, $ami_mailDefaultFromName, $ami_mailDefaultFromEmail;

	// Default sender address
	$from_name = $ami_mailDefaultFromName;
	$from_email = $ami_mailDefaultFromEmail;

	// Do a little spring cleaning
	$to = ami_trim(preg_replace('#[\n\r]+#s', '', $to));
	$subject = ami_trim(preg_replace('#[\n\r]+#s', '', $subject));
	$from_email = ami_trim(preg_replace('#[\n\r:]+#s', '', $from_email));
	$from_name = ami_trim(preg_replace('#[\n\r:]+#s', '', str_replace('"', '', $from_name)));
	$reply_to_email = ami_trim(preg_replace('#[\n\r:]+#s', '', $reply_to_email));
	$reply_to_name = ami_trim(preg_replace('#[\n\r:]+#s', '', str_replace('"', '', $reply_to_name)));

	// Set up some headers to take advantage of UTF-8
	$from = "=?UTF-8?B?".base64_encode($from_name)."?=".' <'.$from_email.'>';
	$subject = "=?UTF-8?B?".base64_encode($subject)."?=";

	$headers = 'From: '.$from."\r\n".'Date: '.gmdate('r')."\r\n".'MIME-Version: 1.0'."\r\n".'Content-transfer-encoding: 8bit'."\r\n".'Content-type: text/plain; charset=utf-8'."\r\n".'X-Mailer: AMI Framework Mailer';

	// If we specified a reply-to email, we deal with it here
	if (!empty($reply_to_email)) {
		$reply_to = "=?UTF-8?B?".base64_encode($reply_to_name)."?=".' <'.$reply_to_email.'>';
		$headers .= "\r\n".'Reply-To: '.$reply_to;
	}

	// Make sure all linebreaks are CRLF in message (and strip out any NULL bytes)
	$message = str_replace(array("\n", "\0"), array("\r\n", ''), ami_linebreaks($message));

	if ($ami_mailUseSMTP) {
		smtp_mail($to, $subject, $message, $headers);
	} else	{
		// Change the linebreaks used in the headers according to OS
		if (strtoupper(substr(PHP_OS, 0, 3)) == 'MAC') {
			$headers = str_replace("\r\n", "\r", $headers);
		} else if (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN') {
			$headers = str_replace("\r\n", "\n", $headers);
		}

		mail($to, $subject, $message, $headers);
	}
}


//
// This function was originally a part of the phpBB Group forum software phpBB2 (http://www.phpbb.com).
// They deserve all the credit for writing it. I made small modifications for it to suit PunBB and it's coding standards.
//
function server_parse($socket, $expected_response) {
	$server_response = '';
	while (substr($server_response, 3, 1) != ' ')
	{
		if (!($server_response = fgets($socket, 256))) {
			throw new Exception('Couldn\'t get mail server response codes. Please contact the forum administrator.');
		}
	}

	if (!(substr($server_response, 0, 3) == $expected_response)) {
		throw new Exception('Unable to send e-mail. Please contact the forum administrator with the following error message reported by the SMTP server: "'.$server_response.'"');
	}
}


//
// This function was originally a part of the phpBB Group forum software phpBB2 (http://www.phpbb.com).
// They deserve all the credit for writing it. I made small modifications for it to suit PunBB and it's coding standards.
//
function smtp_mail($to, $subject, $message, $headers = '') {
	global $ami_mailUseSMTP, $ami_mailSMTP_Server, $ami_mailSMTP_User, $ami_mailSMTP_Password, $ami_mailDefaultFromName, $ami_mailDefaultFromEmail;

	$recipients = explode(',', $to);

	// Sanitize the message
	$message = str_replace("\r\n.", "\r\n..", $message);
	$message = (substr($message, 0, 1) == '.' ? '.'.$message : $message);

	// Are we using port 25 or a custom port?
	if (strpos($ami_mailSMTP_Server, ':') !== FALSE) {
		list($smtp_host, $smtp_port) = explode(':', $ami_mailSMTP_Server);
	} else 	{
		$smtp_host = $ami_mailSMTP_Server;
		$smtp_port = 25;
	}

/*	if ($forum_config['o_smtp_ssl'] == '1')
		$smtp_host = 'ssl://'.$smtp_host;*/

	if (!($socket = fsockopen($smtp_host, $smtp_port, $errno, $errstr, 15))) {
		throw new Exception('Could not connect to smtp host "'.$ami_mailSMTP_Server.'" ('.$errno.') ('.$errstr.').');
	}

	server_parse($socket, '220');

	if ($ami_mailSMTP_User != '' && $ami_mailSMTP_Password != '') {
		fwrite($socket, 'EHLO '.$smtp_host."\r\n");
		server_parse($socket, '250');

		fwrite($socket, 'AUTH LOGIN'."\r\n");
		server_parse($socket, '334');

		fwrite($socket, base64_encode($ami_mailSMTP_User)."\r\n");
		server_parse($socket, '334');

		fwrite($socket, base64_encode($ami_mailSMTP_Password)."\r\n");
		server_parse($socket, '235');
	} else 	{
		fwrite($socket, 'HELO '.$smtp_host."\r\n");
		server_parse($socket, '250');
	}

	fwrite($socket, 'MAIL FROM: <'.$ami_mailDefaultFromEmail.'>'."\r\n");
	server_parse($socket, '250');

	foreach ($recipients as $email) {
		fwrite($socket, 'RCPT TO: <'.$email.'>'."\r\n");
		server_parse($socket, '250');
	}

	fwrite($socket, 'DATA'."\r\n");
	server_parse($socket, '354');

	fwrite($socket, 'Subject: '.$subject."\r\n".'To: <'.implode('>, <', $recipients).'>'."\r\n".$headers."\r\n\r\n".$message."\r\n");

	fwrite($socket, '.'."\r\n");
	server_parse($socket, '250');

	fwrite($socket, 'QUIT'."\r\n");
	fclose($socket);

	return TRUE;
}



?>
