<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.0.0 or newer
 *
 * Copyright (c)  2017 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.10
 */

namespace suda\archive;

use suda\exception\TableException;

class TableInstance
{
    protected static $tableInstances;
    protected static $instance;
    protected static $tableClass;

    protected function __construct()
    {
        $this->getTableNames();
    }

    public static function new(string $tableName)
    {
        $tableClassName=self::instance()->getClassName($tableName);
        return self::$tableInstances[$tableName]=new $tableClassName;
    }

    public static function getInstance(string $tableName)
    {
        if (isset(self::$tableInstances[$tableName])) {
            return self::$tableInstances[$tableName];
        }
        return self::new($tableName);
    }

    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance=new self;
        }
        return self::$instance;
    }

    public function getTables()
    {
        return static::$tableClass;
    }
    
    protected function getTableNames()
    {
        $modules = app()->getLiveModules();
        foreach ($modules as $module) {
            $this->getTableNameFromModule($module);
        }
    }

    protected function getTableNameFromModule(string $module)
    {
        $config=app()->getModuleConfig($module);
        if (isset($config['table'])) {
            foreach ($config['table'] as $name=>$class) {
                $className=class_name($class);
                if (is_string($name)) {
                    $tableName=$name;
                } else {
                    $tableName=substr(strrchr($className, '\\'), 1);
                }
                self::$tableClass[$tableName]=$className;
            }
        }
    }
    
    protected function getClassName(string $tableName)
    {
        if (isset(self::$tableClass[$tableName])) {
            return self::$tableClass[$tableName];
        } else {
            throw new TableException(__('table %s class not exist', $tableName));
        }
    }
}
