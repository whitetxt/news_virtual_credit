<?php
require_once "./config.php";
require_once API_PATH . "/accounts/functions.php";
if (!isset($_POST["username"]) || !isset($_POST["secret"]) || !isset($_POST["amount"])) {
    die(json_encode(array("success" => false, "error" => "Missing parameters.")));
}

if (!logged_in()) {
    die(json_encode(array("success" => false, "error" => "Not logged in.")));
}

$me = current_user();

$username = $_POST["username"];
$secret = $_POST["secret"];
$amount = floatval($_POST["amount"]);

require_once DB_PATH . "/money.php";

$charge = get_user_from_username($username);
if ($secret !== $charge->secret) {
    die(json_encode(["success" => false, "error" => "Incorrect secret (QR code is likely old, refresh page)."]));
}

if ($charge->balance < $amount) {
    die(json_encode(["success" => false, "error" => "Insufficient funds."]));
}

$charge->balance -= $amount;
if (!update_user($charge)) {
    die(json_encode(["success" => false, "error" => "Unknown error. Try again."]));
}

$db = new SQLite3(USERS_DB, SQLITE3_OPEN_READWRITE);

$stmt = $db->prepare("INSERT INTO transactions(username, type, amount, description, time) VALUES(:usr, :typ, :amt, :desc, :tim)");
$stmt->bindParam(":usr", $username);
$stmt->bindValue(":typ", "Charge");
$stmt->bindParam(":amt", $amount);
$stmt->bindValue(":desc", "Charged by " . $me->username);
$stmt->bindValue(":tim", time(), SQLITE3_INTEGER);

$res = $stmt->execute();
if ($res === false) {
    die(json_encode(["success" => false, "error" => "Unknown error. Try again."]));
}

$charge->secret = bin2hex(random_bytes(32));
update_user($charge);

die(json_encode(array("success" => true)));