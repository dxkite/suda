<?php
require_once __DIR__ .'/../../system/suda-console.php';

use suda\core\Storage;

$path = SYSTEM_DIR;
$output = $argv[1];

$files=Storage::readDirFiles($path, true, '/\.php$/');
file_put_contents($output,'# source since version '.SUDA_VERSION.PHP_EOL);
foreach ($files as $file) {
    $content = file_get_contents($file);
    print 'read file ' .$file ."\r\n";
    $name =storage()->cut($file,$path);
    file_put_contents($output,'#'.$name.PHP_EOL.$content.PHP_EOL,FILE_APPEND);
}
print 'success' ."\r\n";