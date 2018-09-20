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
 * @version    since 2.0
 */

namespace suda\core\cache;

/**
 * 缓存接口
 */
interface Cache
{

    /**
     * 设置
     * @param string $name 名
     * @param $value 值
     * @param int $expire 过期时间
     * @return bool
     */
    public function set(string $name, $value, int $expire=null):bool;


    /**
     * 获取值
     * @param string $name 名
     * @return mixed|null
     */
    public function get(string $name, $defalut=null);

    /**
     * 删除值
     * @param string $name 名
     * @return bool
     */
    public function delete(string $name) :bool;
    
    
    /**
     * 检测是否存在
     *
     * @param string $name
     * @return bool
     */
    public function has(string $name):bool;

    /**
     * 清空缓存
     *
     * @param boolean $data
     * @return void
     */
    public function clear();

    /**
     * 检测缓存是否可用
     *
     * @return boolean
     */
    public function enable():bool;
    public function disable():bool;

    /**
     * 实例化一个对象
     *
     * @return Cache
     */
    public static function getInstance();
}
