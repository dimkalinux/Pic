<?
// Make sure no one attempts to run this script "directly"
if (!defined('UP')) {
	exit;
}
?>
	</div>
</div>
<div id="footer">
	<ul id="sitenav">
		<li class="copyright">©&nbsp;<? date_default_timezone_set(CONFIG_TIMEZONE); echo (date ("Y")); ?> <a href="http://iteam.ua/">iTeam</a></li>
		<li class="first"><a href="">Портал</a></li>
		<li><a href="http://forum.iteam.net.ua/">Форум</a></li>
		<li><a href="http://up.iteam.net.ua/">АП</a></li>
		<li><a href="http://film.lg.ua/">Фильмы</a></li>
		<li><a href="http://serial.iteam.net.ua/">Сериалы</a></li>
	</ul>
	<!--Made on Omicron Persei VIII. Designed on Earth.-->
</div>
<script src="<?php echo JS_BASE_URL; ?>js/jquery.js" type="text/javascript"></script>
<?php


// ADDON JS-SCRIPT BLOCK
if (isset($addScript) && is_array($addScript) && count($addScript) > 0) {
	// remove non-uniq values
	$addScript = array_unique($addScript);
	foreach ($addScript as $script) {
		echo '<script src="/js/'.$script.'" type="text/javascript"></script>';
	}
}




// ON-DOM-READY BLOCK
if (isset($onDOMReady) && is_array($onDOMReady) && count($onDOMReady) > 0) {
	// remove non-uniq values
	$onDOMReady = array_unique($onDOMReady);
	echo '<script type="text/javascript">$(document).ready(function () { '.implode("\n", $onDOMReady).'});</script>';
}


// GOOGLE ANALYTICS BLOCK
if (isset($googleAnalyticsCode) && !empty($googleAnalyticsCode)) {
	$gaCodeBlock = <<<FMB
<script type="text/javascript">$(document).ready(function() { $.ga.load('$googleAnalyticsCode'); } );</script>
FMB;
	echo $gaCodeBlock;
}

define('UP_FOOTER', 1);
?>
</body>
</html>
