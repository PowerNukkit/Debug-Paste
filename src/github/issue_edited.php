<?php
require_once __DIR__.'/../import.inc.php';
require_once 'chatter.php';

function handle_issue_edited($hook) {
    $new_body = auto_import_pastes($hook['issue']['body']);
    if ($new_body != $hook['issue']['body']) {
        update_issue($hook['installation']['id'], $hook['issue']['url'], array('body'=>$new_body));
    }
    return $new_body;
}
