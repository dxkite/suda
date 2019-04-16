<?php
namespace suda\orm\struct;

use suda\orm\TableStruct;

/**
 * 感知表结构
 */
trait TableStructAwareTrait
{
    /**
     * 表结构
     *
     * @var TableStruct
     */
    protected static $struct;

    public static function getTableStruct():TableStruct
    {
        if (static::$struct === null) {
            static::$struct = static::createStruct();
        }
        return static::$struct;
    }

    public static function createStruct():TableStruct
    {
        return (new TableStructBuilder(static::class))->createStruct();
    }
}
