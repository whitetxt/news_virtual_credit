<?php
require_once __DIR__ . "/../../config.php";
require_once API_PATH . "/accounts/functions.php";
require_flags($_COOKIE["sulv-token"], ["ADMIN"]);
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