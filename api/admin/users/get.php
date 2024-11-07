<?php
require_once __DIR__ . "/../../config.php";
require_once API_PATH . "/accounts/functions.php";
require_flags($_COOKIE["sulv-token"], ["ADMIN"]);
require_once DB_PATH . "/users.php";

if (empty($_POST["name"])) {
    log_error("Missing fields", ["POST" => $_POST]);
    die(json_encode(["status" => "error", "message" => "Missing fields."]));
}

$usr = get_user_from_username($_POST["name"]);
if ($usr === false) {
    log_error("User doesn't exist", ["POST" => $_POST]);
    die(json_encode(["status" => "error", "message" => "User doesn't exist."]));
}

$usr->password = "";
$usr->salt = "";
$usr->token = "";
$usr->expires_at = 0;

log_action("User retrieved", ["POST" => $_POST, "user" => $usr]);
die(json_encode(["status" => "success", "user" => $usr]));