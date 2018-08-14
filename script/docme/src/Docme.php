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

namespace docme;

use suda\core\Storage;

/**
 * 反射导出注释文档
 */
class Docme
{
    protected $exportClass;
    protected $exportFunction;
    protected $rootPath;
    protected $exportPath;

    public $mdIndex;

    public function path(string $path)
    {
        return self::cut($path,$this->rootPath);
    }

    public function exportPath(string $path)
    {
        return self::realPath(self::cut($path,$this->exportPath));
    }

    public function cut(string $path,string $parent)
    {
        return Storage::cut(Storage::abspath($path), Storage::abspath($parent));
    }

    public function isTarget(string $file)
    {
        $filePath=Storage::abspath($file);
        $preg='/^'.preg_quote($this->rootPath, '/').'/';
        return preg_match($preg, $filePath);
    }

    public function root(string $path)
    {
        $this->rootPath=Storage::abspath($path);
        $files=Storage::readDirFiles($path, true, '/\.php$/');
        foreach ($files as $file) {
            include_once $file;
        }
    }

    public function export(string $path)
    {
        $classes=[];
        $functions=[];
        $this->exportPath =$path;

        storage()->delete($path.'/functions');
        storage()->delete($path.'/classes');
        
        foreach ($this->exportFunction as $function) {
            $functionInfo=(new FunctionExport($function, $this))->export($path.'/functions');
            if ($functionInfo) {
                $functions[$function]=$functionInfo;
            }
        }
        
        $userDefinedInterfaces = [];

        foreach ($this->exportClass as $className) {
            $class = new \ReflectionClass($className);
            $interfaces = $class->getInterfaces();
            foreach ($interfaces as $name=>$interface) {
                if ($interface->isUserDefined()) {
                    array_push($userDefinedInterfaces,$interface);
                }
            }
            $classInfo=(new ClassExport($class, $this))->export($path.'/classes');
            if ($classInfo) {
                $classes[$className]=$classInfo;
            }
        }
        
        foreach ($userDefinedInterfaces as $interfaceObj) {
            $classInfo=(new ClassExport($interfaceObj, $this))->export($path.'/classes');
            if ($classInfo) {
                $classes[$interfaceObj->getName()]=$classInfo;
            }
        }

        $this->genIndexFunction($path, $functions);
        $this->genIndexClass($path, $classes);
        $this->genReadme($path, $classes, $functions);
        $this->genSummary($path, $classes, $functions);
    }

    public function genIndexFunction($path, $functions)
    {
        $template=new ExportTemplate;
        $template->setSrc(__DIR__.'/../template/functions-readme.md');
        $template->setValues([
            'functions'=>$functions,
        ]);
        $destPath=$path.'/functions/README.md';
        $template->export($destPath);
        print 'generate functions-readme  --> '.$destPath ."\r\n";
    }

    public function genIndexClass($path, $classes)
    {
        $template=new ExportTemplate;
        $template->setSrc(__DIR__.'/../template/classes-readme.md');
        $template->setValues([
            'classes'=>$classes,
        ]);
        $destPath=$path.'/classes/README.md';
        $template->export($destPath);
        print 'generate classes-readme  --> '.$destPath ."\r\n";
    }

    public function genReadme($path, $classes, $functions)
    {
        $template=new ExportTemplate;
        $template->setSrc(__DIR__.'/../template/readme.md');
        $template->setValues([
            'classes'=>$classes,
            'functions'=>$functions,
        ]);
        $destPath=$path.'/README.md';
        $template->export($destPath);
        print 'generate readme  --> '.$destPath ."\r\n";
    }

    public function genSummary($path)
    {
        $template=new ExportTemplate;
        $template->setSrc(__DIR__.'/../template/SUMMARY.md');
        $template->setValues([
            'classes'=> $this->mdIndex['classes'],
            'functions'=> $this->mdIndex['functions'],
            'docme' => $this,
        ]);
        $destPath=$path.'/SUMMARY.md';
        $template->export($destPath);
        print 'generate SUMMARY  --> '.$destPath ."\r\n";
    }

    public function setFunctions(array $functions)
    {
        $this->exportFunction=$functions;
    }

    public function setClasses(array $classes)
    {
        $this->exportClass=$classes;
    }

    public static function realPath(string $path)
    {
        return preg_replace('/[\\\\\/]+/', '/', $path);
    }
}
