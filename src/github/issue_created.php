<?php
require_once 'issue_edited.php';
function handle_issue_created($hook) {
    handle_issue_edited($hook);
}
