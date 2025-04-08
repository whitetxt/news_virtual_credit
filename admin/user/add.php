<?php
require_once __DIR__ . "/../../config.php";
require_once API_PATH . "/accounts/functions.php";
require_flags($_COOKIE["sulv-token"], ["ADMIN"]);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require(PREFAB_PATH . "/global/head.php"); ?>
    <title>Admin Panel - Create User</title>
</head>

<body>
    <?php require(PREFAB_PATH . "/nav/nav.php"); ?>
    <div id="site" class="flex flex-col items-center gap-4">
        <a href="../index.php" class="btn">&lt; Back</a>
        <h2 class="text-primary text-2xl">Create a User</h2>
        <div class="flex flex-col lg:flex-row items-center gap-4">
            <div class="card bg-base-200 w-96 shadow-xl">
                <div class="card-body">
                    <h2 class="card-title">User Details</h2>
                    <label>
                        <div class="label">
                            <span class="label-text">Username</span>
                        </div>
                        <input type="text" id="uname" name="uname" class="input input-secondary">
                    </label>
                    <label>
                        <div class="label">
                            <span class="label-text">Password</span>
                        </div>
                        <input type="password" id="pass" name="pass" class="input input-secondary">
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
                        <input type="checkbox" class="checkbox checkbox-secondary perm-cb"
                            data-num="<?=constant("FLAG_" . $flag)?>" />
                    </label>
                    <?php } ?>
                </div>
            </div>
        </div>
        <button class="btn" onclick="create_user()">Create User</button>
    </div>
    <?php require(PREFAB_PATH . "/global/footer.php"); ?>
    <?php require(PREFAB_PATH . "/global/cookie.php"); ?>
</body>
<script>
async function digestMessage(message) {
    const msgUint8 = new TextEncoder().encode(message); // encode as (utf-8) Uint8Array
    const hashBuffer = await crypto.subtle.digest('SHA-256', msgUint8); // hash the message
    const hashArray = Array.from(new Uint8Array(hashBuffer)); // convert buffer to byte array
    const hashHex = hashArray.map((b) => b.toString(16).padStart(2, '0')).join(''); // convert bytes to hex string
    return hashHex;
}

function create_user() {
    const username = document.getElementById("uname").value;
    const password = document.getElementById("pass").value;
    const enabled = document.querySelector("input#enb").checked ? "enabled" : "disabled";

    const flag_checkboxes = document.querySelectorAll("input.perm-cb");
    const flag_number = Array.from(flag_checkboxes).map((cb) => cb.checked ? parseInt(cb.getAttribute("data-num")) : 0)
        .reduce((a, b) => a + b, 0);

    digestMessage(password).then((digest) => {
        fetch("/voucher/api/accounts/register.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
            },
            body: `username=${username}&flags=${flag_number}&enabled=${enabled}&password=${digest}`
        }).then(resp => resp.json()).then(data => {
            if (data.status === "error") {
                create_alert(data.message);
            } else {
                create_alert("Success!", 3, "success");
            }
        });
    });
    return false;
}
</script>
<script src="/voucher/script/alert.js"></script>

</html>