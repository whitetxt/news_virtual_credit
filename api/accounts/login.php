<?php
require_once "../config.php";
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

$stmt = $db->prepare("UPDATE users SET token = :tkn, expires_at = :exp WHERE username = :usr");
$stmt->bindParam(":usr", $username, SQLITE3_TEXT);
if (isset($_POST["remember"]) && $_POST["remember"] == "1") {
    $stmt->bindValue(":exp", null, SQLITE3_NULL);
} else {
    $stmt->bindValue(":exp", time() + 60 * 60 * 24, SQLITE3_INTEGER);
}

$token = bin2hex(random_bytes(128));
$stmt->bindParam(":tkn", $token, SQLITE3_TEXT);

$res = $stmt->execute();
if ($res === false) {
    $db->close();
    die("Failed to execute database query.");
}
$stmt->close();
$db->close();

if (isset($_POST["remember"]) && $_POST["remember"] == "1") {
    setcookie("sulv-token", $token, time() + 60 * 60 * 24 * 31, "/");
} else {
    setcookie("sulv-token", $token, time() + 60 * 60 * 24 * 7, "/");
}
header("Content-Type: application/json");
die(json_encode(array("status" => "success")));