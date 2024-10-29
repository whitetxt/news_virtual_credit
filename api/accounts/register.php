<?php
require_once __DIR__ . "/../config.php";
require_once DB_PATH . "/users.php";

if (empty($_POST["username"]) || empty($_POST["password"]) || empty($_POST["enabled"] || empty($_POST["flags"]))) {
    header("Content-Type: application/json");
    die(json_encode(array("status" => "error", "message" => "Username, password, enabled and access level are required.")));
}

$username = $_POST["username"];
$password = $_POST["password"];
$enabled = $_POST["enabled"];
$flags = $_POST["flags"];

$salt = bin2hex(random_bytes(16));
$password = hash("sha512", $salt . $password);
$password = hash("whirlpool", $salt . $password);
$password = hash("sha256", $salt . $password);

$existing_user = get_user_from_username($username);
if ($existing_user !== false) {
    header("Content-Type: application/json");
    die(json_encode(array("status" => "error", "message" => "Username is taken.")));
}

$token = bin2hex(random_bytes(128));
$result = create_new_user($username, $password, $salt, $token);

if ($result === false) {
    header("Content-Type: application/json");
    die(json_encode(array("status" => "error", "message" => "Failed to execute database query.")));
}

$user = get_user_from_username($username);
$user->flags = $flags;
$user->enabled = $enabled === "enabled";
$result = update_user($user);

if ($result === false) {
    header("Content-Type: application/json");
    die(json_encode(array("status" => "error", "message" => "Failed to execute database query.")));
}

header("Content-Type: application/json");
die(json_encode(array("status" => "success")));