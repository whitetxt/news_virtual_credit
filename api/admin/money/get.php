<?php
require_once "config.php";
require_once API_PATH . "/accounts/functions.php";
require_minimum_permissions($_COOKIE["sulv-token"], USER_PERMISSION_ADMIN);
require_once DB_PATH . "/money.php";

if (empty($_POST["id"])) {
	header("Content-Type: application/json");
	die(json_encode(array("status" => "error", "message" => "Missing fields.")));
	return;
}

$v = get_voucher_from_id($_POST["id"]);
if ($v === false) {
	header("Content-Type: application/json");
	die(json_encode(array("status" => "error", "message" => "Voucher doesn't exist.")));
	return;
}

header("Content-Type: application/json");
die(json_encode(array("status" => "success", "voucher" => $v)));
?>