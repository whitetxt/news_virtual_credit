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

define("USAGE_LOG", BASE_PATH . "/logs/usage.log");
define("ERROR_LOG", BASE_PATH . "/logs/error.log");

foreach ([USAGE_LOG, ERROR_LOG] as $file) {
    if (!file_exists($file)) {
        $log = fopen($file, "w");
        fclose($log);
    }
}

function log_error($error, $data = []) {
    $date = date("d-m-Y H:i:s");
    $uri = $_SERVER['REQUEST_URI'];
    $json_data = json_encode($data);
    $log = fopen(ERROR_LOG, "a");
    fwrite($log, "[${uri}@${date}] - ${error}\n");
    if (count($data) > 0) {
        fwrite($log, "Additional data: ${json_data}\n");
    }
    fclose($log);
}

function log_action($action_name, $data = []) {
    $date = date("d-m-Y H:i:s");
    $uri = $_SERVER['REQUEST_URI'];
    $json_data = json_encode($data);
    $log = fopen(USAGE_LOG, "a");
    fwrite($log, "[${uri}@${date}] - ${action_name}\n");
    if (count($data) > 0) {
        fwrite($log, "Additional data: ${json_data}\n");
    }
    fclose($log);
}

log_action("Page loaded");