<?php
/**
 * Suda FrameWork
 *
 * An open source application development framework for PHP 7.2.0 or newer
 *
 * Copyright (c)  2017-2018 DXkite
 *
 * @category   PHP FrameWork
 * @package    Suda
 * @copyright  Copyright (c) DXkite
 * @license    MIT
 * @link       https://github.com/DXkite/suda
 * @version    since 1.2.4
 */
namespace suda\core;

use suda\tool\Command;

/**
 * 系统钩子，监听系统内部一些操作并载入一些自定义行为
 */
class Hook
{
    protected static $hooks=[];

    public static function loadConfig(string $path, ?string $module=null)
    {
        $hooks=Config::loadConfig($path, $module);
        debug()->trace($path);
        self::load($hooks?:[]);
    }

    public static function load(array $arrays)
    {
        self::$hooks=array_merge_recursive(self::$hooks, $arrays);
    }

    /**
     * 注册一条命令
     *
     * @param string $name
     * @param mixed $command
     * @return void
     */
    public static function listen(string $name, $command)
    {
        self::add($name, $command);
    }

    /**
     * 注册一条命令
     *
     * @param string $name
     * @param mixed $command
     * @return void
     */
    public static function register(string $name, $command)
    {
        self::add($name, $command);
    }

    /**
     * 添加命令到底部
     *
     * @param string $name
     * @param mixed $command
     * @return void
     */
    public static function add(string $name, $command)
    {
        self::$hooks[$name][]=$command;
    }

    /**
     * 添加命令到顶部
     *
     * @param string $name
     * @param mixed $command
     * @return void
     */
    public static function addTop(string $name, $command)
    {
        if (\array_key_exists($name, self::$hooks)  && is_array(self::$hooks[$name])) {
            array_unshift(self::$hooks[$name], $command);
        } else {
            self::add($name, $command);
        }
    }

    /**
     * 移除一条命令
     *
     * @param string $name
     * @param mixed $remove
     * @return void
     */
    public static function remove(string $name, $remove)
    {
        if (\array_key_exists($name, self::$hooks)  && is_array(self::$hooks[$name])) {
            foreach (self::$hooks[$name] as $key=>$command) {
                if ($command === $remove) {
                    unset(self::$hooks[$name][$key]);
                }
            }
        }
    }

    #===================================================================
    #       命令运行
    #===================================================================

    /**
     * 运行所有命令
     *
     * @param string $name
     * @param array $args
     * @return void
     */
    public static function exec(string $name, array $args=[])
    {
        debug()->trace($name);
        if (\array_key_exists($name, self::$hooks) && is_array(self::$hooks[$name])) {
            foreach (self::$hooks[$name] as $command) {
                self::call($command, $args);
            }
        }
    }

    /**
     * 运行，遇到返回指定条件则停止并返回true
     *
     * @param string $name
     * @param array $args
     * @param boolean $condition
     * @return boolean
     */
    public static function execIf(string $name, array $args=[], $condition = true):bool
    {
        debug()->trace($name);
        if (\array_key_exists($name, self::$hooks) && is_array(self::$hooks[$name])) {
            foreach (self::$hooks[$name] as $command) {
                if (self::call($command, $args)===$condition) {
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * 运行所有命令返回第一个非空值
     *
     * @param string $name
     * @param array $args
     * @return mixed|null
     */
    public static function execNotNull(string $name, array $args=[])
    {
        debug()->trace($name);
        if (\array_key_exists($name, self::$hooks) && is_array(self::$hooks[$name])) {
            foreach (self::$hooks[$name] as $command) {
                if (!is_null($value=self::call($command, $args))) {
                    return $value;
                }
            }
        }
        return null;
    }

    /**
     * 运行最先注入的命令
     *
     * @param string $name
     * @param array $args
     * @return mixed|null
     */
    public static function execTop(string $name, array $args=[])
    {
        debug()->trace($name);
        if (\array_key_exists($name, self::$hooks) && is_array(self::$hooks[$name])) {
            return  self::call(array_shift(self::$hooks[$name]), $args);
        }
        return null;
    }

    /**
     * 运行最后一个注入的命令
     *
     * @param string $name
     * @param array $args
     * @return mixed|null
     */
    public static function execTail(string $name, array $args=[])
    {
        debug()->trace($name);
        if (\array_key_exists($name, self::$hooks) && is_array(self::$hooks[$name])) {
            return self::call(array_pop(self::$hooks[$name]), $args);
        }
        return null;
    }

    protected static function call($command, array &$args)
    {
        if (conf('hook.enable', true) == false) {
            debug()->warning(__('hook.enable == false refuse run command'));
            return null;
        }
        if (is_string($command)) {
            if (preg_match('/^(debug)|d\=/', $command)) {
                if (conf('debug')) {
                    return (new Command(preg_replace('/^.+?\=/', '', $command)))->exec($args);
                }
            } elseif (preg_match('/^(normal)|n\=/', $command)) {
                if (conf('debug') == false) {
                    return (new Command(preg_replace('/^.+?\=/', '', $command)))->exec($args);
                }
            } elseif (preg_match('/^is?\:(.+?)\=/', $command, $matchs)) {
                $module = $matchs[1];
                if (app()->getActiveModule() == app()->getModuleFullName($module)) {
                    return (new Command(preg_replace('/^.+?\=/', '', $command)))->exec($args);
                }
            } elseif (preg_match('/^(?:reachable)|r\:(.+?)\=/', $command, $matchs)) {
                $module = $matchs[1];
                if (app()->isModuleReachable($module)) {
                    return (new Command(preg_replace('/^.+?\=/', '', $command)))->exec($args);
                }
            } else {
                return (new Command($command))->exec($args);
            }
        } else {
            return (new Command($command))->exec($args);
        }
    }
}
