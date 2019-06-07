<?php
namespace suda\database\middleware;

/**
 * 中间件
 * 处理数据输出输出
 */
interface Middleware
{
    /**
     * 处理输入数据
     *
     * @param string $name
     * @param mixed $data
     * @return mixed
     */
    public function input(string $name, $data);

    /**
     * 输入名处理
     *
     * @param string $name
     * @return string
     */
    public function inputName(string $name):string;

    /**
     * 处理输出数据
     *
     * @param string $name
     * @param mixed $data
     * @return mixed
     */
    public function output(string $name, $data);

    /**
     * 输出名处理
     *
     * @param string $name
     * @return string
     */
    public function outputName(string $name):string;

    /**
     * 对输出列进行处理
     *
     * @param mixed $row
     * @return mixed
     */
    public function outputRow($row);
}
