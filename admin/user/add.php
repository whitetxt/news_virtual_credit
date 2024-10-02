<?php require "config.php"; ?>
<?php
require_once API_PATH . "/accounts/functions.php";
require_minimum_permissions($_COOKIE["sulv-token"], USER_PERMISSION_ADMIN);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require (PREFAB_PATH . "/global/head.php"); ?>
    <title>Admin Panel - Create User</title>
    <link rel="stylesheet" href="/voucher/style/main.css">
    <link rel="stylesheet" href="/voucher/admin/admin.css">
    <link rel="stylesheet" href="/voucher/style/form.css">
</head>

<body>
    <div id="head">
        <?php require (PREFAB_PATH . "/nav/nav.php"); ?>
    </div>
    <div id="site">
        <a href="../index.php">&lt; Back</a>
        <h2>Create a User</h2>
        <form id="modify" name="modify" onsubmit="return create_user(event);">
            <label for="uname"> Username: </label>
            <input type="text" id="uname" name="uname" autocomplete="off" />
            <label for="pass"> Password: </label>
            <input type="password" id="pass" name="pass" autocomplete="off" />
            <label for="acclvl"> Access Level: </label>
            <select id="acclvl" name="acclvl">
                <option disabled selected value="" hidden>Select an access level</option>
                <option value="-1">Scanner</option>
                <option value="0">User</option>
                <option value="1">Admin</option>
            </select>
            <label for="enb"> Account Enabled: </label>
            <input type="checkbox" id="enb" name="enb">
            <button type="submit">Create User</button>
        </form>
    </div>
    <?php require (PREFAB_PATH . "/global/footer.php"); ?>
    <?php require (PREFAB_PATH . "/global/cookie.php"); ?>
</body>
<script>
async function digestMessage(message) {
    const msgUint8 = new TextEncoder().encode(message); // encode as (utf-8) Uint8Array
    const hashBuffer = await crypto.subtle.digest('SHA-256', msgUint8); // hash the message
    const hashArray = Array.from(new Uint8Array(hashBuffer)); // convert buffer to byte array
    const hashHex = hashArray.map((b) => b.toString(16).padStart(2, '0')).join(''); // convert bytes to hex string
    return hashHex;
}

function create_user(e) {
    e.preventDefault();

    const username = document.querySelector("input#uname").value;
    const password = document.querySelector("input#pass").value;
    const access_level = document.querySelector("select#acclvl").value;
    const enabled = document.querySelector("input#enb").checked ? "enabled" : "disabled";

    digestMessage(password).then((digest) => {
        fetch("/voucher/api/accounts/register.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
            },
            body: `username=${username}&access_level=${access_level}&enabled=${enabled}&password=${digest}`
        }).then(resp => resp.json()).then(data => {
            if (data.status === "error") {
                create_alert(data.message);
            } else {
                create_alert("Success!", 3, "SUCCESS");
            }
        });
    });
    return false;
}
</script>
<script src="/voucher/script/alert.js"></script>

</html>