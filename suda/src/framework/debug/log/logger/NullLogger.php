<?php
namespace suda\framework\debug\log\logger;

use suda\framework\debug\log\AbstractLogger;

class NullLogger extends AbstractLogger
{
    public function log($level, string $message, array $context = [])
    {
        // noop
    }
}
