<?php
require_once __DIR__.'/suda-console.php';

suda\core\Autoloader::addIncludePath(__DIR__.'/cli', 'suda\\cli');
suda\cli\CommandRunner::run($argc, $argv);
