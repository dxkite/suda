<?php
namespace suda\framework;

use suda\framework\debug\Debug;



/**
 * 调试器
 */
class Debugger extends Debug
{
    public function getDefaultConfig():array
    {
        return [
            'log-format' => '%time-format% - %memory-format% [%level%] %file%:%line% %message%',
            'start-time' => 0,
            'start-memory' => 0,
        ];
    }
} 
