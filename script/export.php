<?php

require_once __DIR__ .'/../system/suda-console.php';

use suda\core\Storage;

$path = $argv[1];
$output = $argv[2];

$files=Storage::readDirFiles($path, true, '/\.php$/');
foreach ($files as $file) {
    $content = file_get_contents($file);
    print 'read file ' .$file ."\r\n";
    $name =storage()->cut($file,$path);
    file_put_contents($output,'#'.$name."\r\n".$content,FILE_APPEND);
}
print 'success' ."\r\n";