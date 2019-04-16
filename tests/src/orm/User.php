<?php
namespace test\orm;

use test\orm\UserField;

/**
 * @table user
 */
class User extends UserField
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
}
