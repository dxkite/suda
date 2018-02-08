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

class FunctionExport
{
    protected $reflect;
    protected $docme;

    public function __construct(string $function, $docme)
    {
        $this->reflect=new \ReflectionFunction($function);
        $this->docme=$docme;
    }

    public function export(string $path)
    {
        if (!$this->docme->isTarget($this->reflect->getFileName())){
            return false;
        }
        $template=new ExportTemplate;
        $template->setSrc(__DIR__.'/../template/function.md');
        $value=static::getFunctionInfo($this->reflect,$this->docme);
        $template->setValues($value);
        $destPath=$path.'/'.$this->reflect->getName().'.md';
        print 'doc function '.$value['functionName'] .' --> '.$destPath ."\r\n";
        $template->export($destPath);
        return $value;
    }

    public static function getUserDefinedFunctions()
    {
        return get_defined_functions()['user'];
    }

    public static function getFunctionInfo($reflect,$docme)
    {
        list($comment, $params, $return, $exData)= static::getDoc($reflect->getDocComment());
        $value=$exData;
        $value['functionName']=$reflect->getName();
        $value['functionDoc']=$comment;
        $value['fileName']= $docme->path($reflect->getFileName());
        $value['lineStart']= $reflect->getStartLine();
        $value['lineEnd']=  $reflect->getEndLine();
        
        $refParams=$reflect->getParameters();
        $paramValues=[];

        foreach ($refParams as $refParam) {
            $name= $refParam->getName();
            if (isset($params[$name])) {
                $paramValues[$name]['description']=$params[$name]['description'];
            }
            if ($refParam->isDefaultValueAvailable()) {
                if ($refParam->isDefaultValueConstant()) {
                    $paramValues[$name]['default']=$refParam->getDefaultValueConstantName();
                } else {
                    $paramValues[$name]['default']=static::getValue($refParam->getDefaultValue());
                }
            }
            if ($refParam->hasType()) {
                $paramValues[$name]['type']=$refParam->getType()->__toString();
            } else {
                if (isset($params[$name])) {
                    $paramValues[$name]['type']=$params[$name]['type'];
                }
            }
        }
        $value['params']=$paramValues;
        $value['return']=$return;
        return $value;
    }

    public static function getDoc(string $docs)
    {
        $docs= trim(preg_replace('/^\/\*\*(.+?)\*\//ms', '$1', $docs));
        $lines=preg_split('/\r?\n/', $docs);
        $params=[];
        $return=[];
        $docs=[];
        foreach ($lines as $index=> $line) {
            $line= substr(ltrim(trim($line), '*'), 1)??' ';
            if (preg_match('/^@param\s+(.+?)\s+(.+?)(\s+(.+))?$/', $line, $match)) {
                if (!isset($match[3])) {
                    $match[3]='无';
                }
                list($comment, $type, $name, $description) = $match;
                $name=ltrim($name, '$');
                $params[$name]['comment']=$comment;
                $params[$name]['description']=$description;
                $params[$name]['type']=$type;
            } elseif (preg_match('/^@return\s+(.+?)(\s+(.+))?$/', $line, $match)) {
                if (!isset($match[2])) {
                    $match[2]='无';
                }
                list($comment, $type, $description) = $match;
                $return['comment']=$comment;
                $return['type']=$type;
                $return['description']=$description;
            } else {
                $docs[]=$line;
            }
        }
        $datas=static::docField($docs);
        return [$datas['description'],$params,$return,$datas];
    }

    public static function getValue($value)
    {
        if (is_null($value)) {
            $value='null';
        } elseif (is_array($value)) {
            $value='Array';
        } elseif (is_object($value)) {
            $value='Object '.get_class($value);
        }
        return $value;
    }

    public static function docField(array $lines)
    {
        $field='document';
        $datas=[
            'description'=>array_shift($lines)
        ];
        foreach ($lines as $line) {
            if (preg_match('/^@(\w+?)(\s+)?$/', $line, $match)) {
                list($line, $field)=$match;
            } else {
                $datas[$field][] = $line;
            }
        }
        foreach ($datas as $name=> $content) {
            if (is_array($content)) {
                $datas[$name]=implode("\r\n", $content);
            }
        }
        return $datas;
    }
}
