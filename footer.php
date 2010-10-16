<?php
// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit();
}
?>
</div>
<?php
if (AMI_PRODUCTION): ?>
	<script src="<?php echo AMI_JS_BASE_URL; ?>/j/lib.js" type="text/javascript"></script>
<?php else: ?>
	<script src="<?php echo AMI_JS_BASE_URL; ?>/js/jquery.js" type="text/javascript"></script>
	<script src="<?php echo AMI_JS_BASE_URL; ?>/js/ami.log.js" type="text/javascript"></script>
	<script src="<?php echo AMI_JS_BASE_URL; ?>/js/pic.trash.js" type="text/javascript"></script>
	<script src="<?php echo AMI_JS_BASE_URL; ?>/js/pic.upload.js" type="text/javascript"></script>
	<script src="<?php echo AMI_JS_BASE_URL; ?>/js/pic.ajaxify.js" type="text/javascript"></script>
<?php
endif;

// ADDON JS-SCRIPT BLOCK
if (isset($ami_addScript) && is_array($ami_addScript) && count($ami_addScript) > 0) {
	// remove non-uniq values
	$ami_addScript = array_unique($ami_addScript);
	$js_path = (AMI_PRODUCTION) ? 'j' : 'js';

	foreach ($ami_addScript as $script) {
		echo '<script src="/'.$js_path.'/'.$script.'" type="text/javascript"></script>';
	}
}


// GOOGLE ANALYTICS BLOCK
if (isset($ami_googleAnalyticsCode) && !empty($ami_googleAnalyticsCode) && ($page_name == 'main_page')) {
	ami_addOnDOMReady("$.ga.load('$ami_googleAnalyticsCode');");
}

// ON-DOM-READY BLOCK
if (isset($ami_onDOMReady) && is_array($ami_onDOMReady) && count($ami_onDOMReady) > 0) {
	// remove non-uniq values
	$ami_onDOMReady = array_unique($ami_onDOMReady);
	echo '<script type="text/javascript">$(document).ready(function () { '.implode("\n", $ami_onDOMReady).' });</script>';
}

// ON-WINDOW-READY BLOCK
if (isset($ami_onWindowReady) && is_array($ami_onWindowReady) && count($ami_onWindowReady) > 0) {
	// remove non-uniq values
	$ami_onWindowReady = array_unique($ami_onWindowReady);
	echo '<script type="text/javascript">$(document).ready(function () { '.implode("\n", $ami_onWindowReady).' });</script>';
}

define('AMI_FOOTER', 1);
?>
</body>
</html>
