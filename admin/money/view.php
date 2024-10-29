<?php
require_once __DIR__ . "/../../config.php";
require_once API_PATH . "/accounts/functions.php";
require_flags($_COOKIE["sulv-token"], ["ADMIN"]);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require(PREFAB_PATH . "/global/head.php"); ?>
    <title>Admin Panel - Delete Voucher</title>
</head>

<body>
    <?php require(PREFAB_PATH . "/nav/nav.php"); ?>
    <div id="site" class="flex flex-col items-center gap-4">
        <a href="../index.php" class="btn">&lt; Back</a>
        <?php
        require_once DB_PATH . "/users.php";
        require_once DB_PATH . "/money.php";
        $users = get_users();
        if (logged_in()) {
            $transactions = array_reverse(get_transactions()); ?>
            <h2>Total Transactions: <?php echo count($transactions); ?></h2>
            <label>
                <div class="label">
                    <span class="label-text">User Filter</span>
                </div>
                <select id="usersel" name="user" onchange="changed_user()" class="select select-secondary">
                    <option disabled selected value="" hidden> Select a user </option>
                    <?php foreach ($users as $user) {
                        6 ?>
                        <option value="<?= $user->username ?>"><?= $user->username ?> (<?= $user->role ?>)</option>;
                    <?php } ?>
                </select>
            </label>
            <?php foreach ($transactions as $t) { ?>
                <div class="card bg-base-100 w-96 shadow-xl voucher" data-user="<?= $t->username ?>">
                    <div class="card-body">
                        <h2 class="card-title"><?= $t->username ?> - £<?= number_format($t->amount, 2) ?></h2>

                        <p><?= $t->type ?> - <?= $t->description ?></p>
                        <p><?= date("d-m-y H:i:s", $t->time) ?></p>
                    </div>
                </div>
            <?php } ?>
        <?php } ?>
    </div>
    <?php require(PREFAB_PATH . "/global/footer.php"); ?>
    <?php require(PREFAB_PATH . "/global/cookie.php"); ?>
</body>
<script>
    const cards = document.querySelectorAll(".voucher");

    function changed_user() {
        const user = document.getElementById("usersel").value;
        console.log(user);
        cards.forEach(card => {
            console.log(card.dataset.user);
            if (card.dataset.user === user) {
                card.classList.remove("hidden");
            } else {
                card.classList.add("hidden");
            }
        });
    }
</script>
<script src="/voucher/script/alert.js"></script>

</html>