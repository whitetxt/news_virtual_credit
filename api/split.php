<?php
require_once __DIR__ . "/config.php";
require_once API_PATH . "/accounts/functions.php";
if (!isset($_GET["id"])) {
    die(json_encode(array("success" => false, "error" => "No Voucher ID Provided")));
}
if (!isset($_GET["value"])) {
    die(json_encode(array("success" => false, "error" => "No value provided.")));
}
if (!logged_in()) {
    die(json_encode(array("success" => false, "error" => "Not logged in.")));
}
if (!is_numeric($_GET["value"])) {
    die(json_encode(array("success" => false, "error" => "Value is not a float.")));
}

require_once DB_PATH . "/money.php";

$_GET["value"] = floatval($_GET["value"]);

$v = get_voucher_from_id($_GET["id"]);
if ($v->used) {
    die(json_encode(array("success" => false, "error" => "Voucher already redeemed.")));
}
if ($v->username != current_user()->username) {
    die(json_encode(array("success" => false, "error" => "You do not own this voucher.")));
}
if ($v->amount <= $_GET["value"]) {
    die(json_encode(array("success" => false, "error" => "Value is invalid.")));
}

$remaining = $v->amount - $_GET["value"];

create_new_voucher_with_time(current_user()->username, $_GET["value"], $v->time_given);
create_new_voucher_with_time(current_user()->username, $remaining, $v->time_given);

$db = new SQLite3(USERS_DB, SQLITE3_OPEN_READONLY);
$stmt = $db->prepare("SELECT * FROM vouchers WHERE username = :usr AND amount = :val AND time_given = :tim");
$stmt->bindParam(":usr", current_user()->username);
$stmt->bindParam(":val", $_GET["value"]);
$stmt->bindParam(":tim", $v->time_given);
$res = $stmt->execute();
if ($res === false) {
    die(json_encode(array("success" => false, "error" => "Failed to split voucher.")));
}
$arr = $res->fetchArray();
if ($arr === false) {
    die(json_encode(array("success" => false, "error" => "Failed to split voucher.")));
}
$new_v = db_to_voucher($arr);
$stmt->close();
$db->close();
    
delete_voucher($_GET["id"]);

die(json_encode(array("success" => true, "new_id" => $new_v->voucherid)));
?>