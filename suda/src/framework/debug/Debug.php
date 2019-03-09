<?php
namespace suda\framework\debug;

use suda\framework\debug\Caller;
use suda\framework\debug\ConfigTrait;
use suda\framework\debug\log\LogLevel;
use suda\framework\debug\ConfigInterface;
use suda\framework\debug\log\LoggerTrait;
use suda\framework\debug\attach\DumpTrait;
use suda\framework\debug\attach\AttachTrait;
use suda\framework\debug\log\LoggerInterface;
use suda\framework\debug\attach\DumpInterface;
use suda\framework\debug\log\LoggerAwareTrait;
use suda\framework\debug\log\logger\NullLogger;
use suda\framework\debug\attach\AttachInterface;
use suda\framework\debug\log\LoggerAwareInterface;

class Debug implements LoggerInterface, LoggerAwareInterface, DumpInterface, AttachInterface, ConfigInterface
{
    use LoggerTrait,LoggerAwareTrait,DumpTrait,AttachTrait,ConfigTrait;

    /**
     * 时间记录
     *
     * @var array
     */
    protected $timeRecord;

    public function log(string $level, string $message, array $context = [])
    {
        $attribute = $this->getAttribute();
        $attribute['message'] = $this->strtr($message, $context);
        $attribute['level'] = $level;
        $caller = new Caller(debug_backtrace(), $this->getIgnoreTraces());
        $trace = $caller->getCallerTrace();
        $attribute['file'] = $trace['file'];
        $attribute['line'] = $trace['line'];
        $attribute = $this->assignAttributes($attribute);
        $this->logger->log($level, $this->interpolate($this->getConfig('log-format'), $context, $attribute), []);
    }

    /**
     * 设置忽略前缀
     *
     * @return array
     */
    public function getIgnoreTraces():array {
        return [__DIR__];
    }

    public function getDefaultConfig():array
    {
        return [
            'log-format' => '%time-format% - %memory-format% [%level%] %file%:%line% %message%',
            'start-time' => 0,
            'start-memory' => 0,
        ];
    }

    protected function strtr(string $message, array $context)
    {
        $replace = [];
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }
        return strtr($message, $replace);
    }

    protected function assignAttributes(array $attribute):array
    {
        $attribute['current-time'] = number_format(microtime(true), 4, '.', '');
        $time = microtime(true) - $this->getConfig('start-time');
        $memory = memory_get_usage() - $this->getConfig('start-memory');
        $attribute['time-format'] = number_format($time, 10, '.', '');
        $attribute['memory-format'] = $this->formatBytes($memory, 2);
        $attribute['memory'] = $memory;
        return $attribute;
    }

    protected static function formatBytes(int $bytes, int $precision=0)
    {
        $human= ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes?log($bytes):0)/log(1024));
        $pos = min($pow, count($human)-1);
        $bytes /= (1 << (10* $pos));
        return round($bytes, $precision).' '.$human[$pos];
    }

    public function time(string $name, string $type= LogLevel::INFO)
    {
        $this->timeRecord[$name]=['time'=>microtime(true),'level'=>$type];
    }

    public function timeEnd(string $name)
    {
        if (\array_key_exists($name, $this->timeRecord)) {
            $pass=microtime(true)-$this->timeRecord[$name]['time'];
            $this->log($this->timeRecord[$name]['level'], 'process cost '. $name.' '. number_format($pass, 5).'s');
            return $pass;
        }
        return 0;
    }
}
