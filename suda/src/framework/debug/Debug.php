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

    /**
     * @var array
     */
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
     * @var array
     */
    protected $timing;

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = [])
    {
        $attribute = $this->getAttribute();

        $caller = $this->getCaller($context);

        [$attach, $replace] = $this->analyse($message, $context);
        $attribute            = array_merge($attribute, $attach);
        $attribute['message'] = strtr($message, $replace);
        $attribute['level']   = $level;
        $attribute['file']    = $caller['file'];
        $attribute['line']    = $caller['line'];

        $attribute = $this->assignAttributes($attribute);
        $this->logger->log($level, $this->interpolate($this->getConfig('log-format'), $attach, $attribute), []);
    }

    private function getCaller(array $context)
    {
        if (array_key_exists('exception', $context) && $context['exception'] instanceof \Throwable) {
            $backtrace = $context['exception']->getTrace();
        } else {
            $backtrace = debug_backtrace();
        }
        $caller = new Caller($backtrace, $this->getIgnoreTraces());
        return $caller->getCallerTrace();
    }

    /**
     * @param string $message
     * @param array $context
     * @return array
     */
    public function analyse(string $message, array $context)
    {
        $replace = [];
        $attach  = [];
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

    /**
     * @return array
     */
    public function getDefaultConfig(): array
    {
        return [
            'log-format'   => '%time-format% - %memory-format% [%level%] %file%:%line% %message%',
            'start-time'   => 0,
            'start-memory' => 0,
        ];
    }

    /**
     * @param $val
     * @return bool
     */
    protected function canBeStringValue($val): bool
    {
        return !is_array($val) && (!is_object($val) || method_exists($val, '__toString'));
    }

    /**
     * @param array $attribute
     * @return array
     */
    protected function assignAttributes(array $attribute): array
    {
        $attribute['current-time']  = number_format(microtime(true), 4, '.', '');
        $time                       = microtime(true) - $this->getConfig('start-time');
        $memory                     = memory_get_usage() - $this->getConfig('start-memory');
        $attribute['time-format']   = number_format($time, 10, '.', '');
        $attribute['memory-format'] = $this->formatBytes($memory, 2);
        $attribute['memory']        = $memory;
        return $attribute;
    }

    /**
     * @param int $bytes
     * @param int $precision
     * @return string
     */
    public static function formatBytes(int $bytes, int $precision = 0)
    {
        $human = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pos   = min($pow, count($human) - 1);
        $bytes /= (1 << (10 * $pos));
        return round($bytes, $precision) . ' ' . $human[$pos];
    }

    /**
     * @param string $name
     * @param string $type
     */
    public function time(string $name, string $type = LogLevel::INFO)
    {
        $this->timeRecord[$name] = ['time' => microtime(true), 'level' => $type];
    }

    /**
     * @param string $name
     * @return float
     */
    public function timeEnd(string $name)
    {
        if (array_key_exists($name, $this->timeRecord)) {
            $pass = microtime(true) - $this->timeRecord[$name]['time'];
            $this->log(
                $this->timeRecord[$name]['level'],
                sprintf("process %s cost %ss", $name, number_format($pass, 5, '.', ''))
            );
            unset($this->timeRecord[$name]);
            return $pass;
        }
        return 0;
    }

    /**
     * @param string $name
     * @param float $time
     * @param string $description
     */
    public function recordTiming(string $name, float $time, string $description = '')
    {
        if (array_key_exists($name, $this->timing)) {
            $this->timing[$name]['time'] += $time;
        } else {
            $this->timing[$name]['time'] = $time;
        }
        $this->timing[$name]['description'] = $description;
    }

    /**
     * @return array
     */
    public function getTiming(): array
    {
        return $this->timing;
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
