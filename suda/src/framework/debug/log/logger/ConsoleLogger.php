<?php

namespace suda\framework\debug\log\logger;


use Psr\Log\LogLevel;
use Psr\Log\AbstractLogger;
use suda\framework\debug\Debug;

/**
 * 控制台日志输出
 */
class ConsoleLogger extends AbstractLogger
{
    /**
     * @var string
     */
    protected $level;

    public function __construct(string $level)
    {
        $this->level = $level;
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = [])
    {
        if (Debug::compare($level, $this->level) >= 0) {
            print date('Y-m-d H:i:s') . ' ' . $this->interpolate($message, $context) . PHP_EOL;
        }
    }

    /**
     * @param string $message
     * @param array $context
     * @return string
     */
    public function interpolate(string $message, array $context)
    {
        $replace = [];
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }
        return strtr($message, $replace);
    }
}
