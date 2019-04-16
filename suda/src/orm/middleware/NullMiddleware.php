<?php
namespace suda\orm\middleware;

use suda\orm\TableStruct;
use suda\orm\middleware\Middleware;

/**
 * 中间件
 * 处理数据输出输出
 */
class NullMiddleware implements Middleware
{
    /**
     * 处理输入数据
     *
     * @param string $name
     * @param mixed $data
     * @return mixed
     */
    public function input(string $name, $data)
    {
        return $data;
    }

    /**
     * 处理输出数据
     *
     * @param string $name
     * @param mixed $data
     * @return mixed
     */
    public function output(string $name, $data)
    {
        return $data;
    }

    /**
     * 处理输入字段名
     */
    public function inputName(string $name):string
    {
        return $name;
    }

    /**
     * 处理输出字段名
     *
     * @param string $name
     * @param mixed $data
     * @return mixed
     */
    public function outputName(string $name):string
    {
        return $name;
    }

    /**
     * 对输出列进行处理
     *
     * @param mixed $row
     * @return mixed
     */
    public function outputRow($row)
    {
        return $row;
    }
}
