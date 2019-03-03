<?php
namespace suda\framework;

use suda\framework\Context;
use suda\framework\Debugger;
use suda\framework\debug\Debug;
use suda\framework\runnable\Runnable;
use suda\framework\filesystem\FileSystem;
use suda\framework\debug\log\LoggerInterface;
use suda\framework\debug\log\logger\FileLogger;
use suda\framework\debug\log\logger\NullLogger;

/**
 * 调试器
 */
class Debugger extends Debug
{
    /**
     * 创建调式工具
     *
     * @param Context $context
     * @return Debugger
     */
    public static function create(Context $context): Debugger
    {
        $debugger = new Debugger;
        $debugger->addAttribute('remote-ip', $context->get('request')->getRemoteAddr());
        $debugger->addAttribute('debug', $context->get('config')->get('debug', false));
        $debugger->addAttribute('request-uri', $context->get('request')->getUrl());
        $debugger->addAttribute('request-method', $context->get('request')->getMethod());
        $debugger->addAttribute('request-time', date('Y-m-d H:i:s', \constant('SUDA_START_TIME')));
        $debugger->applyConfig([
            'start-time' => \constant('SUDA_START_TIME'),
            'start-memory' => \constant('SUDA_START_MEMORY'),
        ]);
        $debugger->setLogger(static::createLogger($context));
        $debugger->logger->notice(PHP_EOL.'{request-time} {remote-ip} {request-method} {request-uri} debug={debug}', $debugger->getAttribute());
        return $debugger;
    }

    /**
     * 创建日志记录器
     *
     * @return LoggerInterface
     */
    protected static function createLogger(Context $context): LoggerInterface
    {
        $logger = (new Runnable($context->get('config')->get('app.logger-build', [__CLASS__, 'createDefaultLogger'])))->run();
        if ($logger instanceof LoggerInterface) {
            return $logger;
        } else {
            return new NullLogger;
        }
    }
    
    /**
     * 创建默认记录器
     *
     * @return LoggerInterface
     */
    public static function createDefaultLogger(): LoggerInterface
    {
        $dataPath = SUDA_DATA.'/logs';
        FileSystem::makes($dataPath);
        if (\is_writable(dirname($dataPath))) {
            FileSystem::makes($dataPath.'/zip');
            FileSystem::makes($dataPath.'/dump');
            return new FileLogger(
            [
                'log-level' => defined('SUDA_DEBUG_LEVEL') ? constant('SUDA_DEBUG_LEVEL') : 'debug',
                'save-path' => $dataPath,
                'save-zip-path' => $dataPath.'/zip',
                'log-format' => '%message%',
                'save-pack-path' => $dataPath.'/dump',
            ]
        );
        }
        return new NullLogger;
    }
    
    public function getDefaultConfig():array
    {
        return [
            'log-format' => '%time-format% - %memory-format% [%level%] %file%:%line% %message%',
            'start-time' => 0,
            'start-memory' => 0,
        ];
    }
}
