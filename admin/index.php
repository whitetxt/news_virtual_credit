<?php
require_once __DIR__ . "/../config.php";
require_once API_PATH . "/accounts/functions.php";
require_minimum_permissions($_COOKIE["sulv-token"], USER_PERMISSION_ADMIN);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require PREFAB_PATH . "/global/head.php"; ?>
    <title>Vouchers - Admin Panel</title>
</head>

<body>
    <?php require(PREFAB_PATH . "/nav/nav.php"); ?>
    <div id="site" class="flex flex-col items-center gap-4">
        <a href="../index.php" class="btn">&lt; Back</a>
        <h2 class="text-primary text-2xl">User Management</h2>
        <a href="user/add.php" class="btn">Add User</a>
        <a href="user/modify.php" class="btn">Modify User</a>
        <a href="user/delete.php" class="btn">Delete User</a>
        <h2 class="text-primary text-2xl">Money Management</h2>
        <a href="money/view.php" class="btn">View Transactions</a>
        <a href="money/add.php" class="btn">Give Money</a>
        <a href="money/delete.php" class="btn">Remove Money</a>
        <a href="verify_receipt.php" class="btn">Verify Receipt</a>
    </div>
    <?php require(PREFAB_PATH . "/global/footer.php"); ?>
    <?php require(PREFAB_PATH . "/global/cookie.php"); ?>
</body>
<script src="/voucher/script/alert.js"></script>

</html>