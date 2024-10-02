<?php
require_once "config.php";
require_once DB_PATH . "/money.php";

if (empty($_POST["username"] || $_POST["amount"])) {
	header("Content-Type: application/json");
	die(json_encode(array("status" => "error", "message" => "Voucher ID is required.")));
	return;
}

if (!logged_in()) {
    die(json_encode(array("success" => false, "error" => "Not logged in.")));
}

$current_usr = get_user_from_token($_COOKIE["sulv-token"]);

$username = $_POST["username"];
$usr = get_user_from_username($username);
if ($usr === false) {
    header("Content-Type: application/json");
    die(json_encode(array("status" => "error", "message" => "User not found.")));
    return;
}


if ($result === false) {
	header("Content-Type: application/json");
	die(json_encode(array("status" => "error", "message" => "Failed to execute database query.")));
	return;
}

header("Content-Type: application/json");
die(json_encode(array("status" => "success")));
?>

<?php
require_once "./config.php";
require_once API_PATH . "/accounts/functions.php";
if (!isset($_POST["username"]) || !isset($_POST["secret"]) || !isset($_POST["amount"])) {
    die(json_encode(array("success" => false, "error" => "Missing parameters.")));
}

if (!logged_in()) {
    die(json_encode(array("success" => false, "error" => "Not logged in.")));
}

$username = $_POST["username"];
$secret = $_POST["secret"];
$amount = floatval($_POST["amount"]);

require_once DB_PATH . "/money.php";

$charge = get_user_from_username($username);
if ($secret !== $charge->secret) {
    die(json_encode(["success" => false, "error" => "Incorrect secret."]));
}

$charge->balance -= $amount;
if (!update_user($charge)) {
    die(json_encode(["success" => false, "error" => "Unknown error. Try again."]));
}

$db = new SQLite3(USERS_DB, SQLITE3_OPEN_READWRITE);

$stmt = $db->prepare("INSERT INTO transactions(username, type, amount, time) VALUES(:usr, :typ, :amt, :tim)");
$stmt->bindParam(":usr", $username);
$stmt->bindValue(":typ", "Charge");
$stmt->bindParam(":amt", $amount);
$stmt->bindValue(":tim", time(), SQLITE3_INTEGER);

$res = $stmt->execute();
if ($res === false) {
    die(json_encode(["success" => false, "error" => "Unknown error. Try again."]));
}

die(json_encode(array("success" => true)));
?>