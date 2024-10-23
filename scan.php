<?php
require_once __DIR__ . "/config.php";
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
        <canvas id="canvas" class="hidden"></canvas>
        <div class="card-body">
            <span class="card-title">Scan a QR Code</span>
            <input type="number" min="0.01" step="0.01" placeholder="Enter amount spent" name="amount" id="amount"
                class="input input-bordered input-primary">
            <div class="card-actions justify-end">
                <a onclick="startScan()" class="btn">
                    <span class="material-symbols-rounded">
                        photo_camera
                    </span>
                    <span>Scan QR Code</span>
                </a>
            </div>
        </div>
    </div>
    <dialog id="confirmation_modal" class="modal">
        <div class="modal-box">
            <h3 class="text-lg font-bold" id="title"></h3>
            <p class="py-4" id="info"></p>
            <div class="modal-action" id="modal_actions">
                <form method="dialog">
                    <!-- if there is a button in form, it will close the modal -->
                    <button class="btn" id="modal_yes" onclick="spend()">Yes</button>
                    <button class="btn" onclick="modal.classList.toggle('modal-open');video.play()">No</button>
                </form>
            </div>
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
const modal_title = document.getElementById("title");
const modal_info = document.getElementById("info");
const modal_yes = document.getElementById("modal_yes");
const modal_actions = document.getElementById("modal_actions");
var latest_data = null;

function startScan() {
    canvasElement.classList.remove("hidden");
    // Use facingMode: environment to attempt to get the front camera on phones
    navigator.mediaDevices.getUserMedia({
        video: {
            facingMode: "environment"
        }
    }).then(function(stream) {
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

var frames_since_alert = 0;

function tick() {
    if (video.readyState === video.HAVE_ENOUGH_DATA) {
        frames_since_alert++;
        canvasElement.height = video.videoHeight;
        canvasElement.width = video.videoWidth;
        canvas.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);
        var imageData = canvas.getImageData(0, 0, canvasElement.width, canvasElement.height);
        var code = jsQR(imageData.data, imageData.width, imageData.height, {
            inversionAttempts: "dontInvert",
        });
        if (code && video.paused === false) {
            if (isNaN(parseFloat(document.getElementById("amount").value))) {
                requestAnimationFrame(tick);
                return;
            }
            drawLine(code.location.topLeftCorner, code.location.topRightCorner, "#FF3B58");
            drawLine(code.location.topRightCorner, code.location.bottomRightCorner, "#FF3B58");
            drawLine(code.location.bottomRightCorner, code.location.bottomLeftCorner, "#FF3B58");
            drawLine(code.location.bottomLeftCorner, code.location.topLeftCorner, "#FF3B58");
            try {
                data = JSON.parse(code.data);
                latest_data = data;
                if (data.username === undefined || data.secret === undefined) {
                    throw "Invalid QR Code.";
                }
                var amount = parseFloat(document.getElementById("amount").value);
                modal.classList.toggle("modal-open");
                modal_title.innerText = "Confirm account charge";
                modal_info.innerText = `Do you want to charge £${amount.toFixed(2)} to ${data.username}?`;
                modal_actions.classList.remove("hidden");
                video.pause();
            } catch (error) {
                console.error(error);
                if (frames_since_alert > 30) {
                    create_alert("Invalid QR Code.");
                    frames_since_alert = 0;
                }
            }
        } else {
            // output.innerText = "";
        }
    }
    requestAnimationFrame(tick);
}

function spend() {
    const username = latest_data.username;
    const secret = latest_data.secret;
    const amount = parseFloat(document.getElementById("amount").value);
    modal_title.innerText = "Processing charge...";
    modal_info.innerHTML = '<span class="loading loading-spinner loading-lg"></span>';
    modal_actions.classList.add("hidden");
    var urlencoded = new URLSearchParams();
    urlencoded.append("username", username);
    urlencoded.append("secret", secret);
    urlencoded.append("amount", amount);
    fetch("/voucher/api/charge_account.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
            body: urlencoded
        })
        .then(response => response.json())
        .then(data => {
            modal.classList.toggle("modal-open");
            if (data.success) {
                create_alert("Successfully charged account!", 3, "success");
                setTimeout(() => {
                    window.location.reload();
                }, 5000);
            } else {
                create_alert(data.error);
            }
        }).catch(error => {
            modal.classList.toggle("modal-open");
            console.error("Error:", error);
            create_alert("An error occurred. Please try again.");
        });
}
</script>

</html>