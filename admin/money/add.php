<?php
require_once __DIR__ . "/../../config.php";
require_once API_PATH . "/accounts/functions.php";
require_minimum_permissions($_COOKIE["sulv-token"], USER_PERMISSION_ADMIN);
$me = current_user();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require(PREFAB_PATH . "/global/head.php"); ?>
    <title>Admin Panel - Give Money</title>
</head>

<body>
    <?php require(PREFAB_PATH . "/nav/nav.php"); ?>
    <div id="site" class="flex flex-col items-center gap-4">
        <a href="../index.php" class="btn">&lt; Back</a>
        <h2 class="text-primary text-2xl">Give Money</h2>
        <div id="users" class="grid grid-cols-2 lg:grid-cols-4 gap-8">
            <?php
            require_once DB_PATH . "/users.php";
            $users = get_users();
            if ($users === false) {
                echo '<h3> No other users in the database. </h3>';
                return;
            }
            foreach ($users as $user) {
                if ($user->access_level == USER_PERMISSION_SCAN)
                    continue; ?>
            <label class="label cursor-pointer">
                <span class="label-text mr-2"><?= $user->username ?> (<?= $user->role ?>)</span>
                <input type="checkbox" class="checkbox checkbox-secondary" name="<?= $user->username ?>" />
            </label>
            <?php } ?>
        </div>
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
        <button onclick="give_money()" class="btn">Give Money</button>
    </div>
    <?php require(PREFAB_PATH . "/global/footer.php"); ?>
    <?php require(PREFAB_PATH . "/global/cookie.php"); ?>
</body>
<script>
function give_money() {
    const username = document.querySelectorAll("label.label>input[type='checkbox']");

    var username_string = "";
    for (var i = 0; i < username.length; i++) {
        if (username[i].checked) {
            username_string += username[i].name + "|";
        }
    }
    if (username_string.endsWith("|")) {
        username_string = username_string.slice(0, -1);
    }

    const value = document.querySelector("input#value").value;

    fetch("/voucher/api/admin/money/add.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
        },
        body: `username=${username_string}&amount=${value}&reason=Added by <?= $me->username ?>`
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