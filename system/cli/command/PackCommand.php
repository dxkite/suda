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
            storage()->copydir(SYSTEM_DIR.'/', storage()->path($base.'/system'));
            storage()->copydir(SYSTEM_RESOURCE. '/phar', storage()->path($base));
            storage()->copydir($path, storage()->path($base.'/app'));
            // 删除运行时
            storage()->delete($base.'/app/data/cache');
            storage()->delete($base.'/app/data/install');
            storage()->delete($base.'/app/data/logs');
            storage()->delete($base.'/app/data/temp');
            storage()->delete($base.'/app/data/views');
            storage()->delete($base.'/app/data/runtime');
            $phar = new Phar($pharPath, 0, $name);
            $phar->buildFromDirectory($base);
            $phar->setStub(Phar::createDefaultStub('system/suda-phar.php', 'index.php'));
            $phar->compressFiles(Phar::GZ);
            storage()->delete($base);
        }
    }
}
