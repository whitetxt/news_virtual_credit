<?php
require_once __DIR__ . "/../config.php";
require_once API_PATH . "/accounts/functions.php";
require_flags($_COOKIE["sulv-token"], ["ADMIN"]);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require(PREFAB_PATH . "/global/head.php"); ?>
    <title>Admin Panel - Generate Report</title>
</head>

<body>
    <?php require(PREFAB_PATH . "/nav/nav.php"); ?>
    <div id="site" class="flex flex-col items-center gap-4">
        <a href="../index.php" class="btn">&lt; Back</a>
        <h2 class="text-primary text-2xl">Generate a Report</h2>
        <div class="flex flex-row items-center gap-4">
            <div class="card bg-base-200 w-64 shadow-xl">
                <div class="card-body">
                    <label class="form-control w-full max-w-xs">
                        <div class="label">
                            <span class="label-text">Start Date</span>
                        </div>
                        <input type="date" placeholder="Type here" class="input input-bordered w-full max-w-xs" id="start"/>
                    </label>
                    <label class="form-control w-full max-w-xs">
                        <div class="label">
                            <span class="label-text">End Date</span>
                        </div>
                        <input type="date" placeholder="Type here" class="input input-bordered w-full max-w-xs" id="end"/>
                    </label>
                </div>
            </div>
        </div>
        <button class="btn" onclick="get_report()">Generate Report</button>
    </div>
    <?php require(PREFAB_PATH . "/global/footer.php"); ?>
    <?php require(PREFAB_PATH . "/global/cookie.php"); ?>
</body>
<script>
    function get_report() {
        var start_date = new Date(document.getElementById("start").value);
        start_date = start_date.getTime() / 1000;
        var end_date = new Date(document.getElementById("end").value);
        end_date = end_date.getTime() / 1000;

        console.log(start_date);
        console.log(end_date);

        window.location = `generate_report.php?start=${start_date}&end=${end_date}`;
    }
</script>
<script src="/voucher/script/alert.js"></script>

</html>