<?php
namespace test\database;

/**
 * @table user
 */
class UserField
{
    /**
     * ID
     *
     * @field bigint(20)
     * @var int
     */
    protected $id;

    /**
     * 
     * @field varchar(80)
     * @var int
     */
    protected $name;

    /**
     * money
     *
     * @field DECIMAL(10,2)
     * @var float
     */
    protected $money;

    /**
     * 匿名操作
     *
     * @field-name create_time
     * @field datetime
     * @var string
     */
    protected $createTime;

    /**
     * 
     * @field text
     * @var string
     */
    protected $content;
}
