<?php
namespace suda\framework\debug\log;

/**
 * Interface LoggerInterface
 * @package suda\framework\debug\log
 */
interface LoggerInterface
{
    /**
     * @param string $message
     * @param array $context
     * @return mixed
     */
    public function emergency(string $message, array $context = []);

    /**
     * @param string $message
     * @param array $context
     * @return mixed
     */
    public function alert(string $message, array $context = []);

    /**
     * @param string $message
     * @param array $context
     * @return mixed
     */
    public function critical(string $message, array $context = []);

    /**
     * @param string $message
     * @param array $context
     * @return mixed
     */
    public function error(string $message, array $context = []);

    /**
     * @param string $message
     * @param array $context
     * @return mixed
     */
    public function warning(string $message, array $context = []);

    /**
     * @param string $message
     * @param array $context
     * @return mixed
     */
    public function notice(string $message, array $context = []);

    /**
     * @param string $message
     * @param array $context
     * @return mixed
     */
    public function info(string $message, array $context = []);

    /**
     * @param string $message
     * @param array $context
     * @return mixed
     */
    public function debug(string $message, array $context = []);

    /**
     * @param string $level
     * @param string $message
     * @param array $context
     * @return mixed
     */
    public function log(string $level, string $message, array $context = []);
}
