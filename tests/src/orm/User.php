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
     * email
     *
     * @field varchar(20)
     * @var string
     */
    protected $email;

    /**
     * 匿名操作
     *
     * @field-name create_time
     * @field datetime
     * @var string
     */
    protected $createTime;
}
