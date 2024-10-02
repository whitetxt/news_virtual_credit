<?php
require_once __DIR__ . "/../../config.php";
require_once API_PATH . "/accounts/functions.php";
require_once DB_PATH . "/users.php";
require_once DB_PATH . "/money.php";

if (empty($_POST["username"] || $_POST["amount"])) {
    header("Content-Type: application/json");
    die(json_encode(["status" => "error", "message" => "Voucher ID is required."]));
}

if (!logged_in()) {
    die(json_encode(["success" => false, "error" => "Not logged in."]));
}

$username = $_POST["username"];
$amt = $_POST["amount"];
$reason = "Removed by admin.";
if (!empty($_POST["reason"])) {
    $reason = $_POST["reason"];
}

$usr = get_user_from_username($username);
if ($usr === false) {
    header("Content-Type: application/json");
    die(json_encode(["status" => "error", "message" => "User not found."]));
}

if ($usr->balance < $amt) {
    header("Content-Type: application/json");
    die(json_encode(["status" => "error", "message" => "User does not have enough balance."]));
}

$usr->balance -= $amt;
if (!update_user($usr)) {
    header("Content-Type: application/json");
    die(json_encode(["status" => "error", "message" => "Failed to update user."]));
}

$result = create_transaction($username, "Removal", $amt, $reason);

if ($result === false) {
    header("Content-Type: application/json");
    die(json_encode(["status" => "error", "message" => "Failed to execute database query."]));
}

header("Content-Type: application/json");
die(json_encode(value: ["status" => "success"]));