<?php
require_once __DIR__ . "/../config.php";
require_once DB_PATH . "/users.php";
delete_session($_COOKIE["sulv-token"]);

setcookie("sulv-token", "", time() - 3600, "/");

header("location: /voucher/");