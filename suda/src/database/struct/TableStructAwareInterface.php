<?php
namespace suda\database\struct;


/**
 * 感知表结构
 */
interface TableStructAwareInterface
{
    /**
     * 创建数据表结构
     *
     * @return TableStruct
     */
    public static function getTableStruct():TableStruct;
}
