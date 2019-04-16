<?php
namespace test\orm;

/**
 * @table user
 */
class User
{
    /**
     * ID
     *
     * @field bigint(20) primary auto
     * @var int
     */
    protected $id;

    /**
     * 
     * @field varchar(80) unique
     * @var int
     */
    protected $name;

    /**
     * money
     *
     * @field DECIMAL(10,2) key
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

    protected $content;
}
