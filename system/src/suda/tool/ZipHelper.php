<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.2.0 or newer
 *
 * Copyright (c)  2017-2018 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.10
 */

namespace suda\tool;

use ZipArchive;

class ZipHelper
{

    /**
     * 解压文件到目录
     *
     * @param string $inputFile
     * @param string $output
     * @return boolean 解压结果
     */
    public static function unzip(string $inputFile, string $output):bool
    {
        $zip=new ZipArchive;
        if ($zip->open($inputFile, ZipArchive::CHECKCONS)) {
            $zip->extractTo($output);
            $zip->close();
            return true;
        }
        return false;
    }

    /**
     * 压缩目录到文件
     *
     * @param string $path
     * @param string $output
     * @return boolean 压缩结果
     */
    public static function zip(string $path, string $output)
    {
        $zip=new ZipArchive;
        if ($zip->open($output, ZipArchive::CREATE|ZipArchive::OVERWRITE)) {
            self::zipFolder($zip, $path, $path);
            $zip->close();
            return true;
        } else {
            return false;
        }
        return false;
    }

    protected static function zipFolder(ZipArchive & $zip, string $folder, string $root)
    {
        if (is_dir($folder)) {
            if ($dh = opendir($folder)) {
                while (($file = readdir($dh)) !== false) {
                    if ($file!='.' &&  $file!='..') {
                        $path=$folder.'/'.$file;
                        if (is_dir($path)) {
                            self::zipFolder($zip, $path, $root);
                        } else {
                            $cutPath=storage()->cut($path, $root);
                            $localPath=preg_replace('/[\\\\\\/]+/', '/', $cutPath);
                            $zip->addFile($path, $localPath);
                        }
                    }
                }
                closedir($dh);
            }
        }
    }
}
