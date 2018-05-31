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

use Throwable;
use ErrorException;
use JsonSerializable;

class Exception extends ErrorException implements JsonSerializable 
{
    protected $name;
    protected $backtrace;
    protected $show_start=0;
    protected $show_end=0;
    protected $level = null;
    protected static $levelTable = [
        E_NOTICE => DEBUG::NOTICE,
        E_USER_NOTICE => DEBUG::NOTICE,
        E_USER_WARNING => DEBUG::WARNING,
        E_COMPILE_WARNING => DEBUG::WARNING,
        E_CORE_WARNING => DEBUG::WARNING,
        E_DEPRECATED => DEBUG::WARNING,
    ];
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
    
    public function getLevel() {
        if (isset(self::$levelTable[$this->getSeverity()])) {
            return self::$levelTable[$this->getSeverity()];
        }
        return Debug::ERROR;
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
    public function jsonSerialize() {
        return  [
            'name' => $this->getName(),
            'message' => $this->getMessage(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
            'level' => $this->getLevel(),
            'severity' => $this->getSeverity(),
            'backtrace'=> $this->getBackTrace(),
        ];
    }
}
