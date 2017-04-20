<?php
namespace suda\template;

abstract class Template
{
    protected $value=[];

    /**
    * 渲染语句
    */
    abstract public function render();

    /**
    * 获取渲染后的字符串
    */
    public function getRenderedString(){
        ob_start();
        $this->render();
        return ob_get_clean();
    }

    /**
    * 获取当前模板的字符串
    */
    public function __toString(){
        return self::getRenderedString();
    }
}
