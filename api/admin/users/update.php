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

if (isset($_POST["access_level"])) {
    $usr->access_level = $_POST["access_level"];
}

if (isset($_POST["enabled"])) {
    if ($_POST["enabled"] == "enabled") {
        $usr->enabled = true;
    } else {
        $usr->enabled = false;
    }
}

if (isset($_POST["role"])) {
    $usr->role = $_POST["role"];
}

if (isset($_POST["pass"])) {
    $password = $_POST["pass"];
    $salt = $usr->salt;
    $password = hash("sha512", $salt . $password);
    $password = hash("whirlpool", $salt . $password);
    $password = hash("sha256", $salt . $password);
    $usr->password = $password;
}

$res = update_user($usr);
if ($res === false) {
    header("Content-Type: application/json");
    die(json_encode(["status" => "error", "message" => "Failed to update."]));
}

header("Content-Type: application/json");
die(json_encode(["status" => "success"]));