<?php
require_once __DIR__.'/../import.inc.php';

function handle_issue_comment_edited($hook) {
    $new_body = auto_import_pastes($hook['comment']['body']);
    if ($new_body != $hook['comment']['body']) {
        $curl = curl_init();
        $opts = array (
            CURLOPT_URL => $hook['comment']['url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_USERAGENT => 'PowerNukkit',
            CURLOPT_POSTFIELDS => json_encode(array('body' => $new_body)),
            CURLOPT_HTTPHEADER => array(
                'Accept: application/vnd.github.v3+json',
                'Authorization: Bearer ' . create_jwt()
            )
        );
        curl_setopt_array($curl, $opts);
        file_put_contents(__DIR__.'/../../last_edit.txt', print_r($opts, true)."\n\n".print_r(curl_exec($curl), true));
        curl_close($curl);
    }
}
