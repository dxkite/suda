<?php
namespace suda\framework\debug\log;

/**
 * 抽象日志记录工具
 */
trait LoggerTrait
{
    /**
     * @param string $message
     * @param array $context
     */
    public function emergency(string $message, array $context = [])
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function alert(string $message, array $context = [])
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function critical(string $message, array $context = [])
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function error(string $message, array $context = [])
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function warning(string $message, array $context = [])
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function notice(string $message, array $context = [])
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function info(string $message, array $context = [])
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function debug(string $message, array $context = [])
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * @param string $level
     * @param string $message
     * @param array $context
     * @return mixed
     */
    abstract public function log(string $level, string $message, array $context = []);
}
