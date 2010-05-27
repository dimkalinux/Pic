<?
// Make sure no one attempts to run this script "directly"
if (!defined('AMI')) {
	exit();
}
?>
</div>
<script src="<?php echo AMI_JS_BASE_URL; ?>js/jquery.js" type="text/javascript"></script>
<?php


// ADDON JS-SCRIPT BLOCK
if (isset($ami_addScript) && is_array($ami_addScript) && count($ami_addScript) > 0) {
	// remove non-uniq values
	$ami_addScript = array_unique($ami_addScript);
	foreach ($ami_addScript as $script) {
		echo '<script src="/js/'.$script.'" type="text/javascript"></script>';
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


define('AMI_FOOTER', 1);
?>
</body>
</html>
