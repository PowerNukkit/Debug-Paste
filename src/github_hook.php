<?php
@require_once '../github_secret.php';

define('LOGFILE', '../github-webhook.log');

if (!defined('GITHUB_SECRET')) {
    http_response_code(500);
    die();
}

// receive POST data for signature calculation, don't change!
$post_data = file_get_contents('php://input');
$signature = hash_hmac('sha1', $post_data, GITHUB_SECRET);

// required data in headers - probably doesn't need changing
$required_headers = array(
    'REQUEST_METHOD' => 'POST',
    'HTTP_X_GITHUB_EVENT' => '^issue(_comment)?$',
    'HTTP_USER_AGENT' => '@^GitHub-Hookshot/?.*@',
    'HTTP_X_HUB_SIGNATURE' => 'sha1=' . $signature,
);

// END OF CONFIGURATION

error_reporting(0);

function log_msg($msg) {
    if(LOGFILE != '') {
        file_put_contents(LOGFILE, $msg . "\n", FILE_APPEND);
    }
}

function array_matches($have, $should, $name = 'array') {
    $ret = true;
    if(is_array($have)) {
        foreach($should as $key => $value) {
            if(!array_key_exists($key, $have)) {
                log_msg("Missing: $key");
                $ret = false;
            }
            else if(is_array($value) && is_array($have[$key])) {
                $ret &= array_matches($have[$key], $value);
            }
            else if(is_array($value) || is_array($have[$key])) {
                log_msg("Type mismatch: $key");
                $ret = false;
            }
            else if(!preg_match_all($value, $have[$key])) {
                log_msg("Failed comparison: $key={$have[$key]} (expected $value)");
                $ret = false;
            }
        }
    }
    else {
        log_msg("Not an array: $name");
        $ret = false;
    }
    return $ret;
}

log_msg("=== Received request from {$_SERVER['REMOTE_ADDR']} ===");
header("Content-Type: text/plain");
$data = json_decode($post_data, true);
if(array_matches($_SERVER, $required_headers, '$_SERVER')) {
    require_once 'github/hook_executor.php';
    execute_hook($data, $_SERVER['HTTP_X_GITHUB_EVENT']);
}
else {
    http_response_code(403);
    die("Forbidden\n");
}
