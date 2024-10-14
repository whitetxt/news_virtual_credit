<?php
require_once __DIR__ . "/../config.php";
require_once DB_PATH . "/users.php";

function logged_in()
{
    if (empty($_COOKIE["sulv-token"])) {
        return false;
    }
    $session = get_session($_COOKIE["sulv-token"]);
    if ($session === false) {
        setcookie("sulv-token", "", time() - 3600, "/");
        return false;
    }
    $user = get_user_from_username($session->username);
    if ($user === false) {
        setcookie("sulv-token", "", time() - 3600, "/");
        return false;
    }
    if (!is_null($session->expires_at) && $session->expires_at < time()) {
        setcookie("sulv-token", "", time() - 3600, "/");
        delete_session($session->token);
        return false;
    }
    if ($user->enabled === false) {
        setcookie("sulv-token", "", time() - 3600, "/");
        delete_session($session->token);
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