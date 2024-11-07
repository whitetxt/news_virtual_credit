<?php
require_once __DIR__ . "/../../config.php";
require_once API_PATH . "/accounts/functions.php";
require_once DB_PATH . "/users.php";
require_once DB_PATH . "/money.php";

if (empty($_POST["username"] || $_POST["amount"])) {
    log_error("Missing fields.", ["POST" => $_POST]);
    die(json_encode(["status" => "error", "message" => "Voucher ID is required."]));
}

if (!logged_in()) {
    log_error("Not logged in.", ["POST" => $_POST]);
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
    log_error("User not found.", ["POST" => $_POST, "username" => $username]);
    die(json_encode(["status" => "error", "message" => "User not found."]));
}

if ($usr->balance < $amt) {
    log_error("User does not have enough balance.", ["POST" => $_POST, "user" => $usr, "amount" => $amt, "balance" => $usr->balance]);
    die(json_encode(["status" => "error", "message" => "User does not have enough balance."]));
}

$usr->balance -= $amt;
if (!update_user($usr)) {
    log_error("Failed to update user.", ["POST" => $_POST, "user" => $usr]);
    die(json_encode(["status" => "error", "message" => "Failed to update user."]));
}

$result = create_transaction($username, "Removal", $amt, $reason);

if ($result === false) {
    log_error("Failed to create transaction", ["POST" => $_POST, "user" => $usr, "amount" => $amt, "reason" => $reason]);
    die(json_encode(["status" => "error", "message" => "Failed to execute database query."]));
}

die(json_encode(value: ["status" => "success"]));