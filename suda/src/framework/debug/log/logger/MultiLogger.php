<?php


namespace suda\framework\debug\log\logger;


use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

class MultiLogger extends AbstractLogger
{
    /**
     * @var LoggerInterface[]
     */
    protected $loggers;

    public function __construct() {
        foreach (func_get_args() as $logger) {
            $this->loggers[] = $logger;
        }
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = [])
    {
        foreach ($this->loggers as $logger) {
            $logger->log($level, $message, $context);
        }
    }
}