<?php
namespace suda\template;

use suda\tool\{ArrayHelper};
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

    /**
    * 渲染页面
    */
    public function render()
    {
        // 渲染页面
        $cotent=self::getRenderedString();
        // 输出页面
        $this->response->setHeader('Content-Length:'.strlen($cotent));
        $this->response->type('html');
        if (conf('app.etag', conf('debug'))) {
            $this->response->etag(md5($cotent));
        }
        echo $cotent;
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
        ob_start();
        $this->_render_template();
        $content=ob_get_clean();
        _D()->timeEnd('render '.$this->name);
        return $content; 
    }

    /**
    * 获取当前模板的字符串
    */
    public function __toString()
    {
        return self::getRenderedString();
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
    public function setResponse(Response $response)
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
}
