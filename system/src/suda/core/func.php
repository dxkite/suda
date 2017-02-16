<?php

// 语言翻译
function _T(string $message){
    return call_user_func_array('suda\template\Language::trans',func_get_args());
}

// 获取debug记录
function _D(){
    return new suda\core\Debug;
}

// 获取配置
function conf(string $name,$default=null){
    return Config::get($name,$default);
}

// 使用命名空间
function use_namespace(string $namespace){
    return suda\core\System::setNamespace($namespace);
}