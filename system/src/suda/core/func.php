<?php

spl_autoload_register(function (string $classname) {
    $classfile=preg_replace('/[\\\\]+/', DIRECTORY_SEPARATOR, $classname);
        // 搜索路径
        foreach (System::getIncludePath() as $include_path) {
            if (file_exists($path=$include_path.DIRECTORY_SEPARATOR.$classname.'.php')) {
                if (!class_exists($classname)) {
                    require_once $path;
                }
            } else {
                // 添加命名空间
                foreach (System::getNamespace() as $namespace) {
                    if (file_exists($path=$include_path.DIRECTORY_SEPARATOR.$namespace.DIRECTORY_SEPARATOR.$classname.'.php')) {
                        // var_dump(get_included_files());
                        // var_dump(class_exists($classname),$classname);
                        // 最简类名
                        if (!class_exists($classname)) {
                            class_alias($namespace.'\\'.$classname, $classname);
                        }
                        require_once $path;
                    }
                }
            }
        }
});


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

function _I(string $name,array $values=[]){
    return suda\core\Router::getInstance()->buildUrl($name,$values);
}