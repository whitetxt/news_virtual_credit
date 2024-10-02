<?php
require_once ("flags.php");
function region_to_flag($region) {
	global $regions;
	if (!key_exists($region, $regions)) {
		return "No Region";
	}
	return $regions[$region];
}

function get_regions() {
	global $regions;
	return array_keys($regions);
}
?>