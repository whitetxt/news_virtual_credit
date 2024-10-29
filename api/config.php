<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

set_include_path($_SERVER["DOCUMENT_ROOT"] . "/voucher");

// Defines paths
define("BASE_PATH", $_SERVER["DOCUMENT_ROOT"] . "/voucher");
define("API_PATH", BASE_PATH . "/api");
define("DB_PATH", API_PATH . "/db");
define("PREFAB_PATH", BASE_PATH . "/prefabs");
define("VENDOR_PATH", BASE_PATH . "/vendor");
define("USERS_DB", DB_PATH . "/users_dev.db");

// Define user flags
$db = new SQLite3(USERS_DB, SQLITE3_OPEN_READONLY);
$stmt = $db->prepare("SELECT * FROM flags");
$res = $stmt->execute();
if (!$res) {
    die("<h1>A fatal error occured. Please contact an administrator.</h1><br><h2>Error code 0x01</h2>");
}
$flags = [];
while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
    define("FLAG_" . strtoupper($row["name"]), 2 ** ((int)$row["index"] - 1));
    $flags[] = strtoupper($row["name"]);
}
define("FLAGS", $flags);

//$self_deduct_users = ["JamesG1"];