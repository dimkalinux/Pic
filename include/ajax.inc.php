<?php

// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit;
}

//sleep(5);

class AJAX extends AMI_Ajax {

	//
	public function url_shortener_link() {
		global $out, $result, $ami_User;

		try {
			$key_id = isset($_POST['t_key_id']) ? ami_get_safe_string_len($_POST['t_key_id'], 32) : FALSE;
			$key_delete = isset($_POST['t_key_delete']) ? $_POST['t_key_delete'] : FALSE;

			if (!$key_id || !$key_delete) {
				throw new Exception('отсутствует необходимый аргумент');
			}

			$url = ami_link('show_image', $key_id);

			// CHECK OWNER
			$db = DB::singleton();
			$row = $db->getRow("SELECT * FROM pic WHERE id_key=? AND delete_key=? LIMIT 1", $key_id, $key_delete);

			if (!$row) {
				throw new Exception('файл не найден');
			}

			//
			$pic_id = $row['id'];

			$user_prefered_service = AMI_User_Info::getConfigValue($ami_User['id'], 'shortener_service', URL_SHORTENER_BITLY);
			$url_short = new URL_Shortener($user_prefered_service);
			$out = $url_short->shorten($url);

			// ADD to DB
			$db->query('UPDATE pic SET short_url=? WHERE id=? LIMIT 1', $out, $pic_id);

			$result = AMI_AJAX_RESULT_OK;
		} catch (Exception $e) {
			$this->exitWithError($e->getMessage());
		}
	}

}

?>
