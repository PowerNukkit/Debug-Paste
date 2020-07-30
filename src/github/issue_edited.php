<?php
require_once '../import.inc.php';
require_once '../config.inc.php';

function handle_issue_edited($hook) {
    $new_body = auto_import_pastes($hook['issue']['body']);
    if ($new_body != $hook['issue']['body']) {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $hook['url']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode(array('body' => $new_body)));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Accept: application/vnd.github.machine-man-preview+json',
            'Authorization: Bearer '.create_jwt()
        ));
        curl_exec($curl);
        curl_close($curl);
    }
}
