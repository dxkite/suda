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
 * @version    1.2.4
 */
namespace suda\core;

use Throwable;
use ErrorException;

class Exception extends ErrorException
{
    protected $name;
    protected $backtrace;
    protected $show_start=0;
    protected $show_end=0;

    public function __construct(Throwable $e)
    {
        $this->severity =$e instanceof ErrorException?$e->getSeverity():E_ERROR;
        parent::__construct($e->getMessage(), $e->getCode(), $this->severity, $e->getFile(), $e->getLine(), $e->getPrevious());
        $this->name=get_class($e);
        $this->backtrace=$e->getTrace();
    }

    public function show(int $start, int $end=0)
    {
        $this->show_start=$start;
        $this->show_end=$end;
        return $this;
    }
    
    public function getBackTrace()
    {
        $trace=$this->backtrace;
        $offset_start=$this->show_start;
        $offset_end=$this->show_end;
        while ($offset_start--) {
            array_shift($trace);
        }
        while ($offset_end--) {
            array_pop($trace);
        }
        return $trace;
    }

    public function getName()
    {
        return $this->name;
    }
}
