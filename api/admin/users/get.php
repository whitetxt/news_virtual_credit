<?php
require_once "config.php";
require_once API_PATH . "/accounts/functions.php";
require_minimum_permissions($_COOKIE["sulv-token"], USER_PERMISSION_ADMIN);
require_once DB_PATH . "/users.php";

if (empty($_POST["name"])) {
	header("Content-Type: application/json");
	die(json_encode(array("status" => "error", "message" => "Missing fields.")));
	return;
}

$usr = get_user_from_username($_POST["name"]);
if ($usr === false) {
	header("Content-Type: application/json");
	die(json_encode(array("status" => "error", "message" => "User doesn't exist.")));
	return;
}

$usr->password = "";
$usr->salt = "";
$usr->token = "";
$usr->expires_at = 0;

header("Content-Type: application/json");
die(json_encode(array("status" => "success", "user" => $usr)));
?>