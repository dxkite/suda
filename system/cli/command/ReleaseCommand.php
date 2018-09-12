<?php
namespace suda\cli\command;

use suda\cli\Command;
use Phar;

class ReleaseCommand extends Command
{
    public static function exec(array $argv)
    {
        $isReadOnly = ini_get('phar.readonly');
        if ($isReadOnly) {
            echo 'please set phar.readonly to Off';
        } else {
            $path = './suda-cli_v'.SUDA_VERSION.'.phar';
            $phar = new Phar($path, 0, 'suda.phar');
            $phar->buildFromDirectory(SYSTEM_DIR);
            $phar->setStub(Phar::createDefaultStub('suda-phar.php'));
            $phar->compressFiles(Phar::GZ);
        }
    }
}
