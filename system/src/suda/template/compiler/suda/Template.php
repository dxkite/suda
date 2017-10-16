<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.0.0 or newer
 * 
 * Copyright (c)  2017 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.4
 */
namespace suda\template\compiler\suda;

use suda\tool\ArrayHelper;
use suda\tool\Command;
use suda\core\Response;
use suda\core\Router;
use suda\core\Hook;
use suda\exception\CommandException;

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
    // 所在模块
    protected $module=null;
    // 渲染堆栈
    protected static $render=[];
    
    public function __construct()
    {
        preg_match('/^((?:[a-zA-Z0-9_-]+\/)?[a-zA-Z0-9_-]+)(?::([^:]+))?(?::(.+))?$/', $this->name, $match);
        $this->module= isset($match[3])?$match[1].(isset($match[2])?':'.$match[2]:''):$match[1];
    }
    /**
    * 渲染页面
    */
    public function render()
    {
        debug()->trace('echo '.$this->name);
        // 渲染页面
        $content=self::getRenderedString();
        // 计算输出页面长度
        if (conf('app.calcContentLength', !conf('debug'))) {
            $length=strlen($content);
            // 输出页面长度
            $this->response->setHeader('Content-Length:'.$length);
        }
        $this->response->type('html');
        if (conf('app.etag', !conf('debug'))  && $this->response->etag(md5($content))) {
        } else {
            echo $content;
        }
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
        debug()->time('render '.$this->name);
        self::_render_start();
        $this->_render_template();
        $content=self::_render_end();
        debug()->timeEnd('render '.$this->name);
        return $content;
    }
    
    protected function _render_start()
    {
        array_push(self::$render, $this->name);
        debug()->trace('start render', $this->name);
        ob_start();
    }

    protected function _render_end()
    {
        array_pop(self::$render);
        $content=ob_get_clean();
        debug()->trace('free render ['.strlen($content).']', $this->name);
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

    public function execGloHook(string $name)
    {
        try {
            Hook::exec($name, [$this]);
        } catch (CommandException $e) {
            echo '<div style="color:red" title="'.__('can\'t run global hook %s', $e->getCmd()).'">{:'.$name.'}</div>';
            return;
        }
        if (conf('app.showPageGlobalHook', false)) {
            echo '<div style="color:green" title="'.__('global hook point').'">{:'.$name.'}</div>';
        }
    }

    public function exec(string $name)
    {
        try {
            // 存在父模板
            if ($this->parent) {
                $this->parent->exec($name);
            } elseif (isset($this->hooks[$name])) {
                foreach ($this->hooks[$name] as $hook) {
                    $hook->exec();
                }
            }
        } catch (CommandException $e) {
            echo '<div style="color:red" title="'.__('can\'t run page hook %s %s', $e->getCmd(), $e->getMessage()).'">{:'.$e->getCmd().'}</div>';
            return;
        }
        if (conf('app.showPageHook', false)) {
            echo '<div style="color:green" title="'.__('page hook point').'">{#'.$name.'}</div>';
        }
    }

    public function name()
    {
        return $this->name;
    }
    
    public function responseName()
    {
        return $this->response->getName();
    }

    public function isMe(string $name)
    {
        return $this->response->getName()==Router::getInstance()->getRouterFullName($name);
    }

    public function boolecho($values, string $true, string $false='')
    {
        return is_bool($values) && $values ?$true:$false;
    }

    public function getModule()
    {
        return $this->module;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getResponse()
    {
        return $this->response;
    }
}
