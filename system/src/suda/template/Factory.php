<?php

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
