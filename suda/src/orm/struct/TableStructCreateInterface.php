<?php
namespace suda\orm\struct;


/**
 * 感知表结构
 */
interface TableStructCreateInterface
{
    /**
     * 创建数据表结构
     *
     * @param TableStruct $struct 父级或初始数据表结构
     * @return TableStruct
     */
    public static function createTableStruct(TableStruct $struct):TableStruct;
}
