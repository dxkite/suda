<?php
namespace dxkite\suda\hook;
use suda\template\Manager;

class SafeCheckHook
{
    public static function exec(string & $content)
    {
        $app = real_absolute_path(APP_DIR);
        $document = $_SERVER['DOCUMENT_ROOT'];
        // 检查目录位置
        if (DEBUG && substr($app,0,strlen($document)) === $document) {
            $notice= Manager::display(module(__FILE__).':notice')->set('app',$app)->getRenderedString();
            if (strpos($content, '</body>')) {
                $content=str_replace('</body>', $notice.'</body>', $content);
            } else {
                $content.=$notice;
            }
        }
        // 检查资源路径
        if (DEBUG && !\is_writable(APP_PUBLIC)) {
            $writable= Manager::display(module(__FILE__).':writable')->getRenderedString();
            if (strpos($content, '</body>')) {
                $content=str_replace('</body>', $writable.'</body>', $content);
            } else {
                $content.=$writable;
            }
        }
    }
}
