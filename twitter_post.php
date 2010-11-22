<?php

if (!defined('AMI_ROOT')) {
	define('AMI_ROOT', './');
}

require AMI_ROOT.'functions.inc.php';


try {
	$ami_PageTitle = 'В твитер!';

	if (isset($_GET['ok'])) {
		$post_link = isset($_GET['l']) ? urldecode($_GET['l']) : FALSE;
		if ($post_link) {
			ami_show_message('Сообщение отправлено', 'Cсылка на сообщение: <a href="'.$post_link.'">'.ami_htmlencode($post_link).'</a>');
		} else {
			ami_show_message('Спасибо', 'Сообщение отправлено');
		}
	}


	$key_id = isset($_GET['k']) ? ami_get_safe_string_len($_GET['k'], 32) : FALSE;
	$form_action = ami_link('twitter_post', $key_id);
	$csrf = ami_MakeFormToken($form_action);
	$async = isset($_GET['async']);
	$back_link = ami_link('profile');


	if (!$key_id) {
		throw new AppLevelException('Недостаточно параметров в запросе');
	}

	$db = DB::singleton();
	$row = $db->getRow("SELECT * FROM pic WHERE id_key=? LIMIT 1", $key_id);

	if (!$row) {
		throw new AppLevelException('Ссылка не&nbsp;верна или устарела.<br/>Возможно файл был удалён.');
	}

	// CHECK OWNER
	if (($is_owner === FALSE) && ($ami_User['is_guest'] === FALSE)) {
		$is_owner = ($ami_User['id'] === (int)/**/$row['owner_id']);
	}

	if ($is_owner === FALSE) {
		throw new AppLevelException('Только для владельца файла');
	}

	$pic_id = $row['id'];
	$storage = ami_get_safe_string($row['storage']);
	$location = ami_get_safe_string($row['location']);
	$hash_filename = $row['hash_filename'];
	$filename = ami_htmlencode($row['filename']);
	$short_link = $row['short_url'];
	$preview_link = pic_getImageLink($storage, $location, $hash_filename, PIC_IMAGE_SIZE_MIDDLE);

$form = <<<FMB
<div class="span-10 last prepend-5 body_block last">
	%s
	<h2>$ami_PageTitle</h2>

	<form method="post" action="$form_action" name="twitter_post" accept-charset="utf-8">
		<input type="hidden" name="form_sent" value="1">
		<input type="hidden" name="csrf_token" value="$csrf">

		<div>
			<img class="fancy_image" src="$preview_link" alt="$filename">
		</div>

		<div class="prepend-top">
			<div style="position: relative;">
				<label for="twitter_post_text">Текст сообщения</label>
				<div id="twitter_post_counter">140</div>
			</div>
			<textarea rows="4" id="twitter_post_text" tabindex="1" name="m"></textarea>
		</div>

		<div class="formRow buttons">
			<input class="button" type="submit" name="do" value="Твит" tabindex="2">
			или <a href="$back_link">вернуться</a>
		</div>
	</form>
</div>
FMB;

	if (isset($_POST['form_sent'])) {
		// 1. check csrf
		if (!ami_CheckFormToken($csrf)) {
			throw new InvalidInputDataException('Действие заблокировано системой безопасности');
		}

		$twitter_user = new AMI_User_Twitter($ami_User['id']);
		$twitter_user_tokens = $twitter_user->get_oauth_tokens();

		$connection = new TwitterOAuth(TWITTER_CONSUMER_KEY, TWITTER_CONSUMER_SECRET, $twitter_user_tokens['oauth_token'], $twitter_user_tokens['oauth_token_secret']);

		$message = isset($_POST['m']) ? $_POST['m'] : FALSE;

		if (FALSE === $message) {
			throw new AppLevelException('Пустое сообщение');
		}

		// Add link
		$show_link = ami_link('show_image', $key_id);
		if (!empty($row['short_url'])) {
			$show_link = $row['short_url'];
		}

		$result = $connection->post('statuses/update', array('status' => $message.' '.$show_link));
		if (isset($result->error)) {
			throw new AppLevelException('Твитер вернул ошибку: '.ami_htmlencode($result->error));
		}

		if (200 !== $connection->http_code) {
			throw new AppLevelException('Нет доступа к сайту Твитера. Повторите попытку позже.');
		}

		$post_link = urlencode(sprintf('https://twitter.com/%s/status/%s', $result->user->name, $result->id_str));

		// ADD POST to DB
		$db->query("INSERT INTO twitter_posts VALUES('', ?, ?, ?, ?)", $pic_id, $result->user->id_str, $result->id_str, strtotime((string)/**/$result->created_at));

		// is async request
		if ($async) {
			ami_async_response(array('error'=> 0, 'message' => $post_link), AMI_ASYNC_JSON);
		} else {
			ami_redirect(ami_link('twitter_post_ok', $post_link));
		}
	}

	ami_printPage(sprintf($form, ''));
} catch (AppLevelException $e) {
	if ($async) {
		ami_async_response(array('error'=> 1, 'message' => $e->getMessage()), AMI_ASYNC_JSON);
	} else {
		ami_printPage(sprintf($form, '<div class="span-20"><div class="error span-10 last">'.$e->getMessage().'</div></div>'));
		exit();
	}
} catch (Exception $e) {
	if ($async) {
		ami_async_response(array('error'=> 1, 'message' => $e->getMessage()), AMI_ASYNC_JSON);
	} else {
		ami_show_error($e->getMessage());
	}
}



?>
