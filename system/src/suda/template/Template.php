<?php
namespace suda\template;

use suda\tool\ArrayHelper;
use suda\tool\Command;
use suda\core\Response;

abstract class Template
{
    /**
    * 模板的值
    */
    protected $value=[];
    /**
    * 模板所属于的响应
    */
    protected $response=null;
    protected $name=null;
    protected $parent=null;
    protected $hooks=[];
    protected static $render=[];
    /**
    * 渲染页面
    */
    public function render()
    {
        _D()->trace('echo '.$this->name);
        // 渲染页面
        $content=self::getRenderedString();
        // 计算输出页面长度
        if (conf('app.calcContentLength', !DEBUG)) {
            $length=strlen($content);
            // 输出页面长度
            $this->response->setHeader('Content-Length:'.$length);
        }
        $this->response->type('html');
        if (conf('app.etag', !conf('debug')) && $length>0) {
            $this->response->etag(md5($content));
        }
        echo $content;
        return $this;
    }
    
    /**
    * 渲染语句
    */
    abstract protected function _render_template();

    /**
    * 获取渲染后的字符串
    */
    public function getRenderedString()
    {
        _D()->time('render '.$this->name);
        self::_render_start();
        $this->_render_template();
        $content=self::_render_end();
        _D()->timeEnd('render '.$this->name);
        return $content;
    }
    protected function _render_start()
    {
        array_push(self::$render, $this->name);
        _D()->trace('start render', $this->name);
        ob_start();
    }

    protected function _render_end()
    {
        array_pop(self::$render);
        $content=ob_get_clean();
        _D()->trace('free render ['.strlen($content).']', $this->name);
        return $content;
    }
    /**
    * 获取当前模板的字符串
    */
    public function __toString()
    {
        return self::getRenderedString();
    }

    public function getRenderStack()
    {
        return self::$render;
    }
    /**
    * 单个设置值
    */
    public function set(string $name, $value)
    {
        $this->value=ArrayHelper::set($this->value, $name, $value);
        return $this;
    }

    /**
    * 直接压入值
    */
    public function assign(array $values)
    {
        $this->value=array_merge($this->value, $values);
        return $this;
    }

    /**
    * 创建模板
    */
    public function parent(Template $template)
    {
        $this->parent=$template;
        $this->response=$this->parent->response;
        return $this;
    }
    /**
    * 创建模板
    */
    public function response(Response $response)
    {
        $this->response=$response;
        return $this;
    }
    /**
    * 创建模板获取值
    */
    public function get(string $name, $default=null)
    {
        $fmt= ArrayHelper::get($this->value, $name, $default ?? $name);
        if (func_num_args() > 2) {
            $args=array_slice(func_get_args(), 2);
            array_unshift($args, $fmt);
            return call_user_func_array('sprintf', $args);
        }
        return $fmt;
    }

    public function data(string $name)
    {
        return (new Command($name))->exec([$this]);
    }
    
    public function hook(string $name, $callback)
    {
        // 存在父模板
        if ($this->parent) {
            return $this->parent->hook($name, $callback);
        } else {
            $this->hooks[$name][]=(new Command($callback))->name($name);
        }
    }

    public function exec(string $name)
    {
        // 存在父模板
        if ($this->parent) {
            $this->parent->exec($name);
        } elseif (isset($this->hooks[$name])) {
            foreach ($this->hooks[$name] as $hook) {
                $hook->exec();
            }
        }
    }
}
