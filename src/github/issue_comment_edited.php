<?php
require_once __DIR__.'/../import.inc.php';

function handle_issue_comment_edited($hook) {
    $new_body = auto_import_pastes($hook['comment']['body']);
    if ($new_body != $hook['comment']['body']) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $hook['comment_url']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array('body' => $new_body)));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/vnd.github.machine-man-preview+json',
            'Authorization: Bearer ' . create_jwt()
        ));
        print_r(curl_exec($curl));
        curl_close($curl);
    }
}
