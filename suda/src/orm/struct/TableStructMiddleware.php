<?php
namespace suda\orm\struct;

use ReflectionProperty;
use suda\orm\middleware\ObjectMiddleware;

/**
 * 结构中间件
 */
class TableStructMiddleware extends ObjectMiddleware
{
    /**
     * 获取字段名
     *
     * @param ReflectionProperty $property
     * @return string
     */
    protected function getFieldName(ReflectionProperty $property)
    {
        return TableStructBuilder::getFieldName($property);
    }
}
