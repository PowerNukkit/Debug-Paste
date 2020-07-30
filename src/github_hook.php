<?php
@require_once '../github_secret.php';

if (!defined('GITHUB_SECRET')) {
    http_response_code(500);
    die();
}

if (!isset($_SERVER['HTTP_X_HUB_SIGNATURE'])
    || !isset($_SERVER['HTTP_X_GITHUB_EVENT'])
    || !isset($_SERVER['HTTP_X_GITHUB_DELIVERY'])
    || strlen($_SERVER['HTTP_SECRET_TOKEN']) < 6
    || substr($_SERVER['HTTP_SECRET_TOKEN'], 0, 5) != 'sha1='
    || sha1(GITHUB_SECRET) != substr($_SERVER['HTTP_SECRET_TOKEN'], 5)
    || $_SERVER['HTTP_X_GITHUB_EVENT'] != 'issue_comment'
) {
    http_response_code(403);
    file_put_contents('../last_failure.txt', print_r($_SERVER, true));
    die('Illegal input');
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
