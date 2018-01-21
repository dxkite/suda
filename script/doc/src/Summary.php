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
 * @version    since 1.2.13
 */

namespace doc;

use suda\core\Storage;


/**
 * 反射导出注释文档
 */
class Summary
{
    protected $exportClass;
    protected $exportFunction;
    protected static $rootPath;

    public static  function path(string $path) {
       return Storage::cut(Storage::abspath($path),Storage::abspath(static::$rootPath));
    }

    public function include(string $path)
    {
        static::$rootPath=$path;
        $files=Storage::readDirFiles($path, true, '/\.php$/');
        foreach ($files as $file) {
            include_once $file;
        }
    }

    public function export(string $path)
    {
        $classes=[];
        $functions=[];

        foreach ($this->exportFunction as $function) {
            $functionInfo=(new FunctionExport($function))->export($path.'/functions');
            // $functionInfo['functionDoc']=trim(preg_replace('/\r?\n/',' ',$functionInfo['functionDoc']));
            $functions[$function]=$functionInfo;
        }

        foreach ($this->exportClass as $class) {
            $classInfo=(new ClassExport($class))->export($path.'/classes');
            // $classInfo['classDoc']=trim(preg_replace('/\r?\n/',' ',$classInfo['classDoc']));
            $classes[$class]=$classInfo;
        }

        $template=new ExportTemplate;
        $template->setSrc(__DIR__.'/../template/summary.md');
        $template->setValues([
            'classes'=>$classes,
            'functions'=>$functions,
        ]);
        $template->export($path.'/summary.md');
    }

    public function setFunctions(array $functions)
    {
        $this->exportFunction=$functions;
    }

    public function setClasses(array $classes)
    {
        $this->exportClass=$classes;
    }

    public static function realPath(string $path){
        return preg_replace('/[\\\\\/]+/', '/', $path);
    }
}
