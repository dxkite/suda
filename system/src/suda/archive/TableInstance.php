<?php
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

    protected static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance=new self;
        }
        return self::$instance;
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
    
    protected function formatTableName(string $className)
    {
        return $className;
    }

    protected function getClassName(string $tableName)
    {
        $tableRawName=$this->formatTableName($tableName);
        if (isset(self::$tableClass[$tableRawName])) {
            return self::$tableClass[$tableRawName];
        } else {
            throw new TableException(__('table %s class not exist', $tableName));
        }
    }
}
