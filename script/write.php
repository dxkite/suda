<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.0.0 or newer
 * 
 * Copyright (c)  2017 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.9
 */
define('DATA_DIR', __DIR__.'/data');
define('APP_PUBLIC', __DIR__.'/data/public');


require_once __DIR__ .'/../system/suda-console.php';
 

use suda\core\Storage;
$option=getopt("d:r::");
$files=Storage::readDirFiles($option['d']??__DIR__, true, '/\.php$/');
$version=SUDA_VERSION;
$refresh=isset($option['r'])?true:false;

$mark=<<<Mark
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.0.0 or newer
 * 
 * Copyright (c)  2018 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since {$version}
 */

Mark;


foreach ($files as $no=> $file) {
    $content=file_get_contents($file);
    
    if (preg_match('/^\<\?php(?:\r\n)+?(\/\*\*(?:.|\r\n)+?\*\/(\r\n)+?)/', $content, $match)) {
        $get=$match[1];
        if (md5($get)===md5($mark)) {
            echo 'ignore> '.$file." \r\n";
        } elseif (preg_match('/Suda\s+FrameWork/i', $get)) {
            if($refresh){
                $content=preg_replace('/^\<\?php(?:\r\n)+?(\/\*\*(?:.|\r\n)+?\*\/(\r\n)+?)/',"<?php\r\n".$mark, $content);
                echo '['.$no.'] file > '.$file." refresh\r\n";
                file_put_contents($file, $content);
            }
        } else {
            $content=preg_replace('/^\<\?php/', "<?php\r\n".$mark, $content);
            echo '['.$no.'] file > '.$file." add ok\r\n";
            file_put_contents($file, $content);
        }
    } else {// 无注释
        $content=preg_replace('/^\<\?php/', "<?php\r\n".$mark, $content);
        echo '['.$no.'] file init comment> '.$file."\r\n";
        file_put_contents($file, $content);
    }
}
