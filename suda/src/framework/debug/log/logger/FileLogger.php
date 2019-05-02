<?php
namespace suda\framework\debug\log\logger;

use function class_exists;
use function file_exists;
use function fwrite;
use function is_bool;
use function is_dir;
use function is_file;
use function is_resource;
use function microtime;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use function register_shutdown_function;
use function strtr;
use suda\framework\debug\ConfigTrait;
use suda\framework\debug\log\LogLevel;
use suda\framework\debug\ConfigInterface;
use suda\framework\debug\log\AbstractLogger;
use suda\framework\debug\log\logger\exception\FileLoggerException;
use ZipArchive;

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
    protected $tempname;

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
     * @throws FileLoggerException
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->applyConfig($config);
    }

    public function getAviailbleWrite()
    {
        if (is_resource($this->temp)) {
            return $this->temp;
        }
        $this->prepareWrite();
        return $this->temp;
    }

    protected function prepareWrite()
    {
        $msec = explode('.', microtime(true))[1];
        $this->tempname = $this->getConfig('save-path').'/'.date('YmdHis').'.'.$msec.'.log';
        $temp = fopen($this->tempname, 'w+');
        if ($temp !== false) {
            $this->temp = $temp;
        } else {
            throw new FileLoggerException(__CLASS__.':'.sprintf('cannot create log file'));
        }
        $this->latest = $this->getConfig('save-path').'/'.$this->getConfig('file-name');
        register_shutdown_function([$this,'save']);
    }

    public function getDefaultConfig():array
    {
        return [
            'save-path' => './logs',
            'save-zip-path' => './logs/zip',
            'save-pack-path' => './logs/dump',
            'max-file-size' => 2097152,
            'file-name' => 'latest.log',
            'log-level' => 'debug',
            'log-format' => '[%level%] %message%',
        ];
    }
    
    protected function packLogFile()
    {
        $logFile = $this->latest;
        $path = preg_replace('/[\\\\]+/', '/', $this->getConfig('save-zip-path') .'/'.date('Y-m-d').'.zip');
        $zip = $this->getZipArchive($path);
        if ($zip !== null) {
            if ($zip->addFile($logFile, date('Y-m-d'). '-'. $zip->numFiles .'.log')) {
                array_push($this->removeFiles, $logFile);
            }
            if (is_dir($this->getConfig('save-pack-path'))) {
                $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->getConfig('save-pack-path'), RecursiveDirectoryIterator::SKIP_DOTS));
                foreach ($it as $dumpLog) {
                    if ($zip->addFile($dumpLog, 'pack/'.basename($dumpLog))) {
                        array_push($this->removeFiles, $dumpLog);
                    }
                }
            }
            $zip->close();
        } else {
            if (is_file($logFile) && file_exists($logFile)) {
                rename($logFile, $this->getConfig('save-path') . '/' . date('Y-m-d'). '-'. substr(md5_file($logFile), 0, 8).'.log');
            }
        }
    }

    /**
     * 获取压缩
     *
     * @param string $path
     * @return ZipArchive|null
     */
    protected function getZipArchive(string $path)
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
    protected function checkSize():bool
    {
        $logFile = $this->latest;
        if (file_exists($logFile)) {
            if (filesize($logFile) > $this->getConfig('max-file-size')) {
                return true;
            }
        }
        return false;
    }


    public function log($level, string $message, array $context = [])
    {
        if (LogLevel::compare($level, $this->getConfig('log-level')) >= 0) {
            $replace = [];
            $message = $this->interpolate($message, $context);
            $replace['%level%'] = $level;
            $replace['%message%'] = $message;
            $write = strtr($this->getConfig('log-format'), $replace);
            fwrite($this->getAviailbleWrite(), $write.PHP_EOL);
        }
    }

    protected function rollLatest()
    {
        if (isset($this->latest)) {
            $size = ftell($this->temp);
            fseek($this->temp, 0);
            if ($size > 0) {
                $body = fread($this->temp, $size);
                file_put_contents($this->latest, $body, FILE_APPEND);
            }
            fclose($this->temp);
            if ($this->tempname !== null) {
                unlink($this->tempname);
            }
        }
    }

    protected function removePackFiles()
    {
        foreach ($this->removeFiles as $file) {
            if (is_file($file) && file_exists($file)) {
                unlink($file);
            }
        }
    }

    public function save()
    {
        if (function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        }
        if ($this->checkSize()) {
            $this->packLogFile();
        }
        $this->rollLatest();
        $this->removePackFiles();
    }

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
