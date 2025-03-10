<?php
require_once __DIR__ . "/../config.php";
class User
{
    public $username;
    public $password;
    public $salt;
    public $created_at;
    public $token;
    public $expires_at;
    public $enabled;
    public $role;
    public $balance;
    public $secret;
    public $flags;
    public $permissions;

    public function __construct($username, $password, $salt, $created_at, $enabled, $role, $balance, $secret, $flags)
    {
        $this->username = $username;
        $this->password = $password;
        $this->salt = $salt;
        $this->created_at = $created_at;
        $this->enabled = $enabled;
        $this->role = $role;
        $this->balance = $balance;
        $this->secret = $secret;
        $this->flags = $flags;
        $this->permissions = [];
        foreach (FLAGS as $flag) {
            if ($this->flags & constant("FLAG_" . strtoupper($flag))) {
                $this->permissions[$flag] = true;
            } else {
                $this->permissions[$flag] = false;
            }
        }
    }

    public function has_permission($permission) {
        // var_dump($this->permissions);
        return $this->permissions[$permission];
    }
}

function db_to_user($arr)
{
    return new User($arr["username"], $arr["password"], $arr["salt"], $arr["created_at"], $arr["enabled"] == 1, $arr["role"], $arr["balance"], $arr["secret"], $arr["flags"]);
}

function get_user_from_username($username)
{
    $db = new SQLite3(USERS_DB, SQLITE3_OPEN_READONLY);
    $stmt = $db->prepare("SELECT * FROM users WHERE username = :usr");
    $stmt->bindParam(":usr", $username);
    $res = $stmt->execute();
    if ($res === false) {
        return false;
    }
    $arr = $res->fetchArray();
    if ($arr === false) {
        log_error("Get user from username - User not found");
        return false;
    }
    return db_to_user($arr);
}

function create_new_user($username, $password, $salt)
{
    $db = new SQLite3(USERS_DB, SQLITE3_OPEN_READWRITE);

    $stmt = $db->prepare("INSERT INTO users(username, password, salt, created_at, enabled) VALUES(:usr, :pwd, :slt, :crt, :enb)");
    $stmt->bindParam(":usr", $username);
    $stmt->bindParam(":pwd", $password);
    $stmt->bindParam(":slt", $salt);
    $stmt->bindValue(":crt", time(), SQLITE3_INTEGER);
    $stmt->bindValue(":enb", 1, SQLITE3_INTEGER);

    $res = $stmt->execute();
    return $res;
}

function get_users()
{
    $db = new SQLite3(USERS_DB, SQLITE3_OPEN_READONLY);

    $stmt = $db->prepare("SELECT * FROM users");
    $res = $stmt->execute();
    if ($res === false) {
        return false;
    }
    $out = [];
    while ($arr = $res->fetchArray()) {
        array_push($out, db_to_user($arr));
    }
    return $out;
}

function update_user($usr)
{
    $db = new SQLite3(USERS_DB, SQLITE3_OPEN_READWRITE);

    $stmt = $db->prepare("UPDATE users SET password = :pwd, salt = :slt, created_at = :crt, enabled = :enb, role = :rol, balance = :bal, secret = :sec, flags = :flg WHERE username = :usr");
    $stmt->bindParam(":usr", $usr->username);
    $stmt->bindParam(":pwd", $usr->password);
    $stmt->bindParam(":slt", $usr->salt);
    $stmt->bindParam(":crt", $usr->created_at, SQLITE3_INTEGER);
    if ($usr->enabled) {
        $stmt->bindParam(":enb", $usr->enabled, SQLITE3_INTEGER);
    } else {
        $stmt->bindValue(":enb", null, SQLITE3_NULL);
    }
    $stmt->bindParam(":rol", $usr->role);
    $stmt->bindParam(":bal", $usr->balance);
    $stmt->bindParam(":sec", $usr->secret);
    $stmt->bindParam(":flg", $usr->flags);

    $res = $stmt->execute();
    if (!$res) {
        log_error("Update user - Failed to update user", ["error" => $db->lastErrorMsg()]);
    }
    return $res;
}

function delete_user($username)
{
    $u = get_user_from_username($username);
    $db = new SQLite3(USERS_DB, SQLITE3_OPEN_READWRITE);
    $stmt = $db->prepare("DELETE FROM users WHERE username = :usr");
    $stmt->bindParam(":usr", $u->username);

    $res = $stmt->execute();
    return $res;
}

