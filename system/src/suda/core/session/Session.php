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

namespace suda\core\session;

/**
 * Session 接口
 */
interface Session
{
    public static function getInstance();
    public function set(string $name, $value);
    public function get(string $name='', $default=null);
    public function delete(?string $name=null);
    public function has(string $name);
    public function destroy();
}
