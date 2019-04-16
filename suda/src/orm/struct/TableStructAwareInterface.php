<?php
namespace suda\orm\struct;

/**
 * 感知表结构
 */
interface TableStructAwareInterface
{
    public static function getTableStruct():TableStruct;
}
