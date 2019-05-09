<?php
namespace test\orm;

/**
 * @table user
 * 
 * @field id bigint(20) primary auto
 * @field name varchar(80) unique
 * @field money DECIMAL(10,2) key
 * @field-json content text
 */
class User2
{
    
}
