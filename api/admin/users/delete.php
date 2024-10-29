<?php
require_once __DIR__ . "/../../config.php";
require_once API_PATH . "/accounts/functions.php";
require_flags($_COOKIE["sulv-token"], ["ADMIN"]);
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

delete_user($usr->username);

header("Content-Type: application/json");
die(json_encode(array("status" => "success")));
?>