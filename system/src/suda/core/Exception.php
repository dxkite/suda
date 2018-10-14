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

use Throwable;
use ErrorException;
use JsonSerializable;

/**
 * 通用系统异常
 */
class Exception extends ErrorException implements JsonSerializable
{
    protected $name;
    protected $backtrace;
    protected $show_start=0;
    protected $show_end=0;
    protected $level = null;

    protected static $levelTable = [
        E_ERROR => Debug::ERROR,
        E_WARNING => Debug::WARNING,
        E_PARSE => Debug::ERROR,
        E_NOTICE => Debug::NOTICE,
        E_CORE_ERROR => Debug::ERROR,
        E_CORE_WARNING => Debug::WARNING,
        E_COMPILE_ERROR => Debug::ERROR,
        E_COMPILE_WARNING => Debug::WARNING,
        E_USER_ERROR => Debug::ERROR,
        E_USER_WARNING => Debug::WARNING,
        E_USER_NOTICE => Debug::NOTICE,
        E_STRICT => Debug::NOTICE,
        E_DEPRECATED => Debug::WARNING,
    ];

    protected static $phpErrorName = [
        E_ERROR => 'ErrorException',
        E_PARSE => 'ParseException',
        E_CORE_ERROR => 'CodeErrorException',
        E_COMPILE_ERROR => 'CompileErrorException',
        E_USER_ERROR => 'UserErrorException',
    ];

    public function __construct(Throwable $e, ?string $name = null)
    {
        $this->severity =$e instanceof ErrorException?$e->getSeverity():E_ERROR;
        parent::__construct($e->getMessage(), $e->getCode(), $this->severity, $e->getFile(), $e->getLine(), $e->getPrevious());
        if (array_key_exists($e->getCode(), self::$phpErrorName)) {
            $this->name= self::$phpErrorName[$e->getCode()];
        } else {
            $this->name=is_null($name)?get_class($e):$name;
        }
        $this->backtrace=$e->getTrace();
    }

    public function setName(string $name)
    {
        $this->name =$name;
        return $this;
    }

    public function show(int $start, int $end=0)
    {
        $this->show_start=$start;
        $this->show_end=$end;
        return $this;
    }
    
    public function getLevel()
    {
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

    
    public function jsonSerialize()
    {
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
