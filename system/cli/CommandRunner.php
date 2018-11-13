<?php
namespace suda\cli;

use suda\core\Autoloader;

class CommandRunner
{
    protected static $commands = [
        'pack:suda' => [ 'command' => command\PackSudaCommand::class , 'message' => 'pack suda as phar package' ],
        'pack' => [ 'command' => command\PackCommand::class , 'message' => 'pack app as phar package' ],
        
        'release' => [ 'command' => command\ReleaseCommand::class , 'message' => 'release suda version' ],
        'new' => [ 'command' => command\NewCommand::class , 'message' => 'create a suda application' ],
        'replace' => [ 'command' => command\ReplaceCommand::class , 'message' => 'use inner suda replace exist suda' ],
    ];
    
    public static function run(int $argc, array $argv)
    {
        $phar = Autoloader::realPath($argv[0]);
        if (preg_match('/\.phar$/', $phar)) {
            define('SUDA_PHAR', Autoloader::realPath($argv[0]));
        }

        if ($argc <= 1) {
            self::printBanner();
            print '  Usage: suda-cli command [args]'.PHP_EOL;
            print '    command:'.PHP_EOL;
            foreach (static::$commands as $name => $command) {
                print "      - {$name}\t{$command['message']}".PHP_EOL;
            }
            print PHP_EOL;
        } else {
            if (array_key_exists($argv[1], static::$commands)) {
                $command = static::$commands[$argv[1]]['command'];
                array_shift($argv);
                $command::exec($argv);
            } else {
                print "command {$argv[1]} is unsupport".PHP_EOL;
            }
        }
    }

    protected static function printBanner()
    {
        $version = SUDA_VERSION;
        $banner=<<<BANNER
     ______     __  __     _____     ______    
    /\  ___\   /\ \/\ \   /\  __-.  /\  __ \   
    \ \___  \  \ \ \_\ \  \ \ \/\ \ \ \  __ \  
     \/\_____\  \ \_____\  \ \____-  \ \_\ \_\ 
      \/_____/   \/_____/   \/____/   \/_/\/_/

                                    suda: {$version}
                                    author: dxkite
BANNER;
        print PHP_EOL.PHP_EOL.$banner.PHP_EOL.PHP_EOL;
    }
}
