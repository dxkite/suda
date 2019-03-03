<?php
namespace suda\framework\debug\log;

/**
 * 日志等级
 */
class LogLevel
{
    const EMERGENCY = 'emergency';
    const ALERT     = 'alert';
    const CRITICAL  = 'critical';
    const ERROR     = 'error';
    const WARNING   = 'warning';
    const NOTICE    = 'notice';
    const INFO      = 'info';
    const DEBUG     = 'debug';

    protected static $levels = ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'];
    
    /**
     * 比较优先级
     *
     * @param string $a
     * @param string $b
     * @return integer
     */
    public static function compare(string $a, string $b): int
    {
        $indexA = array_search($a, static::$levels);
        $indexB = array_search($b, static::$levels);
        return $indexA - $indexB;
    }
}
