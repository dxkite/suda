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


function mime(string $type)
{
    return suda\core\Response::mime($type);
}
// 语言翻译
function __(string $message)
{
    return call_user_func_array('suda\core\Locale::_', func_get_args());
}

// 获取debug记录
function debug()
{
    return new suda\core\Debug;
}

// 获取配置
function conf(string $name, $default=null)
{
    return suda\core\Config::get($name, $default);
}

// 使用命名空间
function use_namespace(string $namespace)
{
    return suda\core\Autoloader::setNamespace($namespace);
}

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

function assets(string $module, string $path, bool $static=true)
{
    if ($static) {
        return suda\template\Manager::assetServer(suda\template\Manager::getStaticAssetPath($module)).'/'.ltrim($path, '/');
    } else {
        return suda\template\Manager::assetServer(suda\template\Manager::getDynamicAssetPath($path,$module));
    }
}

function import(string $path)
{
    return suda\core\Autoloader::import($path);
}


function init_resource(array $modules=null)
{
    return $modules?suda\template\Manager::initResource($modules):suda\template\Manager::initResource();
}

function app() {
    return suda\core\System::getApplication();
}

function router() {
    return suda\core\Router::getInstance();
}

function request() {
    return suda\core\Request::getInstance();
}

function hook() {
    return new suda\core\Hook;
}

function cookie() {
    return new suda\core\Cookie;
}

function cache() {
    return new suda\core\Cache;
}

function config() {
    return new suda\core\Config;
}