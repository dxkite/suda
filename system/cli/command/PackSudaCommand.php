<?php
namespace suda\cli\command;

use suda\cli\Command;
use Phar;

class PackSudaCommand extends Command
{
    public static function exec(array $argv)
    {
        $path = $argv[1] ?? './suda-cli.phar';
        $phar = new Phar($path,0,'suda.phar');
        $phar->buildFromDirectory(SYSTEM_DIR);
        $phar->setStub(Phar::createDefaultStub('suda-cli.php','suda.php'));
        $phar->compressFiles(Phar::GZ);
    }
}
