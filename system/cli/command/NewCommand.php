<?php
namespace suda\cli\command;

use suda\cli\Command;
use Phar;

class NewCommand extends Command
{
    public static function exec(array $argv)
    {
        $phar = new Phar('./suda-cli.phar',0,'suda.phar');
        $phar->buildFromDirectory(SYSTEM_DIR);
        $phar->setStub(Phar::createDefaultStub('suda-cli.php','suda.php'));
        $phar->compressFiles(Phar::GZ);
    }
}