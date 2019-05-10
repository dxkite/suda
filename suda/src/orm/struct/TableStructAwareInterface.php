<?php
namespace suda\orm\struct;

use suda\orm\TableStruct;

/**
 * 感知表结构
 */
interface TableStructAwareInterface
{
    /**
     * 创建数据表结构
     *
     * @param TableStruct $struct 父级或初始数据表结构
     * @return TableStruct
     */
    public static function createTableStruct(TableStruct $struct):TableStruct;
}
