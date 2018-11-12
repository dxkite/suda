<?php
namespace suda\cli\command;

use suda\cli\Command;
use Phar;
use suda\core\System;

class ReplaceCommand extends Command
{
    public static function exec(array $argv)
    {
        if (count($argv) < 2) {
            print 'mission suda path'.PHP_EOL;
            return;
        }
        $path = storage()->path($argv[1]);
        $search = [
            'suda.php',
            'system/suda.php',
            'suda/system/suda.php',
        ];
        $index = null;

        foreach ($search as $searchPath) {
            if (storage()->exist($path.'/'.$searchPath)) {
                $index = storage()->abspath($path.'/'.$searchPath);
                break;
            }
        }

        if (is_null($index)) {
            print 'suda system no find in '. $path.PHP_EOL;
            return;
        }
        $replace = dirname($index);
        storage()->delete($replace);
        storage()->copydir(SYSTEM_DIR.'/', $replace);
        print 'replace suda to ' .$replace .PHP_EOL;
    }
}
