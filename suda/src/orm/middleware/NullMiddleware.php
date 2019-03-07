<?php
namespace suda\orm\middleware;

use suda\orm\TableStruct;

/**
 * 中间件
 * 处理数据输出输出
 */
class NullMiddleware 
{
    /**
     * 处理输入数据
     *
     * @param string $name
     * @param mixed $data
     * @return mixed
     */
    public function input(string $name, $data) {
        return $data;
    }

    /**
     * 处理输出数据
     *
     * @param string $name
     * @param mixed $data
     * @return mixed
     */
    public function output(string $name, $data) {
        return $data;
    }

    /**
     * 对输出列进行处理
     *
     * @param TableStruct $row
     * @return TableStruct
     */
    public function outputRow(TableStruct $row) {
        return $row;
    }
}
