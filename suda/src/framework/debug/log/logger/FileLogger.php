<?php

namespace suda\framework\debug\log\logger;

use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;
use suda\framework\debug\ConfigTrait;
use suda\framework\debug\log\LogLevel;
use suda\framework\debug\ConfigInterface;
use suda\framework\filesystem\FileSystem;
use suda\framework\debug\log\AbstractLogger;
use suda\framework\debug\log\logger\exception\FileLoggerException;

/**
 * Class FileLogger
 * @package suda\framework\debug\log\logger
 */
class FileLogger extends AbstractLogger implements ConfigInterface
{
    use ConfigTrait;

    /**
     * 文件
     *
     * @var resource
     */
    protected $temp;

    /**
     * 临时文件名
     *
     * @var string
     */
    protected $tempName;

    /**
     * 移除文件
     *
     * @var array
     */
    protected $removeFiles = [];

    /**
     * 最后的日志
     *
     * @var string
     */
    protected $latest;

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
     * @return resource
     * @throws FileLoggerException
     */
    public function getAvailableWrite()
    {
        if (is_resource($this->temp)) {
            return $this->temp;
        }
        $this->prepareWrite();
        return $this->temp;
    }

    /**
     * @throws FileLoggerException
     */
    private function prepareWrite()
    {
        $msec = explode('.', microtime(true))[1];
        $save = $this->getConfig('save-path');
        $this->tempName = $save . '/' . date('YmdHis') . '.' . $msec . '.log';
        $temp = fopen($this->tempName, 'w+');
        if ($temp !== false) {
            $this->temp = $temp;
        } else {
            throw new FileLoggerException(__METHOD__ . ':' . sprintf('cannot create log file'));
        }
        $this->latest = $save . '/' . $this->getConfig('file-name');
    }

    /**
     * @return array
     */
    public function getDefaultConfig(): array
    {
        return [
            'save-path' => './logs',
            'save-zip-path' => './logs/zip',
            'save-dump-path' => './logs/dump',
            'max-file-size' => 2097152,
            'file-name' => 'latest.log',
            'log-level' => 'debug',
            'log-format' => '[%level%] %message%',
        ];
    }

    /**
     * 打包文件
     */
    private function packLogFile()
    {
        $logFile = $this->latest;
        $path = preg_replace(
            '/[\\\\]+/',
            '/',
            $this->getConfig('save-zip-path') . '/' . date('Y-m-d') . '.zip'
        );
        $zip = $this->getZipArchive($path);
        if ($zip !== null) {
            if ($zip->addFile($logFile, date('Y-m-d') . '-' . $zip->numFiles . '.log')) {
                array_push($this->removeFiles, $logFile);
            }
            if (is_dir($this->getConfig('save-dump-path'))) {
                $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(
                    $this->getConfig('save-dump-path'),
                    RecursiveDirectoryIterator::SKIP_DOTS
                ));
                foreach ($it as $dumpLog) {
                    if ($zip->addFile($dumpLog, 'dump/' . basename($dumpLog))) {
                        array_push($this->removeFiles, $dumpLog);
                    }
                }
            }
            $zip->close();
        } else {
            if (is_file($logFile) && file_exists($logFile)) {
                rename(
                    $logFile,
                    $this->getConfig('save-path')
                    . '/' . date('Y-m-d')
                    . '-' . substr(md5_file($logFile), 0, 8) . '.log'
                );
            }
        }
    }

    /**
     * 获取压缩
     *
     * @param string $path
     * @return ZipArchive|null
     */
    private function getZipArchive(string $path)
    {
        if (class_exists('ZipArchive')) {
            $zip = new ZipArchive;
            $res = $zip->open($path, ZipArchive::CREATE);
            if ($res === true) {
                return $zip;
            }
        }
        return null;
    }

    /**
     * 检查日志文件大小
     *
     * @return boolean
     */
    private function checkSize(): bool
    {
        $logFile = $this->latest;
        if (file_exists($logFile)) {
            if (filesize($logFile) > $this->getConfig('max-file-size')) {
                return true;
            }
        }
        return false;
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
     * 将临时文件写入最后日志
     */
    private function rollLatest()
    {
        if (isset($this->latest)) {
            $size = ftell($this->temp);
            fseek($this->temp, 0);
            if ($size > 0) {
                $body = fread($this->temp, $size);
                file_put_contents($this->latest, $body, FILE_APPEND);
            }
            fclose($this->temp);
            $this->temp = null;
            if ($this->tempName !== null) {
                unlink($this->tempName);
                $this->tempName = null;
            }
        }
    }

    /**
     * 删除已经压缩的文件
     */
    private function removePackFiles()
    {
        foreach ($this->removeFiles as $file) {
            if (is_file($file) && file_exists($file)) {
                unlink($file);
            }
        }
        $this->removeFiles = [];
    }

    /**
     * 即时写入日志
     */
    public function write()
    {
        if ($this->checkSize()) {
            $this->packLogFile();
        }
        $this->rollLatest();
        $this->removePackFiles();
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
