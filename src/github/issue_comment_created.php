<?php
require_once 'issue_comment_edited.php';
function handle_issue_comment_created($hook) {
    handle_issue_edited($hook);
}
