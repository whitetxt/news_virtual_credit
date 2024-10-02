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
        $stmt = $db->prepare("SELECT username FROM users WHERE token = :tkn");
        $stmt->bindParam(":tkn", $_COOKIE["sulv-token"], SQLITE3_TEXT);
        $res = $stmt->execute();
        $arr = $res->fetchArray();
        if ($arr === false) {
            setcookie("sulv-token", "", time() - 3600, "/");
            require_once($guest);
        } else {
            $stmt = $db->prepare("SELECT access_level FROM users WHERE username = :usr");
            $stmt->bindParam(":usr", $arr["username"], SQLITE3_TEXT);
            $res = $stmt->execute();
            $arr = $res->fetchArray();
            require_once($loggedin);
        }
    }
    ?>
</div>