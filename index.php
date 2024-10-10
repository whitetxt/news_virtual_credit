<?php
require_once __DIR__ . "/config.php";
require_once DB_PATH . "/users.php";
require_once DB_PATH . "/money.php";
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
    <div id="site" class="flex flex-col gap-8 w-full justify-center items-center mb-4">
        <span class="text-3xl text-primary">Balance</span>
        <span class="text-xl">£<?= number_format($user->balance, 2) ?></span>
        <a href="spend.php" class="btn">
            <span class="material-symbols-rounded">
                shopping_cart
            </span>
            <span>Spend Money</span>
        </a>
        <span class="text-3xl text-primary">Recent Transactions:</span>
        <?php
        $transactions = get_users_transactions($user->username);
        if (count($transactions) > 8) {
            $transactions = array_slice($transactions, 0, 8);
        }
        foreach ($transactions as $trans) { ?>
        <div class="card bg-base-100 w-96 shadow-xl">
            <div class="card-body">
                <h2 class="card-title"><?= $trans->type ?> - £<?= number_format($trans->amount, 2) ?></h2>
                <p><?= $trans->description ?></p>
                <p><?= date("d-m-y H:i:s", $trans->time) ?></p>
                <div class="card-actions justify-end">
                    <a class="btn btn-primary" href="receipt.php?id=<?= $trans->id ?>">View Receipt</a>
                </div>
            </div>
        </div>
        <?php }
        ?>
    </div>
    <?php require(PREFAB_PATH . "/global/footer.php"); ?>
    <?php require(PREFAB_PATH . "/global/cookie.php"); ?>
</body>
<script src="/voucher/script/alert.js"></script>

</html>