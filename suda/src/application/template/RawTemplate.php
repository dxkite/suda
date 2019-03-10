<?php
namespace suda\application\template;

use suda\framework\arrayobject\ArrayDotAccess;
use suda\application\exception\MissingTemplateException;

/**
 * 应用程序
 */
class RawTemplate
{
    /**
     * 路径
     *
     * @var string
     */
    protected $path;

    /**
     * 模板值
     *
     * @var array
     */
    protected $value;

    public function __construct(string $path, array $value = [])
    {
        $this->path = $path;
        $this->value = $value;
    }

    /**
    * 单个设置值
    */
    public function set(string $name, $value)
    {
        $this->value = ArrayDotAccess::set($this->value, $name, $value);
        return $this;
    }

    /**
    * 直接压入值
    */
    public function assign(array $values)
    {
        $this->value = array_merge($this->value, $values);
        return $this;
    }


    /**
     * 创建模板获取值
     */
    public function get(string $name = null, $default = null)
    {
        if (null === $name) {
            return $this->value;
        }
        return ArrayDotAccess::get($this->value, $name, $default ?? $name);
    }

    /**
     * 检测值
     */
    public function has(string $name)
    {
        return ArrayDotAccess::exist($this->value, $name);
    }

    public function __toString()
    {
        if (file_exists($this->path)) {
            ob_start();
            \extract($this->value);
            require $this->path;
            return \ob_get_clean();
        }
        throw new MissingTemplateException($this->path);
    }
}
