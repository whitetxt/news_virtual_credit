<?php
require_once "./config.php";
require_once API_PATH . "/accounts/functions.php";
if (!isset($_POST["amount"])) {
    log_error('Missing parameters', ['POST' => $_POST]);
    die(json_encode(["success" => false, "error" => "Missing parameters."]));
}

if (!logged_in()) {
    die(json_encode(["success" => false, "error" => "Not logged in."]));
}

$me = current_user();

$amount = floatval($_POST["amount"]);

require_once DB_PATH . "/money.php";

if ($me->balance < $amount) {
    log_error('Insufficient balance', ['balance' => $me->balance, 'amount' => $amount, 'me' => $me]);
    die(json_encode(["success" => false, "error" => "Insufficient funds."]));
}

$me->balance -= $amount;
if (!update_user($me)) {
    die(json_encode(["success" => false, "error" => "Unknown error. Try again."]));
}

$db = new SQLite3(USERS_DB, SQLITE3_OPEN_READWRITE);

$stmt = $db->prepare("INSERT INTO transactions(username, type, amount, description, time) VALUES(:usr, :typ, :amt, :desc, :tim)");
$stmt->bindParam(":usr", $me->username);
$stmt->bindValue(":typ", "Charge");
$stmt->bindParam(":amt", $amount);
$stmt->bindValue(":desc", "Self-Deduction");
$stmt->bindValue(":tim", time(), SQLITE3_INTEGER);

$res = $stmt->execute();
if ($res === false) {
    die(json_encode(["success" => false, "error" => "Unknown error. Try again."]));
}

die(json_encode(array("success" => true)));