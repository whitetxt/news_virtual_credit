<?php
require_once __DIR__ . "/../../config.php";
require_once API_PATH . "/accounts/functions.php";
require_minimum_permissions($_COOKIE["sulv-token"], USER_PERMISSION_ADMIN);
require_once DB_PATH . "/users.php";

if (empty($_POST["name"])) {
    header("Content-Type: application/json");
    die(json_encode(["status" => "error", "message" => "Missing fields."]));
}

$usr = get_user_from_username($_POST["name"]);
if ($usr === false) {
    header("Content-Type: application/json");
    die(json_encode(["status" => "error", "message" => "User doesn't exist."]));
}

$usr->password = "";
$usr->salt = "";
$usr->token = "";
$usr->expires_at = 0;

header("Content-Type: application/json");
die(json_encode(["status" => "success", "user" => $usr]));