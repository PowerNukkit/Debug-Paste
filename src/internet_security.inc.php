<?php
function anonymize_ips($content) {
    $ips = array();
    return preg_replace_callback('@(\[/)([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})(:)@', function ($matches) use ($ips) {
        $ip = $matches[1];
        if (isset($ips[$ip])) {
            return $matches[0].$ips[$ip].$matches[2];
        }
        $anonymous = "1.1.1.".sizeof($ips);
        $ips[$ip] = $anonymous;
        return $matches[0].$anonymous.$matches[2];
    }, $content);
}
