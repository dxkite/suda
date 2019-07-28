<?php
namespace suda\framework\route\uri\parameter;

/**
 * 匹配 string 参数
 */
class StringParameter extends Parameter
{
    protected static $name = 'string';
    
    public function __construct(string $extra)
    {
        parent::__construct($extra);
        if (strlen($extra) > 0) {
            $this->default = $this->getCommonDefault($extra);
        }
    }

    /**
     * @param string $matched
     * @return string
     */
    public function unpackValue(string $matched)
    {
        return urldecode($matched);
    }

    /**
     * @param string|null $matched
     * @return string|null
     */
    public function packValue(?string $matched)
    {
        return $matched ? urlencode($matched): '';
    }
    
    /**
     * 获取匹配字符串
     *
     * @return string
     */
    public function getMatch():string
    {
        return '([^\/]+)';
    }
}
