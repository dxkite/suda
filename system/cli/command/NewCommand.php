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
        storage()->copydir(SYSTEM_DIR.'/', storage()->path($path.'/suda/system'));
        System::createApplication(storage()->path($path.'/app'));
        storage()->copydir(SYSTEM_RESOURCE.'/project/public', storage()->path($path.'/public'));
        storage()->mkdir($path.'/public/assets', 0777);
        storage()->mkdir($path.'/app/data', 0777);
        print 'create app to ' . $path .PHP_EOL;
    }
}
