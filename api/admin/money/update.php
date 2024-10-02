<?php
require_once "config.php";
require_once API_PATH . "/accounts/functions.php";
require_minimum_permissions($_COOKIE["sulv-token"], USER_PERMISSION_ADMIN);
require_once DB_PATH . "/money.php";

if (empty($_POST["vid"])) {
	header("Content-Type: application/json");
	die(json_encode(array("status" => "error", "message" => "Missing fields.")));
	return;
}

$v = get_voucher_from_id($_POST["vid"]);
if ($v === false) {
	header("Content-Type: application/json");
	die(json_encode(array("status" => "error", "message" => "User doesn't exist.")));
	return;
}

if (isset($_POST["value"])) {
	$v->amount = $_POST["value"];
}

$res = update_voucher($v);
if ($res === false) {
	header("Content-Type: application/json");
	die(json_encode(array("status" => "error", "message" => "Failed to update.")));
	return;
}

header("Content-Type: application/json");
die(json_encode(array("status" => "success")));
?>