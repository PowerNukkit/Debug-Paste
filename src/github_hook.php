<?php
require_once '../github_secret.php';

$hook = json_decode(file_get_contents('php://input'), true);

file_put_contents('../debug.txt', print_r($hook, true));
