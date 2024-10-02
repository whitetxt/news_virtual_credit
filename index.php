<?php
require_once __DIR__ . "/config.php";
require_once DB_PATH . "/users.php";
if (isset($_COOKIE["sulv-token"]) == false) {
    header("Location: accounts/login.php");
    exit();
}
$user = get_user_from_token($_COOKIE["sulv-token"]);
if ($user->access_level == USER_PERMISSION_SCAN) {
    header("Location: scan.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require(PREFAB_PATH . "/global/head.php"); ?>
    <title>Vouchers</title>
</head>

<body>
    <?php require(PREFAB_PATH . "/nav/nav.php"); ?>
    <div id="site" class="flex flex-col gap-8 w-full justify-center items-center">
        <span class="text-3xl">Balance</span>
        <span class="text-xl">£<?= number_format($user->balance, 2) ?></span>
        <a href="spend.php" class="btn">
            <span class="material-symbols-rounded">
                shopping_cart
            </span>
            <span>Spend Money</span>
        </a>
    </div>
    <?php require(PREFAB_PATH . "/global/footer.php"); ?>
    <?php require(PREFAB_PATH . "/global/cookie.php"); ?>
</body>
<script src="/voucher/script/alert.js"></script>

</html>