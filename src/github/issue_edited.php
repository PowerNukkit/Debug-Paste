<?php
require_once __DIR__.'/../import.inc.php';

function handle_issue_edited($hook) {
    $new_body = auto_import_pastes($hook['issue']['body']);
    if ($new_body != $hook['issue']['body']) {
        $curl = curl_init();
        $opts = array (
            CURLOPT_URL => $hook['issue']['url'],
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
        curl_exec($curl);
        curl_close($curl);
    }
    return $new_body;
}
