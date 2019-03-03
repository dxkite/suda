<?php
namespace suda\framework\debug\log;

interface LoggerAwareInterface
{
    /**
     * 设置日志记录工具
     *
     * @param LoggerInterface $logger
     *
     * @return void
     */
    public function setLogger(LoggerInterface $logger);
}
