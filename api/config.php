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

// Defines user permissions
define("USER_PERMISSION_SCAN", -1);
define("USER_PERMISSION_USER", 0);
define("USER_PERMISSION_ADMIN", 1);

$self_deduct_users = ["JamesG1", "JR1"];