<?php
require_once 'issue_comment_edited.php';
require_once __DIR__.'/../important_data_scanner.php';

function handle_issue_comment_created($hook) {
    $new_body = handle_issue_comment_edited($hook);

}
