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
 * @version    1.2.3
 */
namespace suda\tool;

class ArrayValue extends Value
{
    /// 迭代器扩展
    public function current()
    {
        return new Value(current($this->var));
    }
}