<?php
// DISABLE THIS PAGE!!
header("location: /voucher/");
die("no");
require_once API_PATH . "/accounts/functions.php";
require_minimum_permissions($_COOKIE["sulv-token"], USER_PERMISSION_ADMIN);
phpinfo();
?>