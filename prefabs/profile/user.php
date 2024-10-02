<?php
if (!array_key_exists("name", $_GET)) {
	if (!logged_in()) {
		$_GET["name"] = "No user specified.";
	} else {
		$_GET["name"] = current_user()->username;
	}
}
echo '
<div id="container">
	<div id="profile">
		<div id="username">' . $_GET["name"] . '</div>
		<div id="ranking">
			<span>Rankings:</span>
			<i class="fa-solid fa-globe" title="Globally">Coming Soon</i>';
if (region_to_flag($user->region) === "No Region") {
	echo '<i class="fa-solid" title="No Region">
	<span style="margin-right: 8px;">No Region</span>
	</i>';
} else {
	echo '<i class="fa-solid" title="' . $user->region . '">
	<span style="margin-right: 8px;">' . region_to_flag($user->region) . '</span>Coming Soon
	</i>';
}
echo '
		</div>
	</div>
	<div id="stats">
		<div id="songsplayed">' . $user->scores_submitted .' songs played</div>
		<div id="totalscore">Total Score: ' . number_format($user->total_score) . '</div>
	</div>
</div>';
?>