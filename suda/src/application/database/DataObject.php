<?php
namespace suda\application\database;

use suda\orm\middleware\Middleware;
use suda\orm\struct\ArrayDataTrait;
use suda\orm\struct\WriteStatement;
use suda\orm\struct\ArrayDataInterface;
use suda\application\database\DataAccess;
use suda\orm\struct\TableStructAwareTrait;
use suda\orm\struct\TableStructAwareInterface;
use suda\application\database\TableMiddlewareTrait;

/**
 * 数据表抽象对象
 *
 * 用于提供对数据表的操作
 *
 */
abstract class DataObject implements TableStructAwareInterface, ArrayDataInterface, Middleware
{
    use ArrayDataTrait;
    use TableStructAwareTrait;
    use TableMiddlewareTrait;
    
    /**
     * 检查字段是否存在
     *
     * @param string $name
     * @return boolean
     */
    public function checkFieldExist(string $name)
    {
        return static::getTableStruct()->getFields()->hasField($name);
    }
}
