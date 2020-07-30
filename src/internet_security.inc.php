<?php
function anonymize_ips($content) {
    $ips = array();
    return preg_replace_callback('@[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}@', function ($matches) use ($ips) {
        $ip = $matches[0];
        if (isset($ips[$ip])) {
            return $ips[$ip];
        }
        $anonymous = "1.1.1.".sizeof($ips);
        $ips[$ip] = $anonymous;
        return $anonymous;
    }, $content);
}
