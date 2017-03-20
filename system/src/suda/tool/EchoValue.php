<?php
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
