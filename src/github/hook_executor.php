<?php
function execute_hook($hook, $type) {
    file_put_contents("../debug-$type.txt", print_r($hook, true));

    switch ($type) {
        case 'issues':
            execute_issue_hook($hook);
            break;
        case 'issue_comment':
            execute_issue_comment_hook($hook);
            break;
        default:
            http_response_code(202);
    }
}

function execute_issue_hook($hook) {
    if (!isset($hook['action'])) {
        http_response_code(412);
        die('Illegal input');
    }

    switch ($hook['action']) {
        case 'edited':
            require_once 'issue_edited.php';
            handle_issue_edited($hook);
            break;
        case 'opened':
            require_once 'issue_created.php';
            handle_issue_created($hook);
            break;
        default:
            http_response_code(202);
    }
}

function execute_issue_comment_hook($hook) {
    if (!isset($hook['action'])) {
        http_response_code(412);
        die('Illegal input');
    }

    switch ($hook['action']) {
        case 'edited':
            require_once 'issue_comment_edited.php';
            handle_issue_comment_edited($hook);
            break;
        case 'created':
            require_once 'issue_comment_created.php';
            handle_issue_comment_created($hook);
            break;
        default:
            http_response_code(202);
    }
}