function get_users_like($like)
{
    $db = new SQLite3(USERS_DB, SQLITE3_OPEN_READONLY);
    $db->query("PRAGMA case_sensitive_like = false");

    $stmt = $db->prepare("SELECT * FROM users WHERE username LIKE :lik");
    $stmt->bindValue(":lik", "%" . $like . "%");
    $res = $stmt->execute();
    if ($res === false) {
        return false;
    }
    $out = [];
    while ($arr = $res->fetchArray()) {
        array_push($out, db_to_user($arr));
    }
    return $out;
}

class Role
{
    public $name;
    public $description;

    public function __construct($name, $description)
    {
        $this->name = $name;
        $this->description = $description;
    }
}

function db_to_role($arr)
{
    return new Role($arr["name"], $arr["description"]);
}

function get_roles()
{
    $db = new SQLite3(USERS_DB, SQLITE3_OPEN_READONLY);

    $stmt = $db->prepare("SELECT * FROM roles");
    $res = $stmt->execute();
    if ($res === false) {
        return false;
    }
    $out = [];
    while ($arr = $res->fetchArray()) {
        array_push($out, db_to_role($arr));
    }
    return $out;
}

class Session
{
    public $username;
    public $token;
    public $expires_at;

    public function __construct($username, $token, $expires_at)
    {
        $this->username = $username;
        $this->token = $token;
        $this->expires_at = $expires_at;
    }
}

function db_to_session($arr)
{
    return new Session($arr["username"], $arr["token"], $arr["expires_at"]);
}

function create_session($username, $token, $expires_at)
{
    $db = new SQLite3(USERS_DB, SQLITE3_OPEN_READWRITE);

    $stmt = $db->prepare("INSERT INTO sessions(username, token, expires_at) VALUES(:usr, :tkn, :exp)");
    $stmt->bindParam(":usr", $username);
    $stmt->bindParam(":tkn", $token);
    $stmt->bindParam(":exp", $expires_at, SQLITE3_INTEGER);

    $res = $stmt->execute();
    return $res;
}

function delete_session($token)
{
    $db = new SQLite3(USERS_DB, SQLITE3_OPEN_READWRITE);

    $stmt = $db->prepare("DELETE FROM sessions WHERE token = :tkn");
    $stmt->bindParam(":tkn", $token);
    $res = $stmt->execute();
    return $res;
}

function update_session($session)
{
    $db = new SQLite3(USERS_DB, SQLITE3_OPEN_READWRITE);

    $stmt = $db->prepare("UPDATE sessions SET username = :usr, expires_at = :exp WHERE token = :tkn");
    $stmt->bindParam(":usr", $session->username);
    $stmt->bindParam(":tkn", $session->token);
    $stmt->bindParam(":exp", $session->expires_at, SQLITE3_INTEGER);

    $res = $stmt->execute();
    if (!$res) {
        log_error("Update session - Failed to update session", ["error" => $db->lastErrorMsg()]);
    }
    return $res;
}

function get_session($token)
{
    $db = new SQLite3(USERS_DB, SQLITE3_OPEN_READONLY);
    $stmt = $db->prepare("SELECT * FROM sessions WHERE token = :tkn");
    $stmt->bindParam(":tkn", $token);
    $res = $stmt->execute();
    if ($res === false) {
        return false;
    }
    $arr = $res->fetchArray();
    if ($arr === false) {
        log_error("Get session - Session not found");
        return false;
    }
    return db_to_session($arr);
}

function get_user_sessions($username)
{
    $db = new SQLite3(USERS_DB, SQLITE3_OPEN_READONLY);
    $stmt = $db->prepare("SELECT * FROM sessions WHERE username = :usr");
    $stmt->bindParam(":usr", $username);
    $res = $stmt->execute();
    if ($res === false) {
        return false;
    }
    $out = [];
    while ($arr = $res->fetchArray()) {
        array_push($out, db_to_session($arr));
    }
    return $out;
}

function get_user_from_token($token)
{
    $db = new SQLite3(USERS_DB, SQLITE3_OPEN_READONLY);
    $stmt = $db->prepare("SELECT * FROM sessions WHERE token = :tkn");
    $stmt->bindParam(":tkn", $token);
    $res = $stmt->execute();
    if ($res === false) {
        return false;
    }
    $arr = $res->fetchArray();
    if ($arr === false) {
        log_error("Get user from token - Session not found");
        return false;
    }
    $session = db_to_session($arr);
    $username = $session->username;
    return get_user_from_username($username);
}
