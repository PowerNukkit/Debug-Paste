<?php
@require_once '../github_secret.php';

if (!defined('GITHUB_SECRET')) {
    http_response_code(500);
    die();
}

$post_data = file_get_contents('php://input');
$signature = hash_hmac('sha1', $post_data, GITHUB_SECRET);

header("Content-Type: text/plain");
if ($_SERVER['REQUEST_METHOD'] != 'POST'
    || $_SERVER['HTTP_X_GITHUB_EVENT'] != 'issue' && $_SERVER['HTTP_X_GITHUB_EVENT'] != 'issue_comment'
    || !fnmatch('GitHub-Hookshot/*', $_SERVER['HTTP_USER_AGENT'])
    || $_SERVER['HTTP_X_HUB_SIGNATURE'] != "sha1=$signature"
) {
    http_response_code(403);
    die("Forbidden\n");
}

$data = json_decode($post_data, true);
require_once 'github/hook_executor.php';
execute_hook($data, $_SERVER['HTTP_X_GITHUB_EVENT']);

