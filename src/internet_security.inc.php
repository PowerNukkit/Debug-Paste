<?php
function anonymize_ips($content) {
    $ips = array();
    $anonymize_ip = function ($matches) use ($ips) {
        $ip = $matches[2];
        if (isset($ips[$ip])) {
            return $matches[1].$ips[$ip].$matches[3];
        }
        $anonymous = "1.1.1.".sizeof($ips);
        $ips[$ip] = $anonymous;
        return $matches[1].$anonymous.$matches[3];
    };

    $content = preg_replace_callback('@(\[/)?([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})(:)@', $anonymize_ip, $content);

    $sockets = array();
    $content = preg_replace_callback('@([0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}:)([0-9]+)@', function ($matches) use ($sockets) {
        if (isset($sockets[$matches[0]])) {
            return $sockets[$matches[0]];
        }
        $anonymous = $matches[1].(sizeof($sockets)+1000);
        $sockets[$matches[0]] = $anonymous;
        return $anonymous;
    }, $content);

    $content = preg_replace_callback('@(\s*(?:server-(?:ip|port)|level-seed|rcon.password|(?:sub-)?motd)\s*=)[^\n\r]+|passwo?r?d@', function ($matches) {
        return $matches[1].'#auto-removed#';
    }, $content);

    return $content;
}
