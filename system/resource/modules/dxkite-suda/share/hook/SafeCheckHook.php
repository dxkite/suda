<?php
namespace dxkite\suda\hook;
use suda\template\Manager;

class SafeCheckHook
{
    public static function exec(string & $content)
    {
        $app = real_absolute_path(APP_DIR);
        $document = $_SERVER['DOCUMENT_ROOT'];
        if (DEBUG && substr($app,0,strlen($document)) === $document) {
            $notice= Manager::display(module(__FILE__).':notice')->set('app',$app)->getRenderedString();
            if (strpos($content, '</body>')) {
                $content=str_replace('</body>', $notice.'</body>', $content);
            } else {
                $content.=$notice;
            }
        }
    }
}
