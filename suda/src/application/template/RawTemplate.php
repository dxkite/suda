<?php

namespace suda\application\template;

use Exception;
use function extract;
use suda\framework\runnable\Runnable;
use suda\framework\arrayobject\ArrayDotAccess;
use suda\application\exception\NoTemplateFoundException;

/**
 * 原始PHP模板
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

    /**
     * 父模版
     *
     * @var self|null
     */
    protected $parent = null;

    /**
     * 模板钩子
     *
     * @var array
     */
    protected $hooks = [];

    /**
     * 继承的模板
     *
     * @var string|null
     */
    protected $extend = null;

    /**
     * RawTemplate constructor.
     * @param string $path
     * @param array $value
     */
    public function __construct(string $path, array $value = [])
    {
        $this->path = $path;
        $this->value = $value;
    }

    /**
     * 单个设置值
     *
     * @param string $name
     * @param mixed $value
     * @return $this
     */
    public function set(string $name, $value)
    {
        $this->value = ArrayDotAccess::set($this->value, $name, $value);
        return $this;
    }

    /**
     * 直接压入值
     *
     * @param array $values
     * @return $this
     */
    public function assign(array $values)
    {
        $this->value = array_merge($this->value, $values);
        return $this;
    }


    /**
     * 创建模板获取值
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
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
     *
     * @param string $name
     * @return boolean
     */
    public function has(string $name)
    {
        return ArrayDotAccess::exist($this->value, $name);
    }

    /**
     * 获取模板路径
     * @return string
     */
    protected function getPath()
    {
        return $this->path;
    }

    /**
     * 调用某函数
     *
     * @param string $name
     * @param mixed ...$args
     * @return mixed
     */
    public function call(string $name, ...$args)
    {
        if (func_num_args() > 1) {
            return (new Runnable($name))->run($this, ...$args);
        }
        return (new Runnable($name))->apply([$this]);
    }


    /**
     * @param string $name
     * @param $callback
     */
    public function insert(string $name, $callback)
    {
        // 存在父模板
        if ($this->parent) {
            $this->parent->insert($name, $callback);
        } else {
            // 添加回调钩子
            $this->hooks[$name][] = new Runnable($callback);
        }
    }

    /**
     * @param string $name
     */
    public function exec(string $name)
    {
        try {
            // 存在父模板
            if ($this->parent) {
                $this->parent->exec($name);
            } elseif (isset($this->hooks[$name])) {
                foreach ($this->hooks[$name] as $hook) {
                    $hook->run();
                }
            }
        } catch (Exception $e) {
            echo '<div style="color:red">' . $e->getMessage() . '</div>';
            return;
        }
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getRenderedString()
    {
        if (file_exists($this->getPath())) {
            ob_start();
            extract($this->value);
            include $this->getPath();
            if ($this->extend) {
                $this->include($this->extend);
            }
            return ob_get_clean() ?: '';
        }
        throw new NoTemplateFoundException(
            'missing dest at ' . $this->getPath(),
            E_USER_ERROR,
            $this->getPath(),
            1
        );
    }

    /**
     * 获取渲染后的字符串
     * @ignore-dump
     * @throws Exception
     * @return string
     */
    public function render()
    {
        $content = $this->getRenderedString();
        $content = trim($content);
        return $content;
    }

    /**
     * 创建模板
     * @param $template
     * @return $this
     */
    public function parent($template)
    {
        $this->parent = $template;
        return $this;
    }


    /**
     * @param string $name
     * @return $this
     */
    public function extend(string $name)
    {
        $this->extend = $name;
        return $this;
    }

    /**
     * @param string $path
     * @throws Exception
     */
    public function include(string $path)
    {
        $included = new self($path, $this->value);
        $included->parent = $this;
        echo $included->getRenderedString();
    }
}
