<?php
namespace suda\framework\debug\log\logger;

use Psr\Log\AbstractLogger;

class NullLogger extends AbstractLogger
{
    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = [])
    {
        // noop
    }
}
