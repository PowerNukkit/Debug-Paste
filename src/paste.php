<?php
header("HTTP/1.1 201 Created");
echo "https://debugpaste.powernukkit.org/testing\n";
echo "POST: \n";
print_r($_POST);

echo "\nFILES:\n";
print_r($_FILES);

