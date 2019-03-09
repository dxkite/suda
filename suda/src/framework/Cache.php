<?php
namespace suda\framework;

use suda\framework\Request;
use suda\framework\Response;

/**
 *  Cache 接口
 */
interface Cache
{
    /**
     * 设置 Cache
     *
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public function set(string $name, $value, int $expire):bool;

    /**
     * 获取Cache
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function get(string $name, $default=null);

    /**
     * 删除一个或者全部Cache数据
     *
     * @param string|null $name
     * @return bool
     */
    public function delete(?string $name=null):bool;

    /**
     * 检测是否存在Cache
     *
     * @param string $name
     * @return boolean
     */
    public function has(string $name):bool;

    /**
     * 清除 Cache
     *
     * @return boolean
     */
    public function clear():bool;
}
