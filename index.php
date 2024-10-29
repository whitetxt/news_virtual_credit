<?php
require_once __DIR__ . "/config.php";
require_once DB_PATH . "/users.php";
require_once DB_PATH . "/money.php";
if (isset($_COOKIE["sulv-token"]) == false) {
    header("Location: /voucher/accounts/login.php");
    exit();
}
$user = get_user_from_token($_COOKIE["sulv-token"]);
if ($user === false) {
    header("Location: /voucher/accounts/login.php");
    exit();
}
if ($user->has_permission("SCAN")) {
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
            <span>Spend Credit</span>
        </a>
        <?php
        if ($user->has_permission("SELF_CHARGE")) { ?>
        <a class="btn" onclick="modal.classList.toggle('modal-open')">
            <span class="material-symbols-rounded">
                attach_money
            </span>
            <span>Deduct Money</span>
        </a>
        <?php } ?>
        <dialog id="deduct_modal" class="modal">
            <div class="modal-box">
                <h3 class="text-lg font-bold" id="title">How much are you spending?</h3>
                <p class="py-4" id="info">
                    <input type="number" min="0.01" step="0.01" placeholder="Enter amount spent" name="amount"
                        id="amount" class="input input-bordered input-primary">
                </p>
                <div class="modal-action" id="modal_actions">
                    <form method="dialog">
                        <!-- if there is a button in form, it will close the modal -->
                        <button class="btn" id="modal_yes" onclick="deductSelf()">Spend</button>
                        <button class="btn" onclick="modal.classList.toggle('modal-open')">Cancel</button>
                    </form>
                </div>
            </div>
        </dialog>
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
<script>
const modal = document.getElementById("deduct_modal");

function deductSelf() {
    const amount = parseFloat(document.getElementById("amount").value)
    var urlencoded = new URLSearchParams();
    urlencoded.append("amount", amount);
    fetch("/voucher/api/charge_self.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: urlencoded
        })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            modal.classList.toggle("modal-open");
            if (data.success) {
                create_alert("Successfully charged account!", 3, "success");
                setTimeout(() => {
                    location.reload();
                }, 3000);
            } else {
                create_alert(data.error);
            }
        }).catch(error => {
            modal.classList.toggle("modal-open");
            console.error("Error:", error);
            create_alert("An error occurred. Please try again.");
        });
}
</script>

</html>