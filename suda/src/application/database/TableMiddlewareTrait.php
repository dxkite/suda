<?php
namespace suda\application\database;

use suda\orm\TableStruct;

trait TableMiddlewareTrait
{
    /**
     * 处理输入数据
     *
     * @param string $name
     * @param mixed $data
     * @return mixed
     */
    public function input(string $name, $data) {
        $methodName='_input'.ucfirst($name).'Field';
        if (\method_exists($this, $methodName)) {
            return $this->$methodName($data);
        }
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
        $methodName='_output'.ucfirst($name).'Field';
        if (\method_exists($this, $methodName)) {
            return $this->$methodName($data);
        }
        return $data;
    }

    /**
     * 对输出列进行处理
     *
     * @param mixed $row
     * @return mixed
     */
    public function outputRow($row) {
        $methodName='_outputDataFilter';
        if (\method_exists($this, $methodName)) {
            return $this->$methodName($row);
        }
        return $row;
    }
}
