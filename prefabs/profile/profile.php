<?php
require_once DB_PATH . "/users.php";

if (!empty($_GET["name"])) {
	$user = get_user_from_username($_GET["name"]);
} else {
	$user = current_user();
}

if ($user === false) {
	echo '<div><h1>User not found.</h1></div>';
} else {
	require_once ("prefabs/misc/regions.php");
	require_once ("actions.php");
	require_once ("user.php");
	require_once ("scores.php");
}
?>