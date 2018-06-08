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
 * @version    since 1.2.4
 */
namespace suda\archive;

use PDO;
use PDOException;
use suda\core\Config;
use suda\core\Storage;
use suda\exception\SQLException;
use suda\archive\creator\InputValue;
use suda\tool\Command;

/**
 * 数据库查询方案，简化数据库查
 * 单列数据查询方案
 *
 * @example
 *
 */
class SQLQuery implements SQLStatement
{
    protected static $defaultQuery = null;
    protected static $query = null;
    protected $rawQuery;
    /**
     * 构造查询
     *
     * @param string $query
     * @param array $binds
     * @param boolean $scroll
     */
    public function __construct(string $query='', array $binds=[], bool $scroll=false)
    {
        self::_connect();
        $this->rawQuery=self::$query->query($query, $binds, $scroll);
    }

    public function getConnection() {
        return $this->rawQuery->getConnection();
    }
    public function setConnection(Connection $connection) {
        $this->connection = $connection;
        return $this;
     }
    /**
     * 获取查询结果的一列
     *
     * @param integer $fetch_style 结果集形式
     * @return array|false 查询成功则返回一列查询结果，否则返回false
     */
    public function fetch(int $fetchStyle = PDO::FETCH_ASSOC)
    {
        return $this->rawQuery->fetch($fetchStyle);
    }

    public static function useQuery(RawQuery $query)
    {
        self::$query = $query;
    }

    public static function resetQuery()
    {
        if (!self::$defaultQuery->getConnection()->isConnected()) {
            $connection=Connection::getDefaultConnection()->connect();
            self::$defaultQuery->setConnection($connection);
        }
        self::$query = self::$defaultQuery;
        
    }

    /**
     * 获取查询结果的一列，并作为类对象
     *
     * @param string $class 类名
     * @return array|false 查询成功则返回一列查询结果，否则返回false
     */
    public function fetchObject(string $class='stdClass')
    {
        return $this->rawQuery->fetchObject($class);
    }
    
    /**
     * 获取全部的查询结果
     *
     * @param integer $fetch_style 结果集形式
     * @return array|false 查询成功则返回查询结果，否则返回false
     */
    public function fetchAll(int $fetchStyle = PDO::FETCH_ASSOC)
    {
        return $this->rawQuery->fetchAll($fetchStyle);
    }
    
    /**
     * 运行SQL语句
     *
     * @return integer 返回影响的数据行数目
     */
    public function exec():int
    {
        return $this->rawQuery->exec();
    }

    /**
     * 生成一个数据输入值
     *
     * @param string $name 列名
     * @param [type] $value 值
     * @param integer $type 类型
     * @return InputValue 输入变量类
     */
    public static function value(string $name, $value, int $type=PDO::PARAM_STR):InputValue
    {
        return new InputValue($name, $value, $type);
    }

    /**
     * SQL语句模板绑定值
     *
     * @param array $values
     * @return SQLQuery
     */
    public function values(array $values)
    {
        $this->rawQuery->values($values);
        return $this;
    }

    /**
     * 生成一条查询语句
     *
     * @param string $query 查询语句模板
     * @param array $array 查询语句模板值
     * @return SQLQuery
     */
    public function query(string $query, array $array=[],bool $scroll=false)
    {
        $this->rawQuery->query($query,$array,$scroll);
        return $this;
    }
    
    /**
     * 切换使用的数据表
     *
     * @param string $name
     * @return SQLQuery
     */
    public function use(string $name=null)
    {
        $this->rawQuery->use($name);
        return $this;
    }
    
    
    /**
     * 获取语句查询错误
     *
     * @return bool|array 错误结果,false获取失败
     */
    public function error()
    {
        return $this->rawQuery->error();
    }

    /**
     * 获取语句查询错误编号
     *
     * @return bool|array 语句编号结错误结果,false获取失败果
     */
    public function erron()
    {
        return $this->rawQuery->erron();
    }

    /**
     * 获取最后一次插入的主键ID（用于自增值
     *
     * @param string $name
     * @return false|int false则获取失败，整数则获取成功
     */
    public function lastInsertId(string $name=null)
    {
        if (is_null($name)) {
            return $this->rawQuery->lastInsertId();
        } else {
            return $this->rawQuery->lastInsertId($name);
        }
        return false;
    }

    /**
     * 事务系列，开启事务
     *
     * @return any
     */
    public static function begin()
    {
        return self::beginTransaction();
    }

    /**
     * 事务系列，开启事务
     *
     * @return any
     */
    public static function beginTransaction()
    {
        self::$query->beginTransaction();
    }

    /**
     * 事务系列，提交事务
     *
     * @return any
     */
    public static function commit()
    {
        self::$query->commit();
    }

    /**
     * 事务系列，撤销事务
     *
     * @return any
     */
    public static function rollBack()
    {
        self::$query->rollBack();
    }

    public function quote($string)
    {
        return self::$query->quote($string);
    }

    public function arrayQuote(array $array)
    {
        return self::$query->arrayQuote($array);
    }

    protected static function _connect(Connection $connection=null)
    {
        // 链接默认数据库
        if (is_null($connection)) {
            if (is_null(self::$defaultQuery) ) {
                if (is_null(self::$query)) {
                    self::$defaultQuery = new RawQuery(Connection::getDefaultConnection()->connect());
                    self::$query = self::$defaultQuery;
                }
                else{
                     self::$defaultQuery = self::$query;
                }
            }
        } else {
            if (!$connection->isConnected()) {
                $connection->connect();
            }
            self::$query =new RawQuery($connection);
        }
    }
    
    /**
     * 添加列处理类
     *
     * @param [type] $object
     * @return void
     */
    public function object($object)
    {
        $this->rawQuery->object($object);
        return $this;
    }
}
