<?php
require_once __DIR__ . "/../../config.php";
require_once API_PATH . "/accounts/functions.php";
require_flags($_COOKIE["sulv-token"], ["ADMIN"]);
$me = current_user();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require(PREFAB_PATH . "/global/head.php"); ?>
    <title>Admin Panel - Remove Money</title>
</head>

<body>
    <?php require(PREFAB_PATH . "/nav/nav.php"); ?>
    <div id="site" class="flex flex-col items-center gap-4">
        <a href="../index.php" class="btn">&lt; Back</a>
        <h2 class="text-primary text-2xl">Remove Money</h2>
        <?php
        require_once DB_PATH . "/money.php";
        $users = get_users();
        if ($users === false) {
            echo '<h3> No users in the database. </h3>';
            return;
        } ?>
        <select id="user" name="user" class="select select-secondary">
            <option disabled selected value="" hidden> Select a user </option>
            <?php foreach ($users as $u) { ?>
                <option value="<?= $u->username ?>"><?= $u->username ?> (£<?= number_format($u->balance, 2) ?>)</option>
            <?php } ?>
        </select>
        <label>
            <div class="label">
                <span class="label-text">Value</span>
            </div>
            <div>
                <label class="input input-secondary flex items-center">
                    <span class="material-symbols-rounded">
                        currency_pound
                    </span>
                    <input type="number" min="0.01" step="0.01" value="4" id="value" name="value" autocomplete="off"
                        class="text-xl" />
                </label>
            </div>
        </label>
        <button onclick="delete_money()" class="btn">Remove Money</button>
    </div>
    <?php require(PREFAB_PATH . "/global/footer.php"); ?>
    <?php require(PREFAB_PATH . "/global/cookie.php"); ?>
</body>
<script>
    function delete_money() {
        const username = document.querySelector("select#user").value;
        const value = document.querySelector("input#value").value;

        fetch("/voucher/api/admin/money/delete.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
            },
            body: `username=${username}&amount=${value}`
        }).then(resp => resp.json()).then(data => {
            if (data.status === "error") {
                create_alert(data.message);
            } else {
                create_alert("Success!", 3, "success");
            }
        });
        return false;
    }
</script>
<script src="/voucher/script/alert.js"></script>

</html>