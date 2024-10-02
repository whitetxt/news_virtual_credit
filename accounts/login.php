<?php require "../config.php"; ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <?php require(PREFAB_PATH . "/global/head.php"); ?>
    <title>Login</title>
</head>

<body>
    <?php require(PREFAB_PATH . "/nav/nav.php"); ?>
    <div id="site">
        <div class="card bg-base-100 w-96 shadow-xl m-auto">
            <div class="card-body">
                <h2 class="card-title">Login to your account</h2>
                <div>
                    <input type="text" placeholder="Username" id="username"
                        class="input input-bordered w-full max-w-xs mb-2" />
                    <input type="password" placeholder="Password" id="password"
                        class="input input-bordered w-full max-w-xs" />
                    <label class="label cursor-pointer">
                        <span class="label-text">Remember me</span>
                        <input type="checkbox" checked="checked" id="remember" class="checkbox checkbox-primary" />
                    </label>
                    </d>
                    <div class="card-actions justify-end">
                        <button class="btn btn-primary" onclick="login()">
                            <span>Login</span>
                            <span class="loading loading-spinner loading-md hidden"></span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php require(PREFAB_PATH . "/global/footer.php"); ?>
        <?php require(PREFAB_PATH . "/global/cookie.php"); ?>
</body>
<script type="module" src="/voucher/script/alert.js"></script>
<script>
function login() {
    const user_field = document.querySelector("input#username");
    const pass_field = document.querySelector("input#password");
    const remember = document.querySelector("input#remember");

    if (user_field.value.length < 3) {
        create_alert("Usernames must be at least 3 characters.");
        return false;
    }

    if (pass_field.value.length < 8) {
        create_alert("Password length must be at least 8 characters.");
        return false;
    }

    const rem = remember.checked ? 1 : 0;
    digestMessage(pass_field.value).then((digest) => {
        var formData = new FormData();
        formData.append("username", user_field.value);
        formData.append("password", digest);
        formData.append("remember", rem);
        fetch(`/voucher/api/accounts/login.php`, {
                method: "post",
                body: formData
            })
            .then((resp) => {
                return resp.json();
            })
            .then((json) => {
                if (json["status"] !== "success") {
                    create_alert(json["message"]);
                    return;
                }
                window.location.pathname = "/voucher";
            }).catch((err) => {
                create_alert("An error occurred while processing your request.");
            });
    });
    return false;
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
</script>

</html>