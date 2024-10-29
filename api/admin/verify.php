<?php
require_once __DIR__ . "/../config.php";
require_once API_PATH . "/accounts/functions.php";
require_flags($_COOKIE["sulv-token"], ["ADMIN"]);
require_once DB_PATH . "/users.php";
require_once DB_PATH . "/money.php";

if (empty($_POST["id"]) || empty($_POST["amount"]) || empty($_POST["time"])) {
    header("Content-Type: application/json");
    die(json_encode(["status" => "error", "message" => "Missing fields."]));
}

$trans = get_transaction_from_id($_POST["id"]);
if ($trans === false) {
    header("Content-Type: application/json");
    die(json_encode(["status" => "success", "valid" => false, "message" => "Transaction doesn't exist."]));
}

if ($trans->amount != $_POST["amount"] || $trans->time != $_POST["time"]) {
    header("Content-Type: application/json");
    die(json_encode(["status" => "success", "valid" => false, "message" => "Transaction doesn't match."]));
}

header("Content-Type: application/json");
die(json_encode(["status" => "success", "valid" => true]));