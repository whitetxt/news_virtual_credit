<?php
require_once __DIR__ . "/../../config.php";
require_once API_PATH . "/accounts/functions.php";
require_flags($_COOKIE["sulv-token"], ["ADMIN"]);
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
        <?php
        require_once DB_PATH . "/users.php";
        $users = get_users();
        if ($users === false || (count($users) == 1 && $users[0]->token == $_COOKIE["sulv-token"])) {
            echo '<h3> No other users in the database. </h3>';
            return;
        } ?>
        <select id="user" name="user" onchange="changed_user()" class="select select-secondary">
            <option disabled selected value="" hidden> Select a user </option>
            <?php foreach ($users as $user) {
                if ($user->token == $_COOKIE["sulv-token"])
                    continue; ?>
                <option value="<?= $user->username ?>"><?= $user->username ?> (<?= $user->role ?>)</option>;
            <?php } ?>
        </select>
        <div class="flex flex-row items-center gap-4">
            <div class="card bg-base-200 w-96 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">User Details</h2>
                    <label>
                        <div class="label">
                            <span class="label-text">Password</span>
                        </div>
                        <input type="password" id="pass" name="pass" class="input input-secondary">
                    </label>
                    <label class="form-control w-full max-w-xs">
                        <div class="label">
                            <span class="label-text">Role</span>
                        </div>
                        <select class="select select-secondary" id="role">
                            <?php
                            $roles = get_roles();
                            foreach ($roles as $role) { ?>
                                <option value="<?=$role->name?>"><?=$role->name?></option>
                            <?php } ?>
                        </select>
                    </label>
                    <label class="label cursor-pointer gap-8">
                        <span class="label-text">Account Enabled</span>
                        <input type="checkbox" id="enb" name="enb" class="checkbox checkbox-secondary">
                    </label>
                </div>
            </div>
            <div class="card bg-base-200 w-96 shadow-xl">
                <div class="card-body grid grid-cols-2">
                    <h2 class="card-title col-span-2">User Permissions</h2>
                    <?php
                    foreach (FLAGS as $flag) { ?>
                        <label class="label cursor-pointer">
                            <span class="label-text"><?=$flag?></span>
                            <input type="checkbox" class="checkbox checkbox-secondary perm-cb" data-num="<?=constant("FLAG_" . $flag)?>"/>
                        </label>
                    <?php } ?>
                </div>
            </div>
        </div>
        <button class="btn" onclick="update_user()">Update User</button>
    </div>
    <?php require(PREFAB_PATH . "/global/footer.php"); ?>
    <?php require(PREFAB_PATH . "/global/cookie.php"); ?>
</body>
<script>
    const flag_checkboxes = document.querySelectorAll("input.perm-cb");
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
                    document.querySelector("input#enb").checked = user["enabled"] != false;
                    document.querySelector("select#role").value = user["role"];
                    for (var i = 0; i < flag_checkboxes.length; i++) {
                        flag_checkboxes[i].checked = (user["flags"] & parseInt(flag_checkboxes[i].getAttribute("data-num"))) != 0;
                    }
                }
            })
            .catch(error => {
                create_alert(`An error has occurred!\nPlease report the copied string to the devs.`);
                navigator.clipboard.writeText(btoa(JSON.stringify({
                    "error": error,
                    "time": Date.now()
                })));
                console.error(error);
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
        const enabled = document.querySelector("input#enb").checked ? "enabled" : "disabled";
        const role = document.querySelector("select#role").value;
        const pass = document.querySelector("input#pass").value;

        const flag_number = Array.from(flag_checkboxes).map((cb) => cb.checked ? parseInt(cb.getAttribute("data-num")) : 0).reduce((a, b) => a + b, 0);

        if (pass.length >= 8) {
            digestMessage(pass).then((hash) => {
                fetch("/voucher/api/admin/users/update.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
                    },
                    body: `name=${name}&flags=${flag_number}&enabled=${enabled}&role=${role}&pass=${hash}`
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
                        console.log(error);
                        navigator.clipboard.writeText(btoa(JSON.stringify({
                            "error": error,
                            "time": Date.now()
                        })));
                    });
            });
        } else {
            create_alert("Password shorter than 8 characters. Password not updated.");
            fetch("/voucher/api/admin/users/update.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
                },
                body: `name=${name}&flags=${flag_number}&enabled=${enabled}&role=${role}`
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