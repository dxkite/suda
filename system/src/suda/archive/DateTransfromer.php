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
 * @version    since 1.2.14
 */

namespace suda\archive;

use suda\tool\Command;


class DateTransfromer
{
    protected $object;

    public function __construct(?object $object=null)
    {
        $this->object=$object;
    }

    /**
     * 添加列处理类
     *
     * @param object $object
     * @return void
     */
    public function setObject(object $object)
    {
        $this->object=$object;
    }

    /**
     * 转换函数；统一处理数据库输入输出
     *
     * 只处理InputValue类型的数据
     *
     * @param string $name
     * @param string $fieldName
     * @param mixed $inputData
     * @return mixed
     */
    protected function dataTransfrom(string $name, string $fieldName, $inputData)
    {
        $methodName='_'.$name.ucfirst($fieldName).'Field';
        if ($this->object) {
            if (method_exists($this->object, '__dataTransfrom')) {
                return Command::invoke([$this->object,'__dataTransfrom'], func_get_args());
            } elseif (method_exists($this->object, $methodName)) {
                $inputData= Command::invoke([$this->object,$methodName], [$inputData]);
            }
        }
        return $inputData;
    }

    public function inputFieldTransfrom(string $name, $inputData)
    {
        return $this->dataTransfrom('input', $name, $inputData);
    }

    private function outputDataFilter($rowData)
    {
        $methodName='_outputDataFilter';
        if ($this->object) {
            if (method_exists($this->object, $methodName)) {
                return Command::invoke([$this->object, $methodName], [$rowData]);
            }
        }
        return $rowData;
    }

    private function outputFieldTransfrom(string $name, $inputData)
    {
        return $this->dataTransfrom('output', $name, $inputData);
    }

    public function outputRowsTransfrom(array $inputRows)
    {
        foreach ($inputRows as $id=>$inputData) {
            $inputRows[$id]=$this->outputRowTransfrom($inputRows[$id]);
        }
        return $inputRows;
    }

    public function outputRowTransfrom(array $inputData)
    {
        foreach ($inputData as $fieldName => $fieldData) {
            $inputData[$fieldName]=$this->outputFieldTransfrom($fieldName, $fieldData);
        }
        return $this->outputDataFilter($inputData);
    }

    public function outputObjectTransfrom($object)
    {
        $reflect=new \ReflectionClass($object);
        $props=$reflect->getProperties(\ReflectionProperty::IS_PUBLIC | \ReflectionProperty::IS_PROTECTED | \ReflectionProperty::IS_PRIVATE);
        foreach ($props as $prop) {
            $prop->setAccessible(true);
            $prop->setValue($object, $this->outputFieldTransfrom($prop->getName(), $prop->getValue()));
        }
        return $this->outputDataFilter($object);
    }
}
