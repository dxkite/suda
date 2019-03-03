<?php
namespace suda\framework\route\uri\parameter;

use suda\framework\route\uri\parameter\Parameter;

/**
 * 匹配 string 参数
 */
class StringParameter extends Parameter {

    protected static $name = 'string';
    
    public function __construct(string $extra) {
        $this->default = $this->getCommonDefault($extra);
    }
 
    public function unpackValue(string $matched) {
        return urldecode($matched);
    }

    public function packValue(string $matched) {
        return urlencode($matched);
    }
    
    /**
     * 获取匹配字符串
     *
     * @return string
     */
    public function getMatch():string {
        return '([^\/]+)';
    }
}