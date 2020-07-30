<?php
if (!isset($_GET['haste'])) {
    http_response_code(412);
    die("Missing data");
}

require_once 'import.inc.php';
$haste = $_GET['haste'];
if (!preg_match("@https://hastebin\.com/[a-zA-Z0-9]+(\.[a-zA-Z0-9]+)?@", $haste)) {
    http_response_code(412);
    die("Illegal URL");
}

echo import_haste($haste);
