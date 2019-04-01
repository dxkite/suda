<?php
namespace suda\framework\route\uri\parameter;

use suda\framework\route\uri\parameter\Parameter;

/**
 * 匹配float参数
 */
class FloatParameter extends Parameter
{
    protected static $name = 'float';


    public function __construct(string $extra)
    {
        $default = $this->getCommonDefault($extra);
        if (strlen($default) > 0) {
            $this->default = floatval($default);
        }
    }
 
    public function unpackValue(string $matched)
    {
        return floatval($matched);
    }

    /**
     * 获取匹配字符串
     *
     * @return string
     */
    public function getMatch():string
    {
        return '(\d+\.\d+)';
    }
}
