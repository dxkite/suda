<?php


namespace suda\framework\debug\log\logger;

use Psr\Log\AbstractLogger;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use suda\framework\debug\ConfigInterface;
use suda\framework\debug\ConfigTrait;
use suda\framework\debug\log\logger\exception\FileLoggerException;
use ZipArchive;

abstract class FileLoggerBase extends AbstractLogger implements ConfigInterface
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
     * 检查日志文件大小
     *
     * @return boolean
     */
    protected function checkSize(): bool
    {
        $logFile = $this->latest;
        if ($this->getConfig('max-file-size') == 0) {
            return false;
        }
        if (file_exists($logFile)) {
            if (filesize($logFile) > $this->getConfig('max-file-size')) {
                return true;
            }
        }
        return false;
    }

    /**
     * 删除已经压缩的文件
     */
    protected function removePackFiles()
    {
        foreach ($this->removeFiles as $file) {
            if (is_file($file) && file_exists($file)) {
                unlink($file);
            }
        }
        $this->removeFiles = [];
    }

    /**
     * @param string $from
     * @param string $to
     */
    protected function safeMoveKeep(string $from, string $to)
    {
        $fromFile = fopen($from, 'r');
        $toFile = fopen($to, 'w+');
        if ($fromFile !== false && $toFile !== false) {
            flock($toFile, LOCK_EX);
            // 复制内容
            stream_copy_to_stream($fromFile, $toFile);
            flock($toFile, LOCK_UN);
            fclose($toFile);
            fclose($fromFile);
        }
        // 清空内容
        $this->clearContent($from);
    }

    /**
     * @throws FileLoggerException
     */
    protected function prepareWrite()
    {
        $unique = substr(md5(uniqid()), 0, 8);
        $save = $this->getConfig('save-path');
        $this->tempName = $save . '/' . date('YmdHis') . '.' . $unique . '.log';
        $temp = fopen($this->tempName, 'w+');
        if ($temp !== false) {
            $this->temp = $temp;
        } else {
            throw new FileLoggerException(__METHOD__ . ':' . sprintf('cannot create log file'));
        }
        $this->latest = $save . '/' . $this->getConfig('file-name');
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
     * @param ZipArchive $zip
     * @param string $logFile
     */
    protected function zipFile(ZipArchive $zip, string $logFile)
    {
        $add = $zip->addFile($logFile, date('Y-m-d') . '-' . $zip->numFiles . '.log');
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
        if ($add) {
            $this->clearContent($logFile);
        }
    }

    /**
     * @param string $logFile
     */
    protected function moveFile(string $logFile)
    {
        if (is_file($logFile) && file_exists($logFile)) {
            $this->safeMoveKeep(
                $logFile,
                $this->getConfig('save-path')
                . '/' . date('Y-m-d')
                . '-' . substr(md5(uniqid()), 0, 8) . '.log'
            );
        }
    }

    /**
     * 清空内容
     * @param string $path
     * @return bool
     */
    protected function clearContent(string $path)
    {
        $file = fopen($path, 'w');
        if ($file !== false) {
            fclose($file);
            return true;
        }
        return false;
    }

    /**
     * 打包文件
     */
    protected function packLogFile()
    {
        $logFile = $this->latest;
        $path = preg_replace(
            '/[\\\\]+/',
            '/',
            $this->getConfig('save-zip-path') . '/' . date('Y-m-d') . '.zip'
        );
        $zip = $this->getZipArchive($path);
        if ($zip !== null) {
            $this->zipFile($zip, $logFile);
        } else {
            $this->moveFile($logFile);
        }
    }

    /**
     * 将临时文件写入最后日志
     */
    protected function rollLatest()
    {
        if (isset($this->latest)) {
            $latest = fopen($this->latest, 'a+');
            if ($latest !== false && flock($latest, LOCK_EX)) {
                rewind($this->temp);
                stream_copy_to_stream($this->temp, $latest);
                flock($latest, LOCK_UN);
                fclose($latest);
            }
            fclose($this->temp);
            if (file_exists($this->tempName)) {
                unlink($this->tempName);
            }
            $this->temp = null;
            $this->tempName = null;
        }
    }
}
