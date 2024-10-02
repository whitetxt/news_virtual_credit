<?php require "../config.php"; ?>
<?php
$db = new SQLite3(USERS_DB);
$stmt = $db->prepare("SELECT username FROM users WHERE token = :tkn");
$stmt->bindParam(":tkn", $_COOKIE["sulv-token"]);
$res = $stmt->execute();
$arr = $res->fetchArray();
if ($arr !== false) {
    $stmt->close();
    $stmt = $db->prepare("UPDATE users SET token = :tkn WHERE username = :usr");
    $stmt->bindParam(":usr", $arr["username"]);
    $res = $stmt->execute();
    if ($res === false) {
        $db->close();
        die("Failed to invalidate token.");
    }
}
$db->close();

setcookie("sulv-token", "", time() - 3600, "/");

header("location: /voucher/");
?>