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
namespace suda\exception;

class SQLException extends \ErrorException
{
    protected $sql;
    protected $binds;
    public function setSql(string $sql)
    {
        $this->sql=$sql;
        return $this;
    }
    public function getSql()
    {
        return $this->sql;
    }
    public function getBinds()
    {
        return $this->binds;
    }
    public function setBinds(array $binds)
    {
        $this->binds=$binds;
        return $this;
    }
}
