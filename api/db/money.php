<?php
require_once "config.php";
class Transaction {
    public $id;
	public $username;
    public $type;
	public $amount;
	public $description;
    public $time;

	public function __construct($id, $username, $type, $amount, $description, $time) {
		$this->id = $id;
		$this->username = $username;
		$this->type = $type;
		$this->amount = $amount;
        $this->description = $description;
        $this->time = $time;
	}
};

function db_to_transaction($arr) {
	return new Transaction($arr["id"], $arr["username"], $arr["type"], $arr["amount"], $arr["description"], $arr["time"]);
}

function get_transaction_from_id($id) {
	$db = new SQLite3(USERS_DB, SQLITE3_OPEN_READONLY);
	$stmt = $db->prepare("SELECT * FROM transactions WHERE id = :id");
	$stmt->bindParam(":id", $id);
	$res = $stmt->execute();
	if ($res === false) {
		return false;
	}
	$arr = $res->fetchArray();
	if ($arr === false) {
		return false;
	}
	return db_to_transaction($arr);
}

function create_transaction($username, $type, $amount, $description) {
	$db = new SQLite3(USERS_DB, SQLITE3_OPEN_READWRITE);

	$stmt = $db->prepare("INSERT INTO transactions(username, type, amount, description, time) VALUES(:usr, :typ, :amt, :des, :tim)");
	$stmt->bindParam(":usr", $username);
    $stmt->bindParam(":typ", $type);
	$stmt->bindParam(":amt", $amount);
    $stmt->bindParam(":des", $description);
    $stmt->bindValue(":tim", time());

	$res = $stmt->execute();
	return $res;
}

function create_new_transaction_with_time($username, $type, $amount, $description, $time) {
    $db = new SQLite3(USERS_DB, SQLITE3_OPEN_READWRITE);

	$stmt = $db->prepare("INSERT INTO transactions(username, type, amount, description, time) VALUES(:usr, :typ, :amt, :des, :tim)");
	$stmt->bindParam(":usr", $username);
    $stmt->bindParam(":typ", $type);
	$stmt->bindParam(":amt", $amount);
    $stmt->bindParam(":des", $description);
    $stmt->bindParam(":tim", $time);

	$res = $stmt->execute();
	return $res;
}

function get_transactions() {
	$db = new SQLite3(USERS_DB, SQLITE3_OPEN_READONLY);

	$stmt = $db->prepare("SELECT * FROM transactions");
	$res = $stmt->execute();
	if ($res === false) {
		return false;
	}
	$out = array();
	while ($arr = $res->fetchArray()) {
		array_push($out, db_to_transaction($arr));
	}
	return $out;
}

function get_users_transactions($username) {
	$db = new SQLite3(USERS_DB, SQLITE3_OPEN_READONLY);

	$stmt = $db->prepare("SELECT * FROM transactions WHERE username = :usr");
	$stmt->bindParam(":usr", $username);
	$res = $stmt->execute();
	if ($res === false) {
		return false;
	}
	$out = array();
	while ($arr = $res->fetchArray()) {
		array_push($out, db_to_transaction($arr));
	}
	return $out;
}