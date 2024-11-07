<?php
require_once __DIR__ . "/../../config.php";
require_once DB_PATH . "/money.php";
require_once DB_PATH . "/users.php";

if (empty($_POST["username"]) || empty($_POST["amount"])) {
    log_error("Missing fields.", ["POST" => $_POST]);
    die(json_encode(["status" => "error", "message" => "Username and amount are required."]));
}

$username = $_POST["username"];
$usernames = explode("|", $username);
$amount = $_POST["amount"];
$reason = "Added by admin.";
if (!empty($_POST["reason"])) {
    $reason = $_POST["reason"];
}

foreach ($usernames as $username) {
    $username = trim($username);
    if (empty($username)) {
        log_error("Invalid username.", ["POST" => $_POST, "username" => $username]);
        die(json_encode(["status" => "error", "message" => "Invalid username: " . $username]));
    }
    $usr = get_user_from_username($username);
    $usr->balance += $amount;
    if (!update_user($usr)) {
        log_error("Failed to update user.", ["POST" => $_POST, "username" => $username]);
        die(json_encode(["status" => "error", "message" => "Failed to update user: " . $username]));
    }
    $result = create_transaction($username, "Credit", $amount, $reason);
    if ($result === false) {
        log_error("Failed to create transaction.", ["POST" => $_POST, "username" => $username, "amt" => $amount, "reason" => $reason]);
        die(json_encode(["status" => "error", "message" => "Failed to execute database query."]));
    }
}

log_action("Added money to user.", ["POST" => $_POST, "usernames" => $usernames, "amount" => $amount, "reason" => $reason]);
die(json_encode(array("status" => "success")));