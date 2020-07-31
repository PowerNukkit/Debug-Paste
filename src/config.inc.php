<?php
define('PASTES_DIR', __DIR__.'/pastes');
if (!is_dir(PASTES_DIR) && !mkdir(PASTES_DIR)) {
    die("Failed to create the pastes dir!");
}

function create_jwt() {
    if (!defined('APP_ID') && !defined('APP_KEY')) {
        http_response_code(500);
        die('App config issue');
    }

    //Header Token
    $header = [
        'typ' => 'JWT',
        'alg' => 'HS256'
    ];

    //Payload - Content
    $issued_at_time = (new DateTime("now"))->getTimestamp();
    $payload = [
        'iat' => $issued_at_time,
        'exp' => $issued_at_time + 20,
        'iss' => APP_ID
    ];

    //JSON
    $header = json_encode($header);
    $payload = json_encode($payload);

    //Base 64
    $header = base64_encode($header);
    $payload = base64_encode($payload);

    //Sign
    $key = openssl_get_privatekey(APP_KEY);
    if ($key == false) {
        http_response_code(500);
        die('Failed to load the key. '.openssl_error_string());
    }

    if(!openssl_private_encrypt($header . "." . $payload, $sign, $key)){
        http_response_code(500);
        die('Failed to use the key '.openssl_error_string());
    }

    $sign = base64_encode($sign);

    //Token
    return $header . '.' . $payload . '.' . $sign;
}

function create_installation_token($installation) {
    $installation = rawurlencode($installation);
    $curl = curl_init();
    $opts = array (
        CURLOPT_URL => "https://api.github.com/app/installations/$installation/access_tokens",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_USERAGENT => 'PowerNukkit',
        CURLOPT_HTTPHEADER => array(
            'Accept: application/vnd.github.v3+json',
            'Authorization: Bearer ' . create_jwt()
        )
    );
    curl_setopt_array($curl, $opts);
    $result = curl_exec($curl);
    curl_close($curl);
    $json = json_decode($result);
    if (!$json) {
        http_response_code(500);
        file_put_contents(__DIR__.'/../last_error.txt', print_r($opts, true)."\n\n$result");
        die("Could not parse the result for installation $installation:\n\n$result");

    }
    return "token ".$json['token'];
}
