<?php

if (!defined('UP_ROOT')) {
	define('UP_ROOT', './');
}

require UP_ROOT.'functions.inc.php';
require UP_ROOT.'header.php';
?>
	<h1>Загрузить картинку на вечное хранение</h1>

	<form method="post" action="/upload.php" name="" enctype="multipart/form-data" accept-charset="utf-8">
		<input type="hidden" name="form_sent" value="1"/>
		<div class="formRow">
			<input type="file" name="feedbackUserEmail" tabindex="1"/>
			<input type="submit" name="do" value="Загрузить" tabindex="2"/>
		</div>
		<div class="formRow buttons">
			<div class="inputHelp">jpeg, png до 10 мегабайт</div>
		</div>
	</form>


<?php
require UP_ROOT.'footer.php';
?>
