<?php

function _T(string $message){
    return call_user_func_array('suda\template\Language::trans',func_get_args());
}

function conf(string $name,$default=null){
    return Config::get($name,$default);
}