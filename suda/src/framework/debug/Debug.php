<?php

namespace suda\framework\debug;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use Psr\Log\LogLevel;
use suda\framework\debug\attach\DumpTrait;
use suda\framework\debug\attach\AttachTrait;
use suda\framework\debug\attach\DumpInterface;
use suda\framework\debug\attach\AttachInterface;

class Debug implements LoggerInterface, LoggerAwareInterface, DumpInterface, AttachInterface, ConfigInterface
{
    use LoggerTrait, LoggerAwareTrait, DumpTrait, AttachTrait, ConfigTrait;

    protected static $levels = ['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'];

    /**
     * 忽略堆栈
     *
     * @var array
     */
    protected $ignoreTraces = [__DIR__];

    /**
     * 时间记录
     *
     * @var array
     */
    protected $timeRecord;

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = [])
    {
        $attribute = $this->getAttribute();
        list($attach, $replace) = $this->analyse($message, $context);
        $attribute = array_merge($attribute, $attach);
        $attribute['message'] = strtr($message, $replace);
        $attribute['level'] = $level;
        $caller = new Caller(debug_backtrace(), $this->getIgnoreTraces());
        $trace = $caller->getCallerTrace();
        $attribute['file'] = $trace['file'];
        $attribute['line'] = $trace['line'];
        $attribute = $this->assignAttributes($attribute);
        $this->logger->log($level, $this->interpolate($this->getConfig('log-format'), $attach, $attribute), []);
    }

    public function analyse(string $message, array $context)
    {
        $replace = [];
        $attach = [];
        foreach ($context as $key => $val) {
            $replaceKey = '{' . $key . '}';
            if ($this->canBeStringValue($val) && strpos($message, $replaceKey) !== false) {
                $replace['{' . $key . '}'] = $val;
            } else {
                $attach[$key] = $val;
            }
        }
        return [$attach, $replace];
    }

    /**
     * 设置忽略前缀
     *
     * @return array
     */
    public function getIgnoreTraces(): array
    {
        return $this->ignoreTraces;
    }

    public function getDefaultConfig(): array
    {
        return [
            'log-format' => '%time-format% - %memory-format% [%level%] %file%:%line% %message%',
            'start-time' => 0,
            'start-memory' => 0,
        ];
    }

    protected function canBeStringValue($val): bool
    {
        return !is_array($val) && (!is_object($val) || method_exists($val, '__toString'));
    }

    protected function assignAttributes(array $attribute): array
    {
        $attribute['current-time'] = number_format(microtime(true), 4, '.', '');
        $time = microtime(true) - $this->getConfig('start-time');
        $memory = memory_get_usage() - $this->getConfig('start-memory');
        $attribute['time-format'] = number_format($time, 10, '.', '');
        $attribute['memory-format'] = $this->formatBytes($memory, 2);
        $attribute['memory'] = $memory;
        return $attribute;
    }

    public static function formatBytes(int $bytes, int $precision = 0)
    {
        $human = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pos = min($pow, count($human) - 1);
        $bytes /= (1 << (10 * $pos));
        return round($bytes, $precision) . ' ' . $human[$pos];
    }

    public function time(string $name, string $type = LogLevel::INFO)
    {
        $this->timeRecord[$name] = ['time' => microtime(true), 'level' => $type];
    }

    public function timeEnd(string $name)
    {
        if (array_key_exists($name, $this->timeRecord)) {
            $pass = microtime(true) - $this->timeRecord[$name]['time'];
            $this->log(
                $this->timeRecord[$name]['level'],
                sprintf("process %s cost %ss", $name, number_format($pass, 5))
            );
            return $pass;
        }
        return 0;
    }

    /**
     * @param array $ignoreTraces
     * @return $this
     */
    public function setIgnoreTraces(array $ignoreTraces)
    {
        $this->ignoreTraces = $ignoreTraces;
        return $this;
    }


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

    /**
     * 添加忽略路径
     * @param string $path
     * @return $this
     */
    public function addIgnorePath(string $path)
    {
        $this->ignoreTraces[] = $path;
        return $this;
    }
}
