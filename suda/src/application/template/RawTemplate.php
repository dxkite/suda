<?php
namespace suda\application\template;

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
