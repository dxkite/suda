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

/**
 * 根据文件类型获取MIME描述
 * 其中文本硬编码为UTF-8
 * 
 * @param string $type 文件类型
 * @return string 获取的MIME字符串
 */
function mime(string $type)
{
    return suda\core\Response::mime($type);
}

/**
 * 语言翻译，I18N支持，依赖locales文件夹下的文件
 * 
 * ## 使用例子
 * 
 * ```php
 *  echo __('text is %d',1);
 * ```
 * @param string $message 输入的信息，支持不定参数，类似printf
 * @return string 翻译过的字符串，如果没有既定翻译，则原样输出
 */
function __(string $message)
{
    return call_user_func_array('suda\core\Locale::_', func_get_args());
}

/**
 * 获取debug对象
 *
 * @return suda\core\Debug 调试用对象实例
 */
function debug() : suda\core\Debug
{
    return new suda\core\Debug;
}

/**
 * 获取配置信息
 *
 * @param string $name 配置名
 * @param [type] $default 获取失败时的值
 * @return void
 */
function conf(string $name, $default=null)
{
    return suda\core\Config::get($name, $default);
}

/**
 * 使用命名空间
 *
 * @param string $namespace 命名空间
 * @return void
 */
function use_namespace(string $namespace)
{
    return suda\core\Autoloader::setNamespace($namespace);
}

/**
 * 根据路由名获取URL
 * 如果第一个参数为字符串，则将字符串作为路由名称，第二个参数作为路由的值获取组合后的路由
 * 如果第一个参数为数组，则获取正在运行的路由的URL，参数使用第一个参数
 *
 * @param [type] $name 路由名称
 * @param [type] $values 路由的值
 * @return void
 */
function u($name=null, $values=null)
{
    if (is_string($name)) {
        if (!is_array($values)) {
            $args=func_get_args();
            array_shift($args);
            $values=suda\core\Router::getInstance()->buildUrlArgs($name, $args);
        }
        return suda\core\Router::getInstance()->buildUrl($name, $values);
    } elseif (is_array($name)) {
        return suda\core\Router::getInstance()->buildUrl(suda\core\Response::$name, $name);
    } else {
        return suda\core\Router::getInstance()->buildUrl(suda\core\Response::$name);
    }
}

/**
 * 根据模块名称获取资源URL
 *
 * @param string $module 资源所在模块
 * @param string $path 资源所在的路径
 * @param boolean $static 资源是否为静态资源
 * @return string 获取到的资源URL
 */
function assets(string $module, string $path, bool $static=true)
{
    if ($static) {
        return suda\template\Manager::assetServer(suda\template\Manager::getStaticAssetPath($module)).'/'.ltrim($path, '/');
    } else {
        return suda\template\Manager::assetServer(suda\template\Manager::getDynamicAssetPath($path, $module));
    }
}

/**
 * 导入PHP文件
 *
 * @param string $path 导入文件的路径
 * @return bool 是否导入成功
 */
function import(string $path)
{
    return suda\core\Autoloader::import($path);
}

/**
 * 初始化资源
 *
 * @param array $modules 需要初始化资源的模块，如果设置为NULL则初始化全部资源
 * @return array 资源初始化结果
 */
function init_resource(array $modules=null)
{
    return $modules?suda\template\Manager::initResource($modules):suda\template\Manager::initResource();
}

/**
 * 获取当运行的APP单例对象
 *
 * @return any 获取的APP单例对象
 */
function app() 
{
    return suda\core\System::getAppInstance();
}

/**
 * 获取当运行的路由单例对象
 *
 * @return suda\core\Router 获取的路由单例对象
 */
function router()
{
    return suda\core\Router::getInstance();
}

/**
 * 获取当运行的请求的单例对象
 *
 * @return suda\core\Request 获取的请求单例对象
 */
function request()
{
    return suda\core\Request::getInstance();
}

/**
 * 获取当运行的请求的单例对象
 *
 * @return suda\core\Request 获取的请求单例对象
 */
function hook()
{
    return new suda\core\Hook;
}

/**
 * 获取当运行的请求的单例对象
 *
 * @return suda\core\Request 获取的请求单例对象
 */
function cookie()
{
    return new suda\core\Cookie;
}

/**
 * 获取一个缓存对象
 *
 * @return suda\core\Cache 获取的缓存对象
 */
function cache()
{
    return new suda\core\Cache;
}

/**
 * 获取一个储存对象
 *
 * @return suda\core\Storage 获取的储存对象
 */
function storage()
{
    return new suda\core\Storage;
}

/**
 * 获取一个配置对象
 *
 * @return suda\core\Config 获取的配置对象
 */
function config()
{
    return new suda\core\Config;
}

/**
 * 新建一个命令对象，命令对象可以是一个字符串或者一个数组，也可以是一个匿名包对象
 * 还可以是一个标准可调用的格式的字符串
 * 
 * ### 静态方法
 * ```
 * 类名::方法名
 * ```
 * 
 * ### 动态方法
 *
 * ```
 * 类名->方法名
 * ``` 
 * 
 * @param [type] $command 可调用的对象
 * @param array $params 调用时的参数
 * @return suda\tool\Command 可调用命令对象
 */
function cmd($command, array $params=[])
{
    return new suda\tool\Command($command, $params);
}

/**
 * 获取类名，将JAVA包式的类名转化为 PHP的标准类名
 *
 * @param string $name 需要转换的类名
 * @return string 转换后的类名
 */
function class_name(string $name)
{
    return suda\core\Autoloader::realName($name);
}

/**
 * 获取数据表对象，该对象需要在module.json文件中注册过
 * 如：
 * ```json
 * {
 *    "table":{
 *        "user":"classNameOfUserTable" 
 *     } 
 * }
 * ```
 *
 * @param string $tableName 数据表名
 * @return Table 获取的表对象
 */
function table(string $tableName)
{
    return suda\archive\TableInstance::new($tableName);
}
