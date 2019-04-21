<?php
namespace suda\orm\struct;

use suda\orm\TableStruct;

/**
 * 感知表结构
 */
interface TableStructAwareInterface
{
    public static function getTableStruct():TableStruct;
}
