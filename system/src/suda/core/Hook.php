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
namespace suda\core;

use suda\tool\Command;
use suda\tool\Json;

class Hook
{
    private static $hooks=[];

    public static function loadJson(string $path)
    {
        $hooks=Json::loadFile($path);
        debug()->trace($path);
        self::load($hooks?:[]);
    }

    public static function load(array $arrays)
    {
        self::$hooks=array_merge_recursive(self::$hooks, $arrays);
    }

    public static function listen(string $name, $command)
    {
        self::add($name, $command);
    }

    public static function register(string $name, $command)
    {
        self::add($name, $command);
    }

    public static function add(string $name, $command)
    {
        self::$hooks[$name][]=$command;
    }

    public static function addTop(string $name, $command)
    {
        if (isset(self::$hooks[$name]) && is_array(self::$hooks[$name])) {
            array_unshift(self::$hooks[$name], $command);
        } else {
            self::add($name, $command);
        }
    }
    public static function remove(string $name, $remove)
    {
        if (isset(self::$hooks[$name]) && is_array(self::$hooks[$name])) {
            foreach (self::$hooks[$name] as $key=>$command) {
                if ($command === $remove) {
                    unset(self::$hooks[$name][$key]);
                }
            }
        }
    }
    /* --- 运行区 ---*/
    public static function exec(string $name, array $args=[])
    {
        if (isset(self::$hooks[$name]) && is_array(self::$hooks[$name])) {
            self::$hooks[$name] = array_unique(self::$hooks[$name]);
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
     * @return void
     */
    public static function execIf(string $name, array $args=[], $condition = true)
    {
        if (isset(self::$hooks[$name]) && is_array(self::$hooks[$name])) {
            self::$hooks[$name] = array_unique(self::$hooks[$name]);
            foreach (self::$hooks[$name] as $command) {
                if (self::call($command, $args)===$condition) {
                    return true;
                }
            }
        }
        return false;
    }

    public static function execNotNull(string $name, array $args=[])
    {
        if (isset(self::$hooks[$name]) && is_array(self::$hooks[$name])) {
            self::$hooks[$name] = array_unique(self::$hooks[$name]);
            foreach (self::$hooks[$name] as $command) {
                if (!is_null($value=self::call($command, $args))) {
                    return $value;
                }
            }
        }
        return null;
    }

    public static function execTop(string $name, array $args=[])
    {
        if (isset(self::$hooks[$name]) && is_array(self::$hooks[$name])) {
            return  self::call(array_shift(self::$hooks[$name]), $args);
        }
    }

    public static function execTail(string $name, array $args=[])
    {
        if (isset(self::$hooks[$name]) && is_array(self::$hooks[$name])) {
            return  self::call(array_pop(self::$hooks[$name]), $args);
        }
    }

    protected static function call($command, array &$args)
    {
        // TODO isModuleReachable
        if (is_string($command)) {
            if (preg_match('/^(debug)|d\=/', $command)) {
                if (conf('debug')) {
                    return (new Command(preg_replace('/^debug\=/', '', $command)))->exec($args);
                }
            } elseif (preg_match('/^(normal)|n\=/', $command)) {
                if (conf('debug') == false) {
                    return (new Command(preg_replace('/^normal\=/', '', $command)))->exec($args);
                }
            } else {
                return (new Command($command))->exec($args);
            }
        }
        return (new Command($command))->exec($args);
    }
}
