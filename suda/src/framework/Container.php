<?php
namespace suda\framework;

use Closure;
use suda\framework\runnable\Runnable;

class Container
{
    /**
     * 对象容器
     *
     * @var object[]
     */
    protected $instance;

    /**
     * 添加实例
     *
     * @param string $name
     * @param object|Closure|\suda\framework\runnable\Runnable $instance
     * @return self
     */
    public function set(string $name, $instance)
    {
        $this->instance[$name] = $instance;
        return $this;
    }

    /**
     * 设置单例
     *
     * @param string $name
     * @param string|Closure|\suda\framework\runnable\Runnable|object $class
     * @return self
     */
    public function setSingle(string $name, $class)
    {
        if (\is_string($class)) {
            return $this->set($name, new $class);
        } elseif ($class instanceof Runnable || $class instanceof Closure) {
            return $this->set($name, $class());
        } else {
            return $this->set($name, $class);
        }
    }

    /**
     * 获取一个类的实例
     * @param  string $alias 类名
     * @return object
     */
    public function get(string $name = '')
    {
        if (array_key_exists($name, $this->instance)) {
            if (\is_object($this->instance[$name])) {
                return $this->instance[$name];
            }
            return $this->instance[$name]();
        }
        throw new \Exception('instance no found: '.$name);
    }
}
