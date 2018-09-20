<?php
require_once __DIR__ .'/../../system/suda-console.php';
 

use suda\core\Storage;

$option=getopt("d:r::a::");
$files=Storage::readDirFiles($option['d']??__DIR__, true, '/\.php$/');

$refresh=isset($option['r'])?true:false;
$autoadd=isset($option['a'])?true:false;

$markTemplate=<<<'Mark'
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
 * @version    since $version
 */

Mark;


foreach ($files as $no=> $file) {
    $content=file_get_contents($file);
    
    if (preg_match('/^\<\?php(?:\r?\n)+?(\/\*\*(?:.|\r?\n)+?\*\/(\r?\n)+?)/', $content, $match)) {
        $get=$match[1];
        preg_match('/since\s+([\d\.]+)/', $get, $versionMatch);
        list($x, $version)=$versionMatch;
        $mark=str_replace('$version', $version, $markTemplate);

        if (md5($get)===md5($mark)) {
            echo 'ignore> ['.$no.'] '.$file." \r\n";
        } elseif (preg_match('/Suda\s+FrameWork/i', $get)) {
            if ($refresh) {
                $mark=str_replace('$version', SUDA_VERSION , $markTemplate);
                $content=preg_replace('/^\<\?php(?:\r?\n)+?(\/\*\*(?:.|\r?\n)+?\*\/(\r?\n)+?)/', '<?php'.PHP_EOL.$mark, $content);
                echo '['.$no.'] file > '.$file." refresh\r\n";
                file_put_contents($file, $content);
            } else {
                $content=preg_replace('/^\<\?php(?:\r?\n)+?(\/\*\*(?:.|\r?\n)+?\*\/(\r?\n)+?)/', '<?php'.PHP_EOL.$mark, $content);
                echo '['.$no.'] file > '.$file." update\r\n";
                file_put_contents($file, $content);
            }
        } elseif ($autoadd) {
            $content=preg_replace('/^\<\?php/', '<?php'.PHP_EOL.$mark, $content);
            echo '['.$no.'] file > '.$file." add ok\r\n";
            file_put_contents($file, $content);
        }
    } elseif ($autoadd) {// 无注释
        $content=preg_replace('/^\<\?php/', '<?php'.PHP_EOL.$mark, $content);
        echo '['.$no.'] file init comment> '.$file."\r\n";
        file_put_contents($file, $content);
    }
}
