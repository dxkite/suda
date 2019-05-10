<?php
namespace suda\application\database;

use ReflectionException;
use suda\orm\struct\ArrayDataTrait;
use suda\orm\struct\ArrayDataInterface;

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
     * @throws ReflectionException
     */
    public function checkFieldExist(string $name)
    {
        return DataAccess::createStruct(static::class)->hasField($name);
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
