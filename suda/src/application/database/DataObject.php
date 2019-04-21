<?php
namespace suda\application\database;

use suda\orm\middleware\Middleware;
use suda\orm\struct\ArrayDataTrait;
use suda\orm\struct\WriteStatement;
use suda\orm\struct\ArrayDataInterface;
use suda\application\database\DataAccess;
use suda\orm\middleware\NullMiddlewareTrait;
use suda\orm\struct\TableStructAwareInterface;
use suda\orm\middleware\MiddlewareAwareInterface;
use suda\application\database\TableMiddlewareTrait;

/**
 * 数据表抽象对象
 *
 * 用于提供对数据表的操作
 *
 */
abstract class DataObject implements ArrayDataInterface
{
    use ArrayDataTrait;

    /**
     * 检查字段是否存在
     *
     * @param string $name
     * @return boolean
     */
    public function checkFieldExist(string $name)
    {
        return DataAccess::createStruct(static::class)->getFields()->hasField($name);
    }

    /**
     * 获取序列化对象
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->data;
    }
}
