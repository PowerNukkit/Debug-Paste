<?php
require_once 'issue_edited.php';
require_once __DIR__.'/../important_data_scanner.php';

function handle_issue_created($hook) {
    $new_body = handle_issue_edited($hook);
    $detections = detect_versions($new_body);
    $versions = $detections['versions'];
    $commits = resolve_commits($hook['installation']['id'], $hook['issue']['repository_url'], $detections['commits']);

    $msg = "Hey ".$hook['issue']['user']['login'].", thank you for the report!\n\n";

    $add_labels = array();

    if (empty($versions) && empty($commits)) {
        $msg .= ":warning: I could not detect find which PowerNukkit version you are using from your message.\n\n" .
            "Please, run the command: `/debugpaste` it will generate a link, send us this link replying this issue. " .
            "It's an important step to get your issue resolved.\n\n" .
            "Don't say 'latest' or 'current version', we support different versions and your latest may not be the same as our latest, ".
            "and your latest will not be the same latest in the future, so please, use a fixed version. Thank you :thumbsup:";

        $add_labels[] = "Status: Awaiting Response";
    }

    if (!empty($versions)) {
        if (sizeof($versions) == 1) {
            $msg .= ":package: Detected the version: $versions[0]\n\n";
        } else {
            $msg .= ":package: Detected the versions:\n";
            foreach ($versions as $version) {
                $msg .= " * $version\n";
            }
            $msg .= "\n";
        }
    }

    if (!empty($commits)) {
        if (sizeof($commits) == 1) {
            $msg .= ":eye_speech_bubble: Detected the commit: $commits[0]\n\n";
        } else {
            $msg .= ":eye_speech_bubble: Detected the commits:\n";
            foreach ($commits as $commit) {
                $msg .= " * $commit\n";
            }
            $msg .= "\n";
        }
    }

    $msg .= "\n<!--\nSTATE: ".json_encode(array(
            'storage' => 1,
            'versions' => $versions,
            'commits' => $commits
        ))."\n-->";
    send_new_comment($hook['installation']['id'], $hook['issue']['comments_url'], $msg);
    if (!empty($add_labels)) {
        foreach ($hook['issue']['labels'] as $label) {
            $add_labels[] = $label['name'];
        }
        array_unique($add_labels);
        set_issue_labels($hook['installation']['id'], $hook['issue']['url'], $add_labels);
    }
}
