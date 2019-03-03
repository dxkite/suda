<?php
namespace suda\framework\route\uri\parameter;

use suda\framework\route\uri\parameter\Parameter;

/**
 * 匹配int参数
 */
class IntParameter extends Parameter
{
    protected static $name = 'int';


    public function __construct(string $extra)
    {
        $this->default = intval($this->getCommonDefault($extra));
    }
 
    public function unpackValue(string $matched)
    {
        return intval($matched);
    }

    /**
     * 获取匹配字符串
     *
     * @return string
     */
    public function getMatch():string
    {
        return '(\d+)';
    }
}
