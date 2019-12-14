<?php


namespace suda\application\database;


use suda\database\struct\TableStruct;
use suda\database\connection\Connection;
use suda\database\exception\SQLException;
use suda\application\database\creator\MySQLTableCreator;

abstract class TableCreator
{
    protected static $map = [
      'mysql' => MySQLTableCreator::class,
    ];

    abstract function create(Connection $connection, TableStruct $fields);

    /**
     * @param Table $table
     * @return bool
     * @throws SQLException
     */
    public static function make(Table $table):bool {
        $type = $table->getSource()->write()->getType();

        if (array_key_exists($type, static::$map)) {
            $className = static::$map[$type];
            /** @var TableCreator $creator */
            $creator = new $className();
            return $creator->create($table->getSource()->write(), $table->getStruct());
        }
        return false;
    }
}