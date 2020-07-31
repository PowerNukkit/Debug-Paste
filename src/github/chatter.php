<?php
require_once __DIR__.'../config.inc.php';

function send_new_comment($installation, $comments_url, $comment) {
    $curl = curl_init();
    $opts = array (
        CURLOPT_URL => $comments_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_USERAGENT => 'PowerNukkit',
        CURLOPT_POSTFIELDS => json_encode(array('body' => $comment)),
        CURLOPT_HTTPHEADER => array(
            'Accept: application/vnd.github.v3+json',
            "Authorization: ".create_installation_token($installation)
        )
    );
    curl_setopt_array($curl, $opts);
    curl_exec($curl);
    curl_close($curl);
}

function set_issue_labels($installation, $issue_url, $labels) {
    $curl = curl_init();
    $opts = array (
        CURLOPT_URL => $issue_url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'PATCH',
        CURLOPT_USERAGENT => 'PowerNukkit',
        CURLOPT_POSTFIELDS => json_encode(array('labels' => $labels)),
        CURLOPT_HTTPHEADER => array(
            'Accept: application/vnd.github.v3+json',
            "Authorization: ".create_installation_token($installation)
        )
    );
    curl_setopt_array($curl, $opts);
    curl_exec($curl);
    curl_close($curl);
}
