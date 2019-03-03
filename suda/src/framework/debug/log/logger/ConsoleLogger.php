<?php
namespace suda\framework\debug\log\logger;

use suda\framework\debug\log\AbstractLogger;

/**
 * 控制台日志输出
 */
class ConsoleLogger extends AbstractLogger
{
    public function log($level, string $message, array $context = [])
    {
        print date('Y-m-d H:i:s') .' ' . $this->interpolate($message, $context) . PHP_EOL;
    }
    public function interpolate(string $message, array $context)
    {
        $replace = [];
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }
        return strtr($message, $replace);
    }
}
