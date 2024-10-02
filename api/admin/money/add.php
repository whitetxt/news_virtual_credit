<?php
require_once "config.php";
require_once DB_PATH . "/money.php";
require_once DB_PATH . "/users.php";

if (empty($_POST["username"]) || empty($_POST["amount"])) {
	header("Content-Type: application/json");
	die(json_encode(array("status" => "error", "message" => "Username and amount are required.")));
	return;
}

$username = $_POST["username"];
$usernames = explode("|", $username);
$amount = $_POST["amount"];

foreach ($usernames as $username) {
    $username = trim($username);
    if (empty($username)) {
        header("Content-Type: application/json");
        die(json_encode(array("status" => "error", "message" => "Invalid username: " . $username)));
        return;
    }
    $usr = get_user_from_username($username);
    $usr->balance += $amount;
    if (!update_user($usr)) {
        header("Content-Type: application/json");
        die(json_encode(array("status" => "error", "message" => "Failed to update user: " . $username)));
        return;
    }
    $result = create_transaction($username, "Credit", $amount, "Added by admin.");
    if ($result === false) {
        header("Content-Type: application/json");
        die(json_encode(array("status" => "error", "message" => "Failed to execute database query.")));
        return;
    }
}

header("Content-Type: application/json");
die(json_encode(array("status" => "success")));
?>