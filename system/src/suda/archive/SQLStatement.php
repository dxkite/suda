<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.2.0 or newer
 *
 * Copyright (c)  2017-2018 DXkite
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
use suda\archive\creator\InputValue;

/**
 * 数据库查询语句接口
 *
 */
interface SQLStatement
{
    public function getConnection();
    public function setConnection(Connection $connection);
    
    /**
     * 获取查询结果的一列
     *
     * @param integer $fetch_style 结果集形式
     * @return array|false 查询成功则返回一列查询结果，否则返回false
     */
    public function fetch(int $fetch_style = PDO::FETCH_ASSOC);

    /**
     * 获取查询结果的一列，并作为类对象
     *
     * @param string $class 类名
     * @return array|false 查询成功则返回一列查询结果，否则返回false
     */
    public function fetchObject(string $class='stdClass');
    
    /**
     * 获取全部的查询结果
     *
     * @param integer $fetch_style 结果集形式
     * @return array|false 查询成功则返回查询结果，否则返回false
     */
    public function fetchAll(int $fetch_style = PDO::FETCH_ASSOC);
    
    /**
     * 运行SQL语句
     *
     * @return integer 返回影响的数据行数目
     */
    public function exec():int;

    /**
     * 生成一个数据输入值
     *
     * @param string $name 列名
     * @param [type] $value 值
     * @param integer $type 类型
     * @return InputValue 输入变量类
     */
    public static function value(string $name, $value, int $type=PDO::PARAM_STR):InputValue;

    /**
     * SQL语句模板绑定值
     *
     * @param array $values
     * @return SQLStatement
     */
    public function values(array $values);

    /**
     * 生成一条查询语句
     *
     * @param string $query 查询语句模板
     * @param array $array 查询语句模板值
     * @return SQLStatement
     */
    public function query(string $query, array $array=[], bool $scroll=false);
    /**
     * 切换使用的数据表
     *
     * @param string $name
     * @return SQLStatement
     */
    public function use(string $name=null);
    /**
     * 获取语句查询错误
     *
     * @return bool|array 错误结果,false获取失败
     */
    public function error();

    /**
     * 获取语句查询错误编号
     *
     * @return bool|array 语句编号结错误结果,false获取失败果
     */
    public function erron();
    
    /**
     * 添加列处理类
     *
     * @param object $object
     * @return SQLStatement
     */
    public function object(object $object);
}
