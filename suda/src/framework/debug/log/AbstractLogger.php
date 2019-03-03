<?php
namespace suda\framework\debug\log;

/**
 * 抽象日志记录工具
 */
abstract class AbstractLogger implements LoggerInterface
{
    public function emergency(string $message, array $context = [])
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }
   
    public function alert(string $message, array $context = [])
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }
    
    public function critical(string $message, array $context = [])
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }
   
    public function error(string $message, array $context = [])
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning(string $message, array $context = [])
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }
    
    public function notice(string $message, array $context = [])
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }
    
    public function info(string $message, array $context = [])
    {
        $this->log(LogLevel::INFO, $message, $context);
    }
    
    public function debug(string $message, array $context = [])
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }
}
