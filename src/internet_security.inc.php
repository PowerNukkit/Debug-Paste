<?php
function anonymize_ips($content) {
    $ips = array();
    $content = preg_replace_callback('@(\[/)([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})(:)@', function ($matches) use ($ips) {
        $ip = $matches[2];
        if (isset($ips[$ip])) {
            return $matches[1].$ips[$ip].$matches[3];
        }
        $anonymous = "1.1.1.".sizeof($ips);
        $ips[$ip] = $anonymous;
        return $matches[1].$anonymous.$matches[3];
    }, $content);

    $content = preg_replace_callback('@(\s*(?:server-(?:ip|port)|level-seed|(?:sub-)?motd)\s*=).*?@', function ($matches) {
        return $matches[1].'#auto-removed#';
    }, $content);

    return $content;
}
