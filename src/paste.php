<?php
require_once 'import.inc.php';

if ($_SERVER['REQUEST_METHOD'] != 'PUT') {
    header("HTTP/1.1 405 Method Not Allowed");
    header("Allow: PUT");
    exit;
}

if (@$_SERVER['CONTENT_TYPE'] != "application/zip") {
    header("HTTP/1.1 415 Unsupported Media Type");
    exit;
}

$len = @$_SERVER['CONTENT_LENGTH'];
if (!$len || $len <= 0) {
    header("HTTP/1.1 411 Length Required");
    exit;
}

if ($len > 15*1024*1024) {
    header("HTTP/1.1 413 Payload Too Large");
    exit;
}

$tmp_dir = sys_get_temp_dir();
$disk_free_space = disk_free_space($tmp_dir);
if (!$disk_free_space || $disk_free_space < $len * 40) {
    header("HTTP/1.1 507 Insufficient Storage");
    exit(1);
}

$input = @fopen("php://input", "rb");
if (!$input) {
    header("HTTP/1.1 400 Bad Request");
    exit;
}

$tmp_dir = tempnam($tmp_dir, "debugpaste_upload");
if (!$tmp_dir) {
    header("HTTP/1.1 500 Internal Server Error");
    error_log("Failed to create a temporary file");
    exit(5);
}

if(!unlink($tmp_dir) || !mkdir($tmp_dir)) {
    header("HTTP/1.1 500 Internal Server Error");
    error_log("Convert the temp file into a folder: $tmp_dir");
    exit(6);
}

$extracted = "$tmp_dir/extracted";
if (!mkdir($extracted)) {
    header("HTTP/1.1 500 Internal Server Error");
    error_log("Failed to create dir $extracted");
    exit(7);
}

register_shutdown_function(function () {
    global $tmp_dir;
    if (is_dir($tmp_dir) || is_file($tmp_dir)) {
        del_tree($tmp_dir);
    }
});

$output = fopen("$tmp_dir/upload.zip", "wb");
if (!$output) {
    header("HTTP/1.1 500 Internal Server Error");
    error_log("Failed to open the file for writing: $tmp_dir/upload.zip");
    abort();
    exit(2);
}

$total = 0;
while ($data = fread($input,1024)) {
    $wrote = fwrite($output, $data);
    if (!$wrote || $wrote < 0) {
        header("HTTP/1.1 500 Internal Server Error");
        error_log("Failed to write all bytes. Wrote $total or $len");
        abort();
        exit(3);
    }
    
    $total += $wrote;
    if ($total > $len) {
        header("HTTP/1.1 413 Payload Too Large");
        abort();
        exit(4);
    }
}

@fclose($output);
@fclose($input);

$code = "PN".random_token();
$attempts = 0;
while (is_dir("pastes/$code")) {
    if (++$attempts > 1000) {
        header("HTTP/1.1 500 Internal Server Error");
        error_log("Failed to create a random code");
        abort();
        exit(8);
    }
    $code = "PN".random_token();
}

if(!mkdir("pastes/$code")) {
    header("HTTP/1.1 500 Internal Server Error");
    error_log("Failed to the folder pastes/$code");
    abort();
    exit(9);
}

$gz_files = array(
    'server-second-most-latest.log.gz'
);

$direct_files = array(
    'nukkit.yml',
    'server.properties',
    'server-info.txt',
    'server-latest.log',
    'status.txt',
    'thread-dump.txt',
);

if(!file_put_contents("$extracted/.htaccess", "Options +Indexes\n")) {
    error_log("Failed to create $extracted/.htaccess");
}

foreach ($gz_files as $gz_file) {
    $full_path = "$tmp_dir/$gz_file";
    if (is_file($full_path)) {
        if (!gz_inflate($full_path)) {
            error_log("Failed inflate $gz_file.");
        } else {
            $direct_files[] = substr($gz_file, 0, -3);
        }
    }
}

foreach ($direct_files as $direct_file) {
    $full_path = "$tmp_dir/$direct_file";
    if (is_file($full_path)) {
        $contents = file_get_contents($full_path);
        if(!file_put_contents("$extracted/$direct_file.html", make_html($contents, "$code/$direct_file"))) {
            error_log("Failed to write to $extracted/$direct_file.html");
        }
    }
}


recurse_copy($extracted, "pastes/$code");
del_tree($tmp_dir);

header("HTTP/1.1 201 Created");
echo "https://debugpaste.powernukkit.org/pastes/$code";

function abort() {
    global $input, $output, $tmp_dir;
    
    @fclose($input);
    @fclose($output);
    @unlink($output);
    
    del_tree($tmp_dir);
}

/**
 * @param $from_file string Gz file path
 * @param string|null $to_file string Path to the output file
 * @return bool If the operation was successful
 * @throws Exception If from_file or to_file are invalid
 */
function gz_inflate(string $from_file, string $to_file = null) {
    if (!$to_file) {
        if (substr_compare(mb_strtolower($from_file), ".gz", -3) === 0) {
            $to_file = substr($from_file, 0, -3);
        }

    }
    if (!$from_file || !$to_file || mb_strtolower($to_file) == mb_strtolower($from_file)) {
        throw new Exception("Missing input or output file. Input: $from_file, Output: $to_file");
    }
    
    if (!is_file($from_file)) {
        throw new Exception("The file $from_file does not exists");
    }

    $buffer_size = 4096;
    $input = gzopen($from_file, 'rb');
    $output = fopen($to_file, 'wb');
    try {
        if (!$input || !$output) {
            error_log("Failed to decompress $from_file, could not open input($input) or output($output).");
            return false;
        }

        while(!gzeof($input)) {
            if (!fwrite($output, gzread($input, $buffer_size))) {
                error_log("Failed to decompress $from_file. fwrite failed.");
                @fclose($output);
                unlink($output);
                return false;
            }
        }
        
        return true;
    } finally {
        @fclose($output);
        @gzclose($input);
    }
}

function del_tree($dir) {
    $files = array_diff(scandir($dir), array('.','..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? del_tree("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

function recurse_copy($src,$dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                recurse_copy($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}
