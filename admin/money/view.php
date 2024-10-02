<?php require "config.php"; ?>
<?php
require_once API_PATH . "/accounts/functions.php";
require_minimum_permissions($_COOKIE["sulv-token"], USER_PERMISSION_ADMIN);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require (PREFAB_PATH . "/global/head.php"); ?>
    <title>Admin Panel - Delete Voucher</title>
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
        <div id="vouchers">
            <?php
            require_once DB_PATH . "/money.php";
            if (logged_in()) {
                $vouchers = get_vouchers();?>
            <h2>Total Vouchers: <?php echo count($vouchers); ?></h2>
            <div id="vouchers2">
                <?php
                foreach ($vouchers as $voucher) {?>
                <div class="voucher">
                    <h3 class="id">Voucher ID: <?php echo $voucher->voucherid; ?></h3>
                    <h3 class="user">User: <?php echo $voucher->username; ?></h3>
                    <h3 class="value">Value: £<?php echo number_format((float)$voucher->amount, 2); ?></h3>
                    <h3 class="time">Time Given: <?php echo date("d/m/Y H:i:s", $voucher->time_given); ?></h3>
                    <h3 class="used">Used: <?php echo $voucher->used == 1 ? "Yes" : "No"; ?></h3>
                </div>
                <hr>
                <?php
                }?>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php require (PREFAB_PATH . "/global/footer.php"); ?>
    <?php require (PREFAB_PATH . "/global/cookie.php"); ?>
</body>
<script>
function delete_voucher(e) {
    e.preventDefault();

    const vid = document.querySelector("select#voucher").value;

    fetch("/voucher/api/admin/vouchers/delete.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8"
        },
        body: `vid=${vid}`
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