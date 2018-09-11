<?php
namespace suda\cli;

class CommandRunner
{
    protected static $commands = [
        'pack-suda' => [ 'command' => command\PackSudaCommand::class , 'message' => 'pack suda as phar package' ]
    ];
    
    public static function run(int $argc, array $argv)
    {
        if ($argc <= 1) {
            print 'Usage: suda-cli command [args]'.PHP_EOL;
            print '  command:'.PHP_EOL;
            foreach (static::$commands as $name => $command) {
                print "   - {$name}\t{$command['message']}".PHP_EOL;
            }
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
}
