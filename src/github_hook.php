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

#// required data in POST body - set your targeted branch and repository here!
$required_data = array(
#    'ref' => 'refs/heads/master',
#    'repository' => array(
#        'full_name' => 'owner/repo',
#    ),
);

// required data in headers - probably doesn't need changing
$required_headers = array(
    'REQUEST_METHOD' => 'POST',
    'HTTP_X_GITHUB_EVENT' => 'issue_comment',
    'HTTP_USER_AGENT' => 'GitHub-Hookshot/*',
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
            else if(!fnmatch($value, $have[$key])) {
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
// First do all checks and then report back in order to avoid timing attacks
$headers_ok = array_matches($_SERVER, $required_headers, '$_SERVER');
$data_ok = array_matches($data, $required_data, '$data');
if($headers_ok && $data_ok) {
    execute_hook($data);
}
else {
    http_response_code(403);
    die("Forbidden\n");
}

function execute_hook($hook) {
    file_put_contents('../debug.txt', print_r($hook, true));

    if (!isset($hook['action'])) {
        http_response_code(412);
        die('Illegal input');
    }

    switch ($hook['action']) {
        case 'edited':
            require_once 'github/issue_edited.php';
            handle_issue_edited($hook);
            break;
        case 'created':
            require_once 'github/issue_created.php';
            handle_issue_created($hook);
            break;
        default:
            http_response_code(400);
    }
}
