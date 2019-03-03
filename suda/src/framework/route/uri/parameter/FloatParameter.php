<?php
namespace suda\framework\route\uri\parameter;

use suda\framework\route\uri\parameter\Parameter;

/**
 * 匹配float参数
 */
class FloatParameter extends Parameter {

    protected static $name = 'float';


    public function __construct(string $extra) {
        $this->default = floatval($this->getCommonDefault($extra));
    }
 
    public function unpackValue(string $matched) {
        return floatval($matched);
    }

    /**
     * 获取匹配字符串
     *
     * @return string
     */
    public function getMatch():string {
        return '(\d+\.\d+)';
    }
}