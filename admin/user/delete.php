<?php
require_once __DIR__ . "/../../config.php";
require_once API_PATH . "/accounts/functions.php";
require_minimum_permissions($_COOKIE["sulv-token"], USER_PERMISSION_ADMIN);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require(PREFAB_PATH . "/global/head.php"); ?>
    <title>Admin Panel - Delete User</title>
</head>

<body>
    <?php require(PREFAB_PATH . "/nav/nav.php"); ?>
    <div id="site" class="flex flex-col items-center gap-4">
        <a href="../index.php" class="btn">&lt; Back</a>
        <h2 class="text-primary text-2xl">Delete a User</h2>
        <?php
        require_once DB_PATH . "/users.php";
        $users = get_users();
        if ($users === false || (count($users) == 1 && $users[0]->token == $_COOKIE["sulv-token"])) {
            echo '<h3> No other users in the database. </h3>';
            return;
        }
        $us = get_user_from_token($_COOKIE["sulv-token"]); ?>
        <select id="user" name="user" class="select select-secondary">
            <?php foreach ($users as $user) {
                if ($user == $us)
                    continue; ?>
                <option value="<?= $user->username ?>"><?= $user->username ?>
                    (<?= date("d-m-Y H:i:s", $user->created_at) ?>)
                </option>
            <?php } ?>
        </select>
        <button class="btn" onclick="delete_user()">Delete User</button>
    </div>
    <?php require(PREFAB_PATH . "/global/footer.php"); ?>
    <?php require(PREFAB_PATH . "/global/cookie.php"); ?>
</body>
<script>
    function delete_user() {
        const name = document.querySelector("select#user").value;

        fetch("/voucher/api/admin/users/delete.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
            },
            body: `name=${name}`
        })
            .then(response => response.json())
            .then(resp => {
                console.log(resp);
                if (resp.status === "error") {
                    create_alert(resp.message);
                } else {
                    location.reload();
                }
            })
            .catch(error => {
                create_alert(`An error has occurred!\nPlease report the copied string to the devs.`);
                navigator.clipboard.writeText(btoa(JSON.stringify({
                    "error": error,
                    "time": Date.now()
                })));
            });

        return false;
    }
</script>
<script src="/voucher/script/alert.js"></script>

</html>