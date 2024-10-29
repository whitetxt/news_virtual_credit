<?php
require_once __DIR__ . "/../config.php";
require_once API_PATH . "/accounts/functions.php";
require_flags($_COOKIE["sulv-token"], ["ADMIN"]);
require_once DB_PATH . "/users.php";
require_once DB_PATH . "/money.php";

if (empty($_GET["start"]) || empty($_GET["end"])) {
    header("Content-Type: application/json");
    die(json_encode(["status" => "error", "message" => "Missing fields."]));
}

header('Content-Description: File Transfer'); 
header('Content-Type: text/csv'); 
header('Content-Disposition: attachment; filename="report.csv"');
header('Expires: 0'); 
header('Cache-Control: must-revalidate'); 
header('Pragma: public'); 

$transactions = get_transactions_between($_GET["start"], $_GET["end"]);
foreach ($transactions as $trans) {
    $trans->time = date("Y-m-d H:i:s", $trans->time);
}

$fp = fopen('php://output', 'wb');
fputcsv($fp, get_object_vars($transactions[0]), ",");
foreach ($transactions as $line) {
    // though CSV stands for "comma separated value"
    // in many countries (including France) separator is ";"
    fputcsv($fp, (array)$line, ',');
}
fclose($fp);