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

class CommandException extends \ErrorException
{
    protected $cmd;
    protected $params;
    public function __construct(string $info)
    {
        parent::__construct($info, 0, E_WARNING);
    }
    public function setCmd(string $cmd)
    {
        $this->cmd=$cmd;
        return $this;
    }
    public function getCmd()
    {
        return $this->cmd;
    }
    public function getParams()
    {
        return $this->params;
    }
    public function setParams(array $params)
    {
        $this->params=$params;
        return $this;
    }
}
