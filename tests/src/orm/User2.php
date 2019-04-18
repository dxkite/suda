<?php
namespace test\orm;

use test\orm\UserField;

/**
 * @table user
 * 
 * @field id bigint(20) primary auto
 * @field name varchar(80) unique
 * @field money DECIMAL(10,2) key
 * @field content text
 */
class User2
{
    
}
