<?php require "config.php"; ?>
<?php
require_once API_PATH . "/accounts/functions.php";
require_minimum_permissions($_COOKIE["sulv-token"], USER_PERMISSION_ADMIN);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require (PREFAB_PATH . "/global/head.php"); ?>
    <title>Admin Panel - Give Money</title>
    <link rel="stylesheet" href="/voucher/style/main.css">
    <link rel="stylesheet" href="/voucher/admin/admin.css">
    <link rel="stylesheet" href="/voucher/style/form.css">
</head>

<style>
#users {
    width: 50%;
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
}

.user {
    display: flex;
    flex-direction: column;
}

.user>input[type="checkbox"] {
    margin-bottom: 8px;
    height: 24px;
}

input[type="number"] {
    height: 32px;
    width: 50%;

    padding: 8px;
    margin: 8px 0px;

    border: 2px solid var(--border-color);
    border-radius: 16px;

    font-family: inherit;
    font-size: inherit;
}

button {
    appearance: none;

    width: 50%;

    padding: 8px;
    margin: 8px 0px;

    border: unset;
    border-radius: 16px;

    background-color: var(--button-color);
    color: var(--text-color);

    font-family: inherit;
    font-size: inherit;
    font-weight: 700;
    transition-duration: 250ms;
    cursor: pointer;
}

button:hover {
    box-shadow: 0px 0px 10px var(--button-color);
}
</style>

<body>
    <div id="head">
        <?php require (PREFAB_PATH . "/nav/nav.php"); ?>
    </div>
    <div id="site">
        <a href="../index.php">&lt; Back</a>
        <h2>Give Money</h2>
        <div id="users">
            <?php
			require_once DB_PATH . "/users.php";
			$users = get_users();
			if ($users === false) {
                echo '<h3> No other users in the database. </h3>';
				return;
			}
			foreach ($users as $user) {
                if ($user->token == $_COOKIE["sulv-token"]) continue;
                if ($user->access_level == USER_PERMISSION_SCAN) continue;?>
            <div class="user">
                <input type="checkbox" name="<?=$user->username?>" class="user-select">
                <label for="<?=$user->username?>"><?=$user->username?> (<?=$user->role?>)</label>
            </div>
            <?php } ?>
        </div>
        <label for="value"> Value: </label>
        <input type="number" min="0" step="0.01" value="4" id="value" name="value" autocomplete="off" />
        <button onclick="give_money()">Give Money</button>
    </div>
    <?php require (PREFAB_PATH . "/global/footer.php"); ?>
    <?php require (PREFAB_PATH . "/global/cookie.php"); ?>
</body>
<script>
function give_money() {
    const username = document.querySelectorAll(".user>input[type='checkbox']");

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
        body: `username=${username_string}&amount=${value}`
    }).then(resp => resp.json()).then(data => {
        if (data.status === "error") {
            create_alert(data.message);
        } else {
            create_alert("Success!", 3, "SUCCESS");
        }
    });
    return false;
}
</script>
<script src="/voucher/script/alert.js"></script>

</html>