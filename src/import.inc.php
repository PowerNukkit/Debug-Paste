<?php
require_once 'config.inc.php';
require_once 'internet_security.inc.php';

function import_haste($url) {
    $code = urlencode(basename($url));
    if (is_file(PASTES_DIR."/$code.html")) {
        return $code;
    }
    $content = file_get_contents("https://hastebin.com/raw/$code", false, stream_context_create(array(
        'http' => array(
            'method' => 'GET',
            'header' => 'User-Agent: Hastebin Java Api'
        )
    )));
    $content = auto_import_pastes($content);
    $content = make_html($content, $code);
    file_put_contents(PASTES_DIR."/$code.html", $content);
    return $code;
}

function auto_import_pastes($content) {
    $content = preg_replace_callback('|https://hastebin\.com/([a-zA-Z0-9]+)|', function ($matches) {
        return "https://debugpaste.powernukkit.org/pastes/".import_haste($matches[1]);
    }, $content);
    return $content;
}

function make_html($content, $code) {
    return "<html><head>
<meta http-equiv='Content-Type' content='text/html;charset=UTF-8'><title>Debug Paste ".htmlspecialchars($code)."</title>
<link rel=\"stylesheet\"
      href=\"//cdnjs.cloudflare.com/ajax/libs/highlight.js/10.1.2/styles/default.min.css\">
<script src=\"//cdnjs.cloudflare.com/ajax/libs/highlight.js/10.1.2/highlight.min.js\"></script>
<script charset=\"UTF-8\"
 src=\"https://cdnjs.cloudflare.com/ajax/libs/highlight.js/10.1.2/languages/java.min.js\"></script>
<script>hljs.initHighlightingOnLoad();</script></head>
<body><pre><code>".restore_powernukkit_links(htmlspecialchars(anonymize_ips($content)))."</code></pre></body></html>";
}

function restore_powernukkit_links($content) {
    return preg_replace_callback("@https://([a-zA-Z0-9]+\.)?(powernukkit\.org|gamemods\.com\.br|github\.com)/[a-zA-Z0-9/._-]+@", function ($matches) {
        return "<a href='$matches[0]'>$matches[0]</a>";
    }, $content);
}

function add_to_queue($url) {
    import_haste($url);
}

function uniqid_real($lenght = 13) {
    // uniqid gives 13 chars, but you could adjust it to your needs.
    if (function_exists("random_bytes")) {
        $bytes = random_bytes(ceil($lenght / 2));
    } elseif (function_exists("openssl_random_pseudo_bytes")) {
        $bytes = openssl_random_pseudo_bytes(ceil($lenght / 2));
    } else {
        throw new Exception("no cryptographically secure random function available");
    }
    return substr(bin2hex($bytes), 0, $lenght);
}
