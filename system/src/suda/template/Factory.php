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
namespace suda\template;

/**
 *
 */
class Factory
{
    static $compiler=null;
    /**
     *
     */
    public function __construct()
    {
    }

    /**
     * @param string $compiler
     * @return \suda\template\Compiler
     */
    public static function compiler(string $compiler=null):Compiler
    {

        if(is_null($compiler)){
            self::$compiler='SudaCompiler';
        }
        else{
            self::$compiler=$compiler;
        }
        $compiler='suda\template\compiler\\'.self::$compiler;
        return new $compiler;
    }
}
