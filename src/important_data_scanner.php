<?php
function detect_versions($content) {
    $versions = array();
    $commits = array();
    preg_match_all('@\b[vV]?(\d+(\.[\dxX]+)+-[pP][nN](-[a-zA-Z0-9._]+)*)\b@', $content, $direct_versions, PREG_SET_ORDER);
    foreach ($direct_versions as $match) {
        $versions[] = strtoupper($match[1]);
    }

    preg_match_all('@\b[gG][iI][tT]-([a-fA-F0-9]{7,})\b@', $content, $git_matches, PREG_SET_ORDER);
    foreach ($git_matches as $match) {
        $commits[] = strtolower(substr($match[1], 0, 7));
    }

    preg_match_all('@\b[a-f0-9]{7}\b@', $content, $short_commits, PREG_SET_ORDER);
    foreach ($short_commits as $match) {
        $commits[] = strtolower($match[0]);
    }

    preg_match_all('@\b[a-f0-9]{40}\b@', $content, $long_commits, PREG_SET_ORDER);
    foreach ($long_commits as $match) {
        $commits[] = strtolower(substr($match[0], 0, 7));
    }

    array_unique($versions);
    array_unique($short_commits);

    return array(
        'versions' => $versions,
        'commits' => $commits
    );
}

function resolve_commits($installation, $repo_url, $list) {
    if (empty($list)) {
        return array();
    }

    $token = create_installation_token($installation);

    $resolved = array();
    foreach ($list as $ref) {
        $curl = curl_init();
        $opts = array(
            CURLOPT_URL => "$repo_url/git/commits/$ref",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_USERAGENT => 'PowerNukkit',
            CURLOPT_HTTPHEADER => array(
                'Accept: application/vnd.github.v3+json',
                "Authorization: $token"
            )
        );
        curl_setopt_array($curl, $opts);
        $commit = curl_exec($curl);
        curl_close($curl);
        $json = json_decode($commit);
        if (!$commit || !$json || !$json->sha) {
            $resolved[] = $json->sha;
        }
    }

    array_unique($resolved);
    return $resolved;
}
