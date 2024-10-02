<?php
require_once "config.php";
class User
{
    public $username;
    public $password;
    public $salt;
    public $created_at;
    public $token;
    public $expires_at;
    public $access_level;
    public $enabled;
    public $role;
    public $balance;
    public $secret;

    public function __construct($username, $password, $salt, $created_at, $token, $expires_at, $access_level, $enabled, $role, $balance, $secret)
    {
        $this->username = $username;
        $this->password = $password;
        $this->salt = $salt;
        $this->created_at = $created_at;
        $this->token = $token;
        $this->expires_at = $expires_at;
        $this->access_level = $access_level;
        $this->enabled = $enabled;
        $this->role = $role;
        $this->balance = $balance;
        $this->secret = $secret;
    }
}
;

function db_to_user($arr)
{
    return new User($arr["username"], $arr["password"], $arr["salt"], $arr["created_at"], $arr["token"], $arr["expires_at"], $arr["access_level"], $arr["enabled"] == 1, $arr["role"], $arr["balance"], $arr["secret"]);
}

function get_access_level($user)
{
    if ($user->access_level == 0) {
        return 'Member';
    } else if ($user->access_level == 1) {
        return 'Admin';
    } else {
        return 'Unknown';
    }
}

function get_user_from_token($token)
{
    $db = new SQLite3(USERS_DB, SQLITE3_OPEN_READONLY);
    $stmt = $db->prepare("SELECT * FROM users WHERE token = :tkn");
    $stmt->bindParam(":tkn", $token);
    $res = $stmt->execute();
    if ($res === false) {
        return false;
    }
    $arr = $res->fetchArray();
    if ($arr === false) {
        return false;
    }
    return db_to_user($arr);
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
        return false;
    }
    return db_to_user($arr);
}

function create_new_user($username, $password, $salt, $token)
{
    $db = new SQLite3(USERS_DB, SQLITE3_OPEN_READWRITE);

    $stmt = $db->prepare("INSERT INTO users(username, password, salt, created_at, token, access_level, enabled) VALUES(:usr, :pwd, :slt, :crt, :tkn, :acl, :enb)");
    $stmt->bindParam(":usr", $username);
    $stmt->bindParam(":pwd", $password);
    $stmt->bindParam(":slt", $salt);
    $stmt->bindValue(":crt", time(), SQLITE3_INTEGER);
    $stmt->bindParam(":tkn", $token);
    $stmt->bindValue(":acl", USER_PERMISSION_USER);
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
    $out = array();
    while ($arr = $res->fetchArray()) {
        array_push($out, db_to_user($arr));
    }
    return $out;
}

function update_user($usr)
{
    $db = new SQLite3(USERS_DB, SQLITE3_OPEN_READWRITE);

    $stmt = $db->prepare("UPDATE users SET password = :pwd, salt = :slt, created_at = :crt, token = :tkn, expires_at = :exp, enabled = :enb, access_level = :acl, role = :rol, balance = :bal, secret = :sec WHERE username = :usr");
    $stmt->bindParam(":usr", $usr->username);
    $stmt->bindParam(":pwd", $usr->password);
    $stmt->bindParam(":slt", $usr->salt);
    $stmt->bindParam(":crt", $usr->created_at, SQLITE3_INTEGER);
    $stmt->bindParam(":tkn", $usr->token);
    if ($usr->enabled) {
        $stmt->bindParam(":enb", $usr->enabled, SQLITE3_INTEGER);
    } else {
        $stmt->bindValue(":enb", null, SQLITE3_NULL);
    }
    $stmt->bindParam(":exp", $usr->expires_at, SQLITE3_INTEGER);
    $stmt->bindParam(":acl", $usr->access_level, SQLITE3_INTEGER);
    $stmt->bindParam(":rol", $usr->role);
    $stmt->bindParam(":bal", $usr->balance);
    $stmt->bindParam(":sec", $usr->secret);

    $res = $stmt->execute();
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
    $out = array();
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
    $out = array();
    while ($arr = $res->fetchArray()) {
        array_push($out, db_to_role($arr));
    }
    return $out;
}