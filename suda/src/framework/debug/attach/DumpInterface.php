<?php
namespace suda\framework\debug\attach;

/**
 * 多行附加属性
 */
interface DumpInterface 
{
    public static function parameterToString($object, int $deep=2);
    public static function dumpThrowable(\Throwable $e);
    public static function dumpTrace(array $backtrace, bool $str=true, string $perfix='');
}
