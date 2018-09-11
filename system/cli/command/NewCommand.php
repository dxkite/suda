<?php
namespace suda\cli\command;

use suda\cli\Command;
use Phar;
use suda\core\System;

class NewCommand extends Command
{
    public static function exec(array $argv)
    {
        $path = storage()->path($argv[1] ?? 'suda');
        $phar = new Phar(SUDA_PHAR);
        $extract = $path.'/suda/system';
        if (storage()->exist($extract.'/suda.php')) {
            print 'app exists in: ' .$path.PHP_EOL;
            return 0;
        }
        if ($phar->extractTo($extract)) {
            System::createApplication(storage()->path($path.'/app'));
            storage()->copydir(SYSTEM_RESOURCE.'/project/public', storage()->path($path.'/public'));
            print 'create app to ' . $path .PHP_EOL;
        } else {
            print 'failed create app to ' . $path .PHP_EOL;
        }
    }
}
