<?php
require_once __DIR__ . "/../config.php";
require_once DB_PATH . "/users.php";

function logged_in()
{
    if (empty($_COOKIE["sulv-token"])) {
        return false;
    }
    $db = new SQLite3(USERS_DB, SQLITE3_OPEN_READWRITE);
    $stmt = $db->prepare("SELECT username, expires_at, enabled FROM users WHERE token = :tkn");
    $stmt->bindParam(":tkn", $_COOKIE["sulv-token"]);
    $res = $stmt->execute();
    $arr = $res->fetchArray();
    if ($arr === false) {
        setcookie("sulv-token", "", time() - 3600, "/");
        return false;
    }
    if (!is_null($arr["expires_at"]) && $arr["expires_at"] < time()) {
        setcookie("sulv-token", "", time() - 3600, "/");
        $stmt = $db->prepare("UPDATE users SET token = :tkn, expires_at = :exp WHERE username = :usr");
        $stmt->bindParam(":usr", $arr["username"]);
        $stmt->bindValue(":tkn", null, SQLITE3_NULL);
        $stmt->bindValue(":exp", null, SQLITE3_NULL);
        $res = $stmt->execute();
        return false;
    }
    if ($arr["enabled"] === null) {
        setcookie("sulv-token", "", time() - 3600, "/");
        $stmt = $db->prepare("UPDATE users SET token = :tkn, expires_at = :exp WHERE username = :usr");
        $stmt->bindParam(":usr", $arr["username"]);
        $stmt->bindValue(":tkn", null, SQLITE3_NULL);
        $stmt->bindValue(":exp", null, SQLITE3_NULL);
        $res = $stmt->execute();
        return false;
    }
    return true;
}

function require_minimum_permissions($token, $access_level)
{
    if (empty($token)) {
        header("location: /voucher/");
        return;
    }

    $user = get_user_from_token($token);
    if ($user === false || $user->access_level < $access_level) {
        header("location: /voucher/");
        return;
    }
}

function current_user()
{
    if (!logged_in()) {
        return false;
    }
    $user = get_user_from_token($_COOKIE["sulv-token"]);
    return $user;
}
?>