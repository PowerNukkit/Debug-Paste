<?php
if (!isset($_GET['haste'])) {
    http_response_code(412);
    die("Missing data");
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'import.inc.php';
$haste = $_GET['haste'];
if (!preg_match("@https://hastebin\.com/[a-zA-Z0-9]+(\.[a-zA-Z0-9]+)?@", $haste)) {
    http_response_code(412);
    die("Illegal URL");
}

echo import_haste($haste);
