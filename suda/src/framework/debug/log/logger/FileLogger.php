<?php

namespace suda\framework\debug\log\logger;

use suda\framework\debug\log\LogLevel;
use suda\framework\filesystem\FileSystem;
use suda\framework\debug\log\logger\exception\FileLoggerException;

/**
 * Class FileLogger
 * @package suda\framework\debug\log\logger
 */
class FileLogger extends FileLoggerBase
{

    /**
     * 构建文件日志
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->set($config);
        register_shutdown_function([$this, 'shutdown']);
    }

    /**
     * 设置配置
     * @param array $config
     */
    public function set(array $config)
    {
        $this->applyConfig($config);
        FileSystem::make($this->getConfig('save-path'));
        FileSystem::make($this->getConfig('save-dump-path'));
        FileSystem::make($this->getConfig('save-zip-path'));
    }

    /**
     * @param string $level
     * @param string $message
     * @param array $context
     * @return mixed|void
     * @throws FileLoggerException
     */
    public function log($level, string $message, array $context = [])
    {
        if (LogLevel::compare($level, $this->getConfig('log-level')) >= 0) {
            $replace = [];
            $message = $this->interpolate($message, $context);
            $replace['%level%'] = $level;
            $replace['%message%'] = $message;
            $write = strtr($this->getConfig('log-format'), $replace);
            fwrite($this->getAvailableWrite(), $write . PHP_EOL);
        }
    }


    /**
     * 即时写入日志
     */
    public function write()
    {
        if ($this->checkSize()) {
            $this->packLogFile();
            $this->removePackFiles();
        }
        $this->rollLatest();
    }

    /**
     * 程序关闭时调用
     */
    public function shutdown()
    {
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        $this->write();
    }

    /**
     * @param string $message
     * @param array $context
     * @return string
     */
    public function interpolate(string $message, array $context)
    {
        $replace = [];
        foreach ($context as $key => $val) {
            if (is_bool($val)) {
                $val = $val ? 'true' : 'false';
            } elseif (null === $val) {
                $val = 'null';
            }
            $replace['{' . $key . '}'] = $val;
        }
        return strtr($message, $replace);
    }
}
