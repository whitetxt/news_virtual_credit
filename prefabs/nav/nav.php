<div id="links" class="navbar shadow-md mb-4">
    <div class="navbar-start">
        <a href="/voucher" class="btn">
            <span class="material-symbols-rounded">
                home
            </span>
            Home
        </a>
    </div>
    <?php
    require_once(API_PATH . "/accounts/functions.php");
    $loggedin = "prefabs/nav/logged_in.php";
    $guest = "prefabs/nav/guest.html";
    if (!logged_in()) {
        require_once($guest);
    } else {
        $db = new SQLite3(USERS_DB, SQLITE3_OPEN_READWRITE);
        $user = get_user_from_token($_COOKIE["sulv-token"]);
        if ($user === false) {
            setcookie("sulv-token", "", time() - 3600, "/");
            require_once($guest);
        } else {
            require_once($loggedin);
        }
    }
    ?>
</div>