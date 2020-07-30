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
            'Accept: application/vnd.github.v3+json',
            'Authorization: Bearer ' . create_jwt()
        ));
        file_put_contents(__DIR__.'/../../last_edit.txt', print_r(curl_exec($curl), true));
        curl_close($curl);
    }
}
