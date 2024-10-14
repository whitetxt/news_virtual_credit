<?php
require_once __DIR__ . "/../config.php";
require_once DB_PATH . "/users.php";
if (empty($_POST["username"]) || empty($_POST["password"])) {
    header("Content-Type: application/json");
    die(json_encode(array("status" => "error", "message" => "Username and password are required.")));
}

$username = $_POST["username"];
$password = $_POST["password"];

$db = new SQLite3(USERS_DB);

$stmt = $db->prepare("SELECT salt FROM users WHERE username = :usr");
$stmt->bindParam(":usr", $username);

$res = $stmt->execute();
if ($res === false) {
    $db->close();
    header("Content-Type: application/json");
    die(json_encode(array("status" => "error", "message" => "Incorrect username or password.")));
}

$arr = $res->fetchArray();
if ($arr === false) {
    $db->close();
    header("Content-Type: application/json");
    die(json_encode(array("status" => "error", "message" => "Incorrect username or password.")));
}
$stmt->close();

$salt = $arr["salt"];
$password = hash("sha512", $salt . $password);
$password = hash("whirlpool", $salt . $password);
$password = hash("sha256", $salt . $password);

$stmt = $db->prepare("SELECT password, enabled FROM users WHERE username = :usr");
$stmt->bindParam(":usr", $username);
$res = $stmt->execute();
if ($res === false) {
    $db->close();
    header("Content-Type: application/json");
    die(json_encode(array("status" => "error", "message" => "Incorrect username or password.")));
}

$arr = $res->fetchArray();
if ($arr === false) {
    $db->close();
    header("Content-Type: application/json");
    die(json_encode(array("status" => "error", "message" => "Incorrect username or password.")));
}
$stmt->close();

if ($arr["enabled"] === null) {
    $db->close();
    header("Content-Type: application/json");
    die(json_encode(array("status" => "error", "message" => "This account is disabled.")));
}

if ($arr["password"] !== $password) {
    $db->close();
    header("Content-Type: application/json");
    die(json_encode(array("status" => "error", "message" => "Incorrect username or password.")));
}

if (isset($_POST["remember"]) && $_POST["remember"] == "1") {
    $expires_at = time() + 60 * 60 * 24 * 365;
} else {
    $expires_at = time() + 60 * 60 * 24;
}

$token = bin2hex(random_bytes(128));

$res = create_session($username, $token, $expires_at);

if ($res === false) {
    $db->close();
    die(json_encode(["status" => "error", "message" => "Failed to create session."]));
}
$db->close();

if (isset($_POST["remember"]) && $_POST["remember"] == "1") {
    setcookie("sulv-token", $token, time() + 60 * 60 * 24 * 31, "/");
} else {
    setcookie("sulv-token", $token, time() + 60 * 60 * 24 * 7, "/");
}
header("Content-Type: application/json");
die(json_encode(array("status" => "success")));