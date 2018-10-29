<?php
namespace suda\cli\command;

use suda\cli\Command;
use Phar;

class PackCommand extends Command
{
    public static function exec(array $argv)
    {
        $isReadOnly = ini_get('phar.readonly');
        if ($isReadOnly) {
            echo 'please set phar.readonly to Off';
        } else {
            $path = $argv[1] ?? './app';
            $base = TEMP_DIR.'/pack';
            $to = $argv[2] ?? basename($path);
            $name = $to.'.phar';
            $pharPath = $name;
            storage()->copydir(SYSTEM_DIR.'/', storage()->path($base.'/suda/system'));
            storage()->copydir(SYSTEM_RESOURCE. '/project/public', storage()->path($base.'/public'));
            storage()->copydir($path, storage()->path($base.'/app'));
            $phar = new Phar($pharPath, 0, $name);
            $phar->buildFromDirectory($base);
            $phar->setStub(Phar::createDefaultStub('suda/system/suda-phar.php', 'public/index.php'));
            $phar->compressFiles(Phar::GZ);
        }
    }
}
