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
namespace suda\tool;

class EchoValue extends Value
{
    public function __get(string $name)
    {
        $value=parent::__get($name);
        return is_null($value)?$name:$value;
    }

    public function _(string $name)
    {
        return call_user_func_array([$this, $name], array_slice(func_get_args(), 1));
    }

    public function __call(string $name, $args)
    {
        // 获取值
        $value=parent::__get($name);
        $args[0]=is_null($value)?($args[0] ?? $name):$value;
        return parent::__call($name, $args);
    }
}
