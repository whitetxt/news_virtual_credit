<?php
require_once __DIR__ . "/../config.php";
require_once DB_PATH . "/users.php";
if (isset($_COOKIE["sulv-token"]) == false) {
    header("Location: accounts/login.php");
    exit();
}
$user = get_user_from_token($_COOKIE["sulv-token"]);
if (($user->access_level != USER_PERMISSION_SCAN) && ($user->access_level != USER_PERMISSION_ADMIN)) {
    header("Location: /voucher/index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require(PREFAB_PATH . "/global/head.php"); ?>
    <title>Scan</title>
</head>

<body>
    <?php require(PREFAB_PATH . "/nav/nav.php"); ?>
    <div class="card bg-base-100 w-96 shadow-xl mx-auto">
        <canvas id="canvas"></canvas>
        <div class="card-body">
            <span class="card-title">Scan a QR Code</span>
        </div>
    </div>
    <dialog id="confirmation_modal" class="modal">
        <div class="modal-box">
            <form method="dialog">
                <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2"
                    onclick="modal.classList.toggle('modal-open');video.play()">✕</button>
            </form>
            <h3 class="text-lg font-bold" id="title">Receipt Data</h3>
            <p class="py-4" id="verify">
                <span>Verifying with server...</span>
                <span class="loading loading-spinner loading-sm" id="verify-spin"></span>
            </p>
            <p class="py-4" id="value">Value: </p>
            <p class="py-4" id="time">Time: </p>
        </div>
    </dialog>
    <?php require(PREFAB_PATH . "/global/footer.php"); ?>
    <?php require(PREFAB_PATH . "/global/cookie.php"); ?>
</body>
<script src="/voucher/script/alert.js"></script>
<script src="/voucher/script/jsQR.js"></script>
<script>
    const video = document.createElement("video");
    const canvasElement = document.getElementById("canvas");
    const canvas = canvasElement.getContext("2d");
    // var output = document.getElementById("output");
    const modal = document.getElementById("confirmation_modal");
    const modal_value = document.getElementById("value");
    const modal_time = document.getElementById("time");
    const verify_spin = document.getElementById("verify-spin");

    function startScan() {
        canvasElement.classList.remove("hidden");
        // Use facingMode: environment to attempt to get the front camera on phones
        navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: "environment"
            }
        }).then(function (stream) {
            video.srcObject = stream;
            video.setAttribute("playsinline", true); // required to tell iOS safari we don't want fullscreen
            video.play();
            requestAnimationFrame(tick);
        });
    }

    function drawLine(begin, end, color) {
        canvas.beginPath();
        canvas.moveTo(begin.x, begin.y);
        canvas.lineTo(end.x, end.y);
        canvas.lineWidth = 2;
        canvas.strokeStyle = color;
        canvas.stroke();
    }

    function tick() {
        if (video.readyState === video.HAVE_ENOUGH_DATA) {
            canvasElement.height = video.videoHeight;
            canvasElement.width = video.videoWidth;
            canvas.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);
            var imageData = canvas.getImageData(0, 0, canvasElement.width, canvasElement.height);
            var code = jsQR(imageData.data, imageData.width, imageData.height, {
                inversionAttempts: "dontInvert",
            });
            if (code && video.paused === false) {
                drawLine(code.location.topLeftCorner, code.location.topRightCorner, "#FF3B58");
                drawLine(code.location.topRightCorner, code.location.bottomRightCorner, "#FF3B58");
                drawLine(code.location.bottomRightCorner, code.location.bottomLeftCorner, "#FF3B58");
                drawLine(code.location.bottomLeftCorner, code.location.topLeftCorner, "#FF3B58");
                try {
                    data = JSON.parse(code.data);
                    modal.classList.toggle("modal-open");
                    verify_spin.classList.remove("hidden");
                    modal_value.classList.add("hidden");
                    modal_time.classList.add("hidden");
                    modal_value.innerText = `Value: ${data.amount.toFixed(2)}`;
                    modal_time.innerText = `Time: ${new Date(data.time * 1000).toLocaleString()}`;
                    video.pause();
                    fetch("/voucher/api/admin/verify.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
                        },
                        body: `id=${data.transactionid}&amount=${data.amount}&time=${data.time}`,
                    }).then((response) => {
                        response.json().then((data) => {
                            if (data.status === "success") {
                                if (data.valid === true) {
                                    modal.querySelector("#verify").innerText = "Verified!";
                                } else {
                                    modal.querySelector("#verify").innerText =
                                        `QR data different to server: ${data.message}`;
                                }
                            } else {
                                modal.querySelector("#verify").innerText =
                                    "An server-side error occured. Manual check is required.";
                                modal_value.classList.remove("hidden");
                                modal_time.classList.remove("hidden");
                            }
                            verify_spin.classList.add("hidden");
                        });
                    });
                } catch (error) {
                    console.log("wuuh");
                    console.error(error);
                    modal.querySelector("#verify").innerText =
                        "An client-side error occured. Manual check is required.";
                    modal_value.classList.remove("hidden");
                    modal_time.classList.remove("hidden");
                }
            } else {
                // output.innerText = "";
            }
        }
        requestAnimationFrame(tick);
    }

    startScan();
</script>

</html>