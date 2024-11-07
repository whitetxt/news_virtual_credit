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

if (isset($_POST["flags"])) {
    $usr->flags = $_POST["flags"];
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
    log_error("Failed to update user", ["POST" => $_POST, "user" => $usr]);
    die(json_encode(["status" => "error", "message" => "Failed to update."]));
}

die(json_encode(["status" => "success"]));