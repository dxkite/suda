<?php
namespace suda\framework\route\uri\parameter;

use suda\framework\route\uri\parameter\Parameter;

/**
 * 匹配 string 参数
 */
class UrlParameter extends StringParameter
{
    protected static $name = 'url';
    
    /**
     * 获取匹配字符串
     *
     * @return string
     */
    public function getMatch():string
    {
        return '(.+)';
    }
}
