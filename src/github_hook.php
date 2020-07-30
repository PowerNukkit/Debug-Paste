<?php
require_once '../github_secret.php';

if (!isset($_SERVER['HTTP_SECRET_TOKEN']) || sha1(GITHUB_SECRET) != $_SERVER['HTTP_SECRET_TOKEN']) {
    http_response_code(403);
    file_put_contents('../debug.txt', print_r($_SERVER, true));
    die();
}

$hook = json_decode(file_get_contents('php://input'), true);

file_put_contents('../debug.txt', print_r($hook, true));

if (!isset($hook['action'])) {
    http_response_code(412);
    die('Illegal input');
}

switch ($hook['action']) {
    case 'edited':
        require_once 'github/issue_edited.php';
        handle_issue_edited($hook);
        break;
    case 'created':
        require_once 'github/issue_created.php';
        handle_issue_created($hook);
        break;
    default:
        http_response_code(400);
}
