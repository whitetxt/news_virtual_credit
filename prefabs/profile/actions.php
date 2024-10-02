<?php
echo '<div id="settings">';
if ($user->username === current_user()->username) {
	echo '
		<a href="settings.php">
			<span class="material-symbols-rounded">
				manage_accounts
			</span>
			<span>Manage Profile</span>
		</a>';
}
echo '
<a href="javascript:navigator.clipboard.writeText(window.location.protocol + \'//\' + window.location.host + window.location.pathname + \'?name=' . $user->username . '\');create_alert(\'URL Copied!\', 3, \'SUCCESS\')">
	<span class="material-symbols-rounded">
		share
	</span>
	<span>Share Profile</span>
</a>';
echo '</div>';
?>