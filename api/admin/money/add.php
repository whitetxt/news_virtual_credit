<?php
require_once __DIR__ . "/../../config.php";
require_once DB_PATH . "/money.php";
require_once DB_PATH . "/users.php";

if (empty($_POST["username"]) || empty($_POST["amount"])) {
    header("Content-Type: application/json");
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
        header("Content-Type: application/json");
        die(json_encode(["status" => "error", "message" => "Invalid username: " . $username]));
    }
    $usr = get_user_from_username($username);
    $usr->balance += $amount;
    if (!update_user($usr)) {
        header("Content-Type: application/json");
        die(json_encode(["status" => "error", "message" => "Failed to update user: " . $username]));
    }
    $result = create_transaction($username, "Credit", $amount, $reason);
    if ($result === false) {
        header("Content-Type: application/json");
        die(json_encode(["status" => "error", "message" => "Failed to execute database query."]));
    }
}

header("Content-Type: application/json");
die(json_encode(array("status" => "success")));