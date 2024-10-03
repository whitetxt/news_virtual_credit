<?php
require_once __DIR__ . "/../../config.php";
require_once API_PATH . "/accounts/functions.php";
require_minimum_permissions($_COOKIE["sulv-token"], USER_PERMISSION_ADMIN);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require(PREFAB_PATH . "/global/head.php"); ?>
    <title>Admin Panel - Update User</title>
</head>

<body>
    <?php require(PREFAB_PATH . "/nav/nav.php"); ?>
    <div id="site" class="flex flex-col items-center gap-4">
        <a href="../index.php" class="btn">&lt; Back</a>
        <h2 class="text-primary text-2xl">Modify a User</h2>
        <!-- <form id="modify" name="modify" onsubmit="return update_user(event);"> -->
        <?php
        require_once DB_PATH . "/users.php";
        $users = get_users();
        if ($users === false || (count($users) == 1 && $users[0]->token == $_COOKIE["sulv-token"])) {
            echo '<h3> No other users in the database. </h3>';
            return;
        }
        $us = get_user_from_token($_COOKIE["sulv-token"]); ?>
        <select id="user" name="user" onchange="changed_user()" class="select select-secondary">
            <option disabled selected value="" hidden> Select a user </option>
            <?php foreach ($users as $user) {
                if ($user->token == $_COOKIE["sulv-token"])
                    continue; ?>
                <option value="<?= $user->username ?>"><?= $user->username ?> (<?= $user->role ?>)</option>;
            <?php } ?>
        </select>
        <label>
            <div class="label">
                <span class="label-text">Password</span>
            </div>
            <input type="password" id="pass" name="pass" class="input input-secondary">
        </label>
        <label>
            <div class="label">
                <span class="label-text">Access Level</span>
            </div>
            <select id="acclvl" name="acclvl" class="select select-secondary">
                <option disabled selected value="" hidden>Select an access level</option>
                <option value="-1">Scanner</option>
                <option value="0">User</option>
                <option value="1">Admin</option>
            </select>
        </label>
        <label class="label cursor-pointer gap-8">
            <span class="label-text">Account Enabled</span>
            <input type="checkbox" id="enb" name="enb" class="checkbox checkbox-secondary">
        </label>
        <label>
            <div class="label">
                <span class="label-text">Role</span>
            </div>
            <select name="role" id="role" class="select select-secondary">
                <option value="" hidden disabled selected>Select a role</option>
                <?php
                $roles = get_roles();
                foreach ($roles as $role) { ?>
                    <option value="<?= $role->name ?>"><?= $role->name ?></option>
                <?php } ?>
            </select>
        </label>
        <button class="btn" onclick="update_user()">Update User</button>
        <!-- </form> -->
    </div>
    <?php require(PREFAB_PATH . "/global/footer.php"); ?>
    <?php require(PREFAB_PATH . "/global/cookie.php"); ?>
</body>
<script>
    function changed_user() {
        const name = document.querySelector("select#user").value;

        fetch("/voucher/api/admin/users/get.php", {
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
                    var user = resp["user"];
                    document.querySelector("select#acclvl").value = user["access_level"];
                    document.querySelector("input#enb").checked = user["enabled"] != false;
                    document.querySelector("select#role").value = user["role"];
                }
            })
            .catch(error => {
                create_alert(`An error has occurred!\nPlease report the copied string to the devs.`);
                navigator.clipboard.writeText(btoa(JSON.stringify({
                    "error": error,
                    "time": Date.now()
                })));
            });
    }

    async function digestMessage(message) {
        const msgUint8 = new TextEncoder().encode(message); // encode as (utf-8) Uint8Array
        const hashBuffer = await crypto.subtle.digest("SHA-256", msgUint8); // hash the message
        const hashArray = Array.from(new Uint8Array(hashBuffer)); // convert buffer to byte array
        const hashHex = hashArray
            .map((b) => b.toString(16).padStart(2, "0"))
            .join(""); // convert bytes to hex string
        return hashHex;
    }

    function update_user() {
        const name = document.querySelector("select#user").value;
        const access_level = document.querySelector("select#acclvl").value;
        const enabled = document.querySelector("input#enb").checked ? "enabled" : "disabled";
        const role = document.querySelector("select#role").value;
        const pass = document.querySelector("input#pass").value;

        if (pass.length >= 8) {
            digestMessage(pass).then((hash) => {
                fetch("/voucher/api/admin/users/update.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
                    },
                    body: `name=${name}&access_level=${access_level}&enabled=${enabled}&role=${role}&pass=${hash}`
                })
                    .then(response => response.json())
                    .then(resp => {
                        console.log(resp);
                        if (resp.status === "error") {
                            create_alert(resp.message);
                        } else {
                            create_alert("Success!", 3, "success");
                        }
                    })
                    .catch(error => {
                        create_alert(`An error has occurred!\nPlease report the copied string to the devs.`);
                        navigator.clipboard.writeText(btoa(JSON.stringify({
                            "error": error,
                            "time": Date.now()
                        })));
                    });
            });
        } else {
            fetch("/voucher/api/admin/users/update.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
                },
                body: `name=${name}&access_level=${access_level}&enabled=${enabled}&role=${role}`
            })
                .then(response => response.json())
                .then(resp => {
                    console.log(resp);
                    if (resp.status === "error") {
                        create_alert(resp.message);
                    } else {
                        create_alert("Success!", 3, "success");
                    }
                })
                .catch(error => {
                    create_alert(`An error has occurred!\nPlease report the copied string to the devs.`);
                    navigator.clipboard.writeText(btoa(JSON.stringify({
                        "error": error,
                        "time": Date.now()
                    })));
                });
        }

        return false;
    }
</script>
<script src="/voucher/script/alert.js"></script>

</html>