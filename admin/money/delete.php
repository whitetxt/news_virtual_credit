<?php require "config.php"; ?>
<?php
require_once API_PATH . "/accounts/functions.php";
require_minimum_permissions($_COOKIE["sulv-token"], USER_PERMISSION_ADMIN);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require (PREFAB_PATH . "/global/head.php"); ?>
    <title>Admin Panel - Remove Money</title>
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
        <h2>Remove Money</h2>
        <?php
			require_once DB_PATH . "/money.php";
            $users = get_users();
			if ($users === false) {
				echo '<h3> No users in the database. </h3>';
				return;
			}
			echo '<select id="user" name="user">';
			echo '<option disabled selected value="" hidden> Select a user </option>';
			foreach ($users as $u) {
				echo '<option value="' . $u->username . '">' . $u->username .'</option>';
			}
			echo '</select>';
		?>
        <label for="value"> Value: </label>
        <input type="number" min="0" step="0.01" value="4" id="value" name="value" autocomplete="off" />
        <button onclick="delete_money()">Remove Money</button>
    </div>
    <?php require (PREFAB_PATH . "/global/footer.php"); ?>
    <?php require (PREFAB_PATH . "/global/cookie.php"); ?>
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
            create_alert("Success!", 3, "SUCCESS");
            location.reload();
        }
    });
    return false;
}
</script>
<script src="/voucher/script/alert.js"></script>

</html>