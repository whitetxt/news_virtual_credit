<?php
require_once "./config.php";
require_once API_PATH . "/accounts/functions.php";
if (!logged_in()) {
    die(json_encode(array("success" => false, "error" => "Not logged in.")));
}

$user = current_user();

$db = new SQLite3(USERS_DB, SQLITE3_OPEN_READONLY);
$stmt = $db->prepare("SELECT * FROM transactions WHERE time > :time_low AND time < :time_high AND username = :usr");
$stmt->bindValue(":time_low", time() - 10);
$stmt->bindValue(":time_high", time() + 10);
$stmt->bindValue(":usr", $user->username);
$res = $stmt->execute();
if ($res === false) {
    die(json_encode(["success" => false]));
}
$arr = $res->fetchArray();
if ($arr === false) {
    die(json_encode(["success" => false]));
}

die(json_encode(array("success" => true, "amount" => $arr["amount"])));