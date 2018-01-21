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

        list($doc)=FunctionExport::getDoc($reflect->getDocComment());

        $classData= [
            'className'=>$reflect->getShortName(),
            'classFullName'=>$reflect->getName(),
            'classDoc' => $doc,
        ];
        $methodPath=$path.'/'.$reflect->getName();

        $template=new ExportTemplate;
        $template->setSrc(__DIR__.'/../template/class.md');

        
        $destPath=$methodPath.'.md';
        print 'doc  class '.$classData['className'].' --> '.$destPath ."\r\n";
        $methodsInfo=[];

        foreach ($methods as $method) {
            $methodInfo=self::exportMethod($method, $classData, $methodPath);
            $methodInfo['functionDoc']=trim(preg_replace('/\r?\n/',' ',$methodInfo['functionDoc']));
            $methodsInfo[$method->getName()]=$methodInfo;
        }

        $classData['methods']= $methodsInfo;
        $template->setValues($classData);
        $template->export($destPath);
        return $classData;
    }
    
    public function exportMethod($reflect, array $classData, string $path)
    {
        $template=new ExportTemplate;
        $template->setSrc(__DIR__.'/../template/method.md');
        $value=FunctionExport::getFunctionInfo($reflect);
        $value['visibility'] = $reflect->isProtected ()? 'protected':'public';
        $value['abstract'] = $reflect->isAbstract()? 'abstract':'';
        $value['static'] = $reflect->isStatic()? 'static':'';
        $value=array_merge($value, $classData);
        $template->setValues($value);
        $destPath=$path.'/'.$reflect->getName().'.md';
        print 'doc  method '.$classData['className'].' -> '.$value['functionName'] .' --> '.$destPath ."\r\n";
        $template->export($destPath);
        return $value;
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
