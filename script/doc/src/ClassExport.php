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

class ClassExport
{
    protected $reflect;

    public function __construct(string $class)
    {
        $this->reflect=new \ReflectionClass($class);
    }

    public function export(string $path)
    {
        $reflect=$this->reflect;
        $methods=$reflect->getMethods(\ReflectionMethod::IS_PUBLIC | \ReflectionMethod::IS_PROTECTED);
        $classData= [
            'className'=>$reflect->getShortName(),
            'classFullName'=>$reflect->getName(),
        ];
        $methodPath=$path.'/'.$reflect->getName();
        foreach ($methods as $method) {
            self::exportMethod($method, $classData, $methodPath);
        }
    }
    
    public function exportMethod($reflect, array $classData, string $path)
    {
        $template=new ExportTemplate;
        $template->setSrc(__DIR__.'/../template/method.md');
        $value=FunctionExport::getFunctionInfo($reflect);
        $value=array_merge($value, $classData);
        $template->setValues($value);
        $destPath=$path.'/'.$reflect->getName().'.md';
        print 'doc  method'.$classData['className'].' -> '.$value['functionName'] .' --> '.$destPath ."\r\n";
        $template->export($destPath);
        return $destPath;
    }

    public static function getUserDefinedClasses()
    {
        $classes=get_declared_classes();
        $userClasses=[];
        foreach ($classes as $class) {
            if ((new \ReflectionClass($class))->isUserDefined()) {
                if (preg_match('/^class@anonymous/', $class)) {
                    continue;
                }
                $userClasses[]=$class;
            }
        }
        return $userClasses;
    }
}
