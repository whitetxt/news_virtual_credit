<?php
require_once __DIR__ . "/../config.php";
require_once DB_PATH . "/users.php";

function logged_in()
{
    if (empty($_COOKIE["sulv-token"])) {
        log_error('Not logged in - no cookie token');
        return false;
    }
    $session = get_session($_COOKIE["sulv-token"]);
    if ($session === false) {
        log_error('Not logged in - no session found');
        setcookie("sulv-token", "", time() - 3600, "/");
        return false;
    }
    $user = get_user_from_username($session->username);
    if ($user === false) {
        log_error('Not logged in - no user found');
        setcookie("sulv-token", "", time() - 3600, "/");
        return false;
    }
    if (!is_null($session->expires_at) && $session->expires_at < time()) {
        log_error('Not logged in - session expired');
        setcookie("sulv-token", "", time() - 3600, "/");
        delete_session($session->token);
        return false;
    }
    if ($user->enabled === false) {
        log_error('Not logged in - user disabled');
        setcookie("sulv-token", "", time() - 3600, "/");
        delete_session($session->token);
        return false;
    }
    return true;
}

function require_flags($token, $flags)
{
    if (empty($token)) {
        log_error('Require flags - no token');
        header("location: /voucher/");
        return;
    }

    $user = get_user_from_token($token);
    if ($user === false) {
        log_error('Require flags - no user found');
        header("location: /voucher/");
        return;
    }

    foreach ($flags as $flag) {
        if (!$user->has_permission($flag)) {
            log_error('Require flags - insufficient permissions');
            header("location: /voucher/");
            return;
        }
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